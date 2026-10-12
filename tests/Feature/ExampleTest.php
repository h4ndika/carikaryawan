<?php

use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use App\Models\Province;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Http::preventStrayRequests();
    Http::fake();
});

// Smoke tests: the public pages that Google indexes must never 500 after a deploy.
function publishedPost(string $categoryName = 'Lowongan Kerja', array $attrs = []): Post
{
    $province = Province::firstOrCreate(['slug' => 'jawa-barat'], ['name' => 'Jawa Barat']);
    $city = City::firstOrCreate(['slug' => 'bandung-kota'], ['name' => 'Kota Bandung', 'province_id' => $province->id]);
    $category = Category::firstOrCreate(['slug' => Str::slug($categoryName)], ['name' => $categoryName]);

    return Post::create(array_merge([
        'title' => 'Admin - Perusahaan Uji', 'slug' => 'admin-perusahaan-uji', 'status' => 'published',
        'category_id' => $category->id, 'city_id' => $city->id, 'province_id' => $province->id,
    ], $attrs));
}

it('renders the main public pages', function (string $url) {
    $this->get($url)->assertOk();
})->with(['/', '/cari', '/tools', '/karir/cv-builder', '/karir/interview-prep', '/tentang-kami', '/sitemap.xml', '/ads.txt']);

it('renders category, city listing and post detail with the existing job URLs', function () {
    $post = publishedPost();

    $this->get('/lowongan-kerja')->assertOk();
    $this->get('/lowongan-kerja/bandung-kota')->assertOk();
    $this->get('/lowongan-kerja/bandung-kota/admin-perusahaan-uji')->assertOk()->assertSee($post->title);
});

it('returns 404 for unknown post', function () {
    publishedPost();
    $this->get('/lowongan-kerja/bandung-kota/tidak-ada')->assertNotFound();
});

it('redirects retired blog and landmark URLs', function (string $url) {
    $this->get($url)->assertStatus(301)->assertRedirect('/');
})->with(['/blog', '/blog/panduan-lama', '/disekitar/stasiun-lama', '/hotel/disekitar/stasiun-lama']);

it('never serves or indexes non-job posts even before the purge is applied', function () {
    $job = publishedPost();
    $place = publishedPost('Kuliner', ['title' => 'Warung lama', 'slug' => 'warung-lama']);

    $this->get('/')->assertSee($job->title)->assertDontSee($place->title);
    $this->get('/cari')->assertDontSee($place->title);
    $this->get('/kuliner')->assertNotFound();
    $this->get('/kuliner/bandung-kota')->assertNotFound();
    $this->get('/kuliner/bandung-kota/warung-lama')->assertNotFound();
    $this->get('/sitemap.xml')->assertDontSee('warung-lama')->assertDontSee('/blog');
    $this->getJson('/api/csr/jobs/' . $place->id)->assertNotFound();
    $this->getJson('/api/csr/jobs')->assertJsonPath('pagination.total', 1);
});

it('serves 503 in maintenance mode but keeps the API up', function () {
    Setting::set('maintenance_mode_enabled', true);

    $this->get('/')->assertStatus(503);
    $this->getJson('/api/csr/config')->assertOk();
});

it('lets admin disable the CSR API', function () {
    $this->getJson('/api/csr/jobs')->assertOk();

    Setting::set('csr_api_enabled', false);
    $this->getJson('/api/csr/jobs')->assertForbidden();
});

it('expires job posts after 21 days', function () {
    $job = publishedPost('Lowongan Kerja', ['published_at' => now()->subDays(22)]);
    expect($job->isJob())->toBeTrue()->and($job->isExpired())->toBeTrue();

    $job->published_at = now()->subDays(5);
    expect($job->isExpired())->toBeFalse();
});

it('keeps closed jobs out of the index, sitemap and Google Jobs', function () {
    $open = publishedPost('Lowongan Kerja', ['slug' => 'loker-buka', 'published_at' => now()->subDays(5)]);
    $closed = publishedPost('Lowongan Kerja', ['slug' => 'loker-tutup', 'published_at' => now()->subDays(22)]);

    $types = fn (Post $p) => collect($p->generateJsonLd()['@graph'] ?? [])->pluck('@type')->all();

    expect($open->seoRobots())->toBe('index,follow')
        ->and($closed->seoRobots())->toBe('noindex,follow')
        ->and($types($open))->toContain('JobPosting')
        ->and($types($closed))->not->toContain('JobPosting');

    $job = collect($open->generateJsonLd()['@graph'])->firstWhere('@type', 'JobPosting');
    expect($job['validThrough'])->toBe($open->published_at->copy()->addDays(21)->toIso8601String())
        ->and($job)->not->toHaveKey('baseSalary')
        ->and($job['directApply'])->toBeFalse();

    $this->get('/sitemap.xml')->assertSee('loker-buka')->assertDontSee('loker-tutup');
});

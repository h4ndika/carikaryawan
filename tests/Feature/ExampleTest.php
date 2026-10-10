<?php

use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use App\Models\Province;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Smoke tests: the public pages that Google indexes must never 500 after a deploy.
function publishedPost(string $categoryName = 'Kuliner', array $attrs = []): Post
{
    $province = Province::firstOrCreate(['slug' => 'jawa-barat'], ['name' => 'Jawa Barat']);
    $city = City::firstOrCreate(['slug' => 'bandung-kota'], ['name' => 'Kota Bandung', 'province_id' => $province->id]);
    $category = Category::firstOrCreate(['slug' => Str::slug($categoryName)], ['name' => $categoryName]);

    return Post::create(array_merge([
        'title' => 'Warung Uji', 'slug' => 'warung-uji', 'status' => 'published',
        'category_id' => $category->id, 'city_id' => $city->id, 'province_id' => $province->id,
    ], $attrs));
}

it('renders the main public pages', function (string $url) {
    $this->get($url)->assertOk();
})->with(['/', '/cari', '/blog', '/tools', '/tentang-kami', '/sitemap.xml', '/ads.txt']);

it('renders category, city listing and post detail', function () {
    $post = publishedPost();

    $this->get('/kuliner')->assertOk();
    $this->get('/kuliner/bandung-kota')->assertOk();
    $this->get('/kuliner/bandung-kota/warung-uji')->assertOk()->assertSee($post->title);
});

it('returns 404 for unknown post', function () {
    publishedPost();
    $this->get('/kuliner/bandung-kota/tidak-ada')->assertNotFound();
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

    // validThrough follows the 21-day rule instead of sliding forward on every crawl.
    $job = collect($open->generateJsonLd()['@graph'])->firstWhere('@type', 'JobPosting');
    expect($job['validThrough'])->toBe($open->published_at->copy()->addDays(21)->toIso8601String())
        ->and($job)->not->toHaveKey('baseSalary') // no invented salary
        ->and($job['directApply'])->toBeFalse();

    $this->get('/sitemap.xml')->assertSee('loker-buka')->assertDontSee('loker-tutup');
});

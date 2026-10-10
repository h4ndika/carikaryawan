<?php

use App\Models\Category;
use App\Models\City;
use App\Models\Landmark;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

it('includes only landmarks whose pages are indexable', function (int $nearbyCount, float $radius, bool $indexable) {
    Cache::forget('sitemap_indexable_landmark_ids');
    // Exercise the unchanged SQL distance scope on SQLite as well as the PHP sitemap filter.
    $pdo = DB::connection()->getPdo();
    foreach (['radians' => 'deg2rad', 'cos' => 'cos', 'sin' => 'sin', 'acos' => 'acos'] as $sql => $php) {
        $pdo->sqliteCreateFunction($sql, $php, 1);
    }

    $far = publishedPost(attrs: ['slug' => 'far', 'latitude' => -6.92, 'longitude' => 107.6]);
    $landmark = Landmark::create([
        'name' => 'Landmark Uji', 'slug' => 'landmark-uji', 'type' => 'mall',
        'city_id' => $far->city_id, 'province_id' => $far->province_id,
        'latitude' => -6.9, 'longitude' => 107.6, 'default_radius_km' => $radius,
    ]);
    for ($i = 0; $i < $nearbyCount; $i++) {
        publishedPost(attrs: ['slug' => 'near-' . $i, 'latitude' => -6.9004, 'longitude' => 107.6]);
    }
    publishedPost(attrs: ['slug' => 'draft', 'status' => 'draft', 'latitude' => -6.9004, 'longitude' => 107.6]);
    publishedPost(attrs: ['slug' => 'deleted', 'latitude' => -6.9004, 'longitude' => 107.6])->delete();
    publishedPost(attrs: ['slug' => 'no-coordinates']);

    $pageUrl = route('landmark.show', $landmark);
    $this->get($pageUrl)->assertOk()->assertSee(
        'name="robots" content="' . ($indexable ? 'index,follow' : 'noindex,follow') . '"', false
    );
    $sitemap = $this->get('/sitemap.xml')->assertOk();
    $loc = '<loc>' . $pageUrl . '</loc>';
    if ($indexable) {
        $sitemap->assertSee($loc, false);
    } else {
        $sitemap->assertDontSee($loc, false);
    }
    Cache::forget('sitemap_indexable_landmark_ids');
})->with([
    'empty' => [0, 1, false],
    'two places' => [2, 1, false],
    'three places' => [3, 1, true],
    'default three-kilometre radius' => [2, 0, true],
]);

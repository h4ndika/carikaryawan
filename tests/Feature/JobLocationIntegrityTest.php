<?php

use App\Helpers\AddressParser;
use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use App\Models\Province;
use App\Services\GoogleMapsImportService;
use App\Services\JobLocationRepairService;
use App\Services\PostAiContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Http::preventStrayRequests();
    Http::fake();
    $this->province = Province::create(['name' => 'DKI Jakarta', 'slug' => 'dki-jakarta']);
    $this->south = City::create(['name' => 'KOTA JAKARTA SELATAN', 'slug' => 'jakarta-selatan-kota', 'province_id' => $this->province->id]);
    $this->west = City::create(['name' => 'KOTA JAKARTA BARAT', 'slug' => 'jakarta-barat-kota', 'province_id' => $this->province->id]);
    $this->category = Category::firstOrCreate(['slug' => 'lowongan-kerja'], ['name' => 'Lowongan Kerja']);
});

function locationTestPost($test, array $attrs = []): Post
{
    return Post::create(array_merge([
        'title' => 'Admin Jakarta Barat', 'slug' => 'admin-jakarta-barat', 'content' => 'Penempatan Jakarta Barat.',
        'status' => 'published', 'published_at' => now()->subDays(2), 'place_id' => 'job_original',
        'category_id' => $test->category->id, 'city_id' => $test->south->id, 'province_id' => $test->province->id,
    ], $attrs));
}

it('matches complete city names and refuses ambiguous names', function () {
    $province = Province::create(['name' => 'Jawa Barat', 'slug' => 'jawa-barat']);
    City::create(['name' => 'Kota Bandung', 'slug' => 'bandung-kota', 'province_id' => $province->id]);
    City::create(['name' => 'Kabupaten Bandung', 'slug' => 'bandung-kabupaten', 'province_id' => $province->id]);
    $west = City::create(['name' => 'Kabupaten Bandung Barat', 'slug' => 'bandung-barat', 'province_id' => $province->id]);
    expect(AddressParser::parse('Bandung Barat, Jawa Barat')['city_id'])->toBe($west->id)
        ->and(AddressParser::parse('Bandung, Jawa Barat')['city_id'])->toBeNull()
        ->and(AddressParser::parse('Jakarta')['city_id'])->toBeNull()
        ->and(AddressParser::parse('Jakarta Barat dan Jakarta Selatan')['city_id'])->toBeNull()
        ->and(AddressParser::parse('Jakarta Baratan')['city_id'])->toBeNull();
});

it('imports source location and keeps unknown locations unpublished', function () {
    $importer = app(GoogleMapsImportService::class);
    $importer->importFromRecords([
        ['title' => 'Admin Barat', 'location' => 'Jakarta Barat', 'details' => ['lokasi' => 'Jakarta Barat']],
        ['title' => 'Admin Remote', 'location' => 'Remote'],
    ], ['category_id' => $this->category->id, 'status' => 'published']);
    expect(Post::where('title', 'Admin Barat')->first()->city_id)->toBe($this->west->id)
        ->and(Post::where('title', 'Admin Remote')->exists())->toBeFalse();
});

it('preserves source facts when publishing AI jobs', function () {
    $post = locationTestPost($this, ['status' => 'draft']);
    foreach (['gaji' => '-', 'perusahaan' => 'Sumber', 'lokasi' => 'Remote', 'posisi' => 'Admin', 'cara_melamar' => 'https://example.com/apply'] as $key => $value) {
        $post->details()->create(['meta_key' => $key, 'meta_value' => $value]);
    }
    $service = app(PostAiContentService::class);
    $service->applyGeneratedContent($post, '<p>' . str_repeat('Informasi lowongan dari sumber resmi. ', 12) . '</p><json>{"gaji":"Rp 10.000.000", "perusahaan":"AI", "lokasi":"Jakarta", "posisi":"Manajer", "cara_melamar":"Palsu", "kualifikasi":"Pengalaman administrasi"}</json>', true);
    $details = $post->details()->pluck('meta_value', 'meta_key');
    expect($details['gaji'])->toBe('-')->and($details['perusahaan'])->toBe('Sumber')
        ->and($details['lokasi'])->toBe('Remote')->and($details['posisi'])->toBe('Admin')
        ->and($details['cara_melamar'])->toBe('https://example.com/apply')
        ->and($details['kualifikasi'])->toBe('Pengalaman administrasi')
        ->and($post->fresh()->status)->toBe('published')
        ->and($service->buildPrompt($post))->not->toContain('Perkiraan / Negosiasi');
});

it('defaults to dry run and requires reviewed ids and backup to apply', function () {
    $post = locationTestPost($this);
    $this->artisan('jobs:fix-locations')->assertSuccessful();
    expect($post->fresh()->city_id)->toBe($this->south->id)->and(DB::table('post_redirects')->count())->toBe(0);
    $this->artisan('jobs:fix-locations --apply')->assertFailed();
});

it('repairs explicit locations atomically and redirects without changing identity or expiration', function () {
    $post = locationTestPost($this);
    $oldUrl = $post->url();
    $expires = $post->expired_at;
    $published = $post->published_at;
    app(JobLocationRepairService::class)->apply($post, $this->west);
    $post->refresh();
    expect($post->city_id)->toBe($this->west->id)->and($post->place_id)->toBe('job_original')
        ->and($post->expired_at?->toIso8601String())->toBe($expires?->toIso8601String())
        ->and($post->published_at->toIso8601String())->toBe($published->toIso8601String());
    $this->get($oldUrl)->assertStatus(301)->assertRedirect($post->url());
    $this->get($post->url())->assertOk();
    $post->update(['status' => 'draft']);
    $this->get($oldUrl)->assertNotFound();
});

it('skips conflicting city mentions in old content', function () {
    $post = locationTestPost($this, ['content' => 'Kantor Jakarta Selatan, penempatan Jakarta Barat.']);
    expect(app(JobLocationRepairService::class)->candidate($post))->toBeNull();
});

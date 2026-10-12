<?php

use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use App\Models\Province;
use App\Services\GoogleMapsImportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
    Http::fake();
    $this->province = Province::create(['slug' => 'jawa-barat', 'name' => 'Jawa Barat']);
    $this->city = City::create(['slug' => 'bandung-kota', 'name' => 'Kota Bandung', 'province_id' => $this->province->id]);
});

it('imports a job with its dedup key, company logo and original apply link', function () {
    $category = Category::jobs()->firstOrFail();
    $placeId = Post::jobPlaceId('PT Uji', 'Admin', 'Bandung');
    $record = [
        'title' => 'Admin - PT Uji', 'placeId' => $placeId, 'website' => 'https://example.com/apply/1',
        'images' => ['https://example.com/logo.png'],
        'details' => ['perusahaan' => 'PT Uji', 'cara_melamar' => 'https://example.com/apply/1'],
    ];
    $options = ['city_id' => $this->city->id, 'category_id' => $category->id, 'skip_duplicates' => true];
    $service = new GoogleMapsImportService;
    expect($service->importFromRecords([$record], $options)['imported'])->toBe(1);
    expect($service->importFromRecords([$record], $options)['skipped'])->toBe(1);

    $post = Post::where('place_id', $placeId)->firstOrFail();
    expect($post->isJob())->toBeTrue()
        ->and($post->status)->toBe('draft')
        ->and($post->website)->toBe($record['website'])
        ->and($post->images->first()->image_url)->toBe($record['images'][0])
        ->and($post->getDetailValue(['perusahaan']))->toBe('PT Uji');
    Http::assertNothingSent();
});

it('purges legacy content including soft deletes but preserves every job category and logo', function () {
    $category = Category::create(['slug' => 'LOKER-LAMA', 'name' => 'Kategori Legacy']);
    $placeCategory = Category::create(['slug' => 'hotel', 'name' => 'Hotel']);
    $attrs = [
        'title' => 'Admin Uji', 'slug' => 'admin-uji', 'category_id' => $category->id,
        'city_id' => $this->city->id, 'province_id' => $this->province->id,
        'status' => 'published', 'place_id' => Post::jobPlaceId('PT Uji', 'Admin', 'Bandung'),
    ];
    $job = Post::create($attrs);
    $job->images()->create(['image_url' => 'https://example.com/logo.png', 'order' => 0]);
    $place = Post::create(array_merge($attrs, ['slug' => 'hotel-uji', 'category_id' => $placeCategory->id, 'place_id' => 'ChI-hotel']));
    $place->details()->create(['meta_key' => 'fasilitas', 'meta_value' => 'Kolam']);
    $place->images()->create(['image_url' => 'https://example.com/place.png', 'order' => 0]);
    $place->delete();

    // Recreate the retired schema to simulate upgrading an existing installation.
    foreach ([
        '2026_08_24_080000_create_landmarks_table.php',
        '2026_08_29_083941_create_photo_scrape_jobs_table.php',
        '2026_09_12_030744_create_articles_table.php',
        '2026_09_12_044044_create_auto_harvest_queues_table.php',
        '2026_09_22_035830_create_article_curation_jobs_table.php',
    ] as $file) {
        (require database_path('migrations/' . $file))->up();
    }
    Schema::table('posts', fn (Blueprint $table) => $table->decimal('latitude', 10, 7)->nullable());

    $migration = require database_path('migrations/2026_10_12_000000_remove_place_landmark_and_blog_features.php');
    $migration->up();
    $migration->up(); // Safe to rerun after a partially completed deploy.

    expect(Post::withTrashed()->find($place->id))->toBeNull()
        ->and(Category::find($placeCategory->id))->toBeNull()
        ->and($job->fresh()->place_id)->toBe($attrs['place_id'])
        ->and($job->fresh()->images)->toHaveCount(1)
        ->and(Schema::hasColumn('posts', 'latitude'))->toBeFalse();
    foreach (['landmarks', 'articles', 'article_curation_jobs', 'auto_harvest_queues', 'photo_scrape_jobs'] as $table) {
        expect(Schema::hasTable($table))->toBeFalse();
    }
    expect(DB::table('post_details')->where('post_id', $place->id)->count())->toBe(0)
        ->and(DB::table('post_images')->where('post_id', $place->id)->count())->toBe(0);
});

it('only serves branded job images for published job posts', function () {
    $job = Post::create([
        'title' => 'Admin Uji', 'slug' => 'admin-uji', 'category_id' => Category::jobs()->firstOrFail()->id,
        'city_id' => $this->city->id, 'province_id' => $this->province->id,
        'status' => 'published', 'published_at' => now(),
    ]);
    $this->get('/job-image/admin-uji.svg')->assertOk()->assertSee('CARIKARYAWAN');

    $job->update(['category_id' => Category::create(['slug' => 'hotel', 'name' => 'Hotel'])->id]);
    $this->get('/job-image/admin-uji.svg')->assertNotFound();
    $this->get('/job-image/admin-uji.png')->assertNotFound();
});

it('excludes expired jobs recognized only by category name from the sitemap', function () {
    $category = Category::create(['slug' => 'karier', 'name' => 'Lowongan Legacy']);
    Post::create([
        'title' => 'Admin Lama', 'slug' => 'admin-lama', 'category_id' => $category->id,
        'city_id' => $this->city->id, 'province_id' => $this->province->id,
        'status' => 'published', 'published_at' => now()->subDays(22),
    ]);
    $this->get('/sitemap.xml')->assertOk()->assertDontSee('admin-lama');
});

it('refuses the purge before deleting anything when legacy extraction is still queued', function () {
    DB::table('jobs')->insert([
        'queue' => 'photos', 'payload' => '{"displayName":"App\\\\Jobs\\\\ProcessBatchPhotoScrapeJob"}',
        'attempts' => 0, 'available_at' => time(), 'created_at' => time(),
    ]);
    $migration = require database_path('migrations/2026_10_12_000000_remove_place_landmark_and_blog_features.php');
    expect(fn () => $migration->up())->toThrow(RuntimeException::class, 'Drain');
});

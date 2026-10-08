<?php

use App\Exceptions\PhotoScrapeException;
use App\Filament\Rahasia\Resources\PhotoScrapeJobs\Pages\ListPhotoScrapeJobs;
use App\Jobs\ProcessBatchPhotoScrapeJob;
use App\Models\ApifyAccount;
use App\Models\Category;
use App\Models\City;
use App\Models\PhotoScrapeJob;
use App\Models\Post;
use App\Models\Province;
use App\Models\User;
use App\Services\PhotoScrapeQueue;
use App\Services\PhotoScraperService;
use Filament\Facades\Filament;
use Illuminate\Http\Client\Factory;
use Illuminate\Queue\TimeoutExceededException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

beforeEach(function () {
    // Exercise real photo-pipeline migrations only, isolated from unrelated AI SDK migrations.
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');
    $migrations = [
        '0001_01_01_000000_create_users_table.php',
        '0001_01_01_000002_create_jobs_table.php',
        '2025_06_20_095025_create_categories_table.php',
        '2025_06_20_095214_create_provinces_table.php',
        '2025_06_20_095404_create_cities_table.php',
        '2025_08_02_075421_create_posts_table.php',
        '2025_08_02_080049_create_post_details_table.php',
        '2025_08_02_080744_create_post_iamges_table.php',
        '2025_08_14_082132_alter_posts_thumbnail_is_null.php',
        '2025_08_15_040008_change_name_table_post_images.php',
        '2026_08_21_162822_add_google_maps_fields_to_posts_table.php',
        '2026_08_22_160000_create_apify_accounts_table.php',
        '2026_08_29_083941_create_photo_scrape_jobs_table.php',
        '2026_09_11_000000_add_run_state_to_photo_scrape_jobs.php',
    ];
    foreach ($migrations as $migration) {
        (require database_path('migrations/'.$migration))->up();
    }

    config(['queue.default' => 'database', 'services.apify.token' => 'test-secret', 'services.apify.photo_max_active_runs' => 3]);
    Http::preventStrayRequests();
    $this->realQueue = Queue::getFacadeRoot();
    Queue::fake();
});

function photoRow(string $placeId = 'ChI-test', array $attrs = []): PhotoScrapeJob
{
    $province = Province::firstOrCreate(['slug' => 'test-province'], ['name' => 'Test Province']);
    $city = City::firstOrCreate(['slug' => 'test-city'], ['name' => 'Test City', 'province_id' => $province->id]);
    $category = Category::firstOrCreate(['slug' => 'test-wisata'], ['name' => 'Test Wisata']);
    $post = Post::create([
        'title' => 'Test Place', 'slug' => 'test-'.Str::uuid(),
        'category_id' => $category->id, 'city_id' => $city->id, 'province_id' => $province->id,
        'place_id' => $placeId, 'status' => 'draft', 'published_at' => now(),
    ]);

    return PhotoScrapeJob::create(array_merge(['post_id' => $post->id, 'limit' => 8, 'status' => 'pending'], $attrs));
}

function photoWorker(array $rows): ProcessBatchPhotoScrapeJob
{
    $ids = array_map(fn ($row) => $row->id, $rows);
    app(PhotoScrapeQueue::class)->enqueue($ids);
    $worker = new ProcessBatchPhotoScrapeJob($ids, $rows[0]->fresh()->processing_token);
    $worker->withFakeQueueInteractions();

    return $worker;
}

function fakePhotoRun(array $items = [], string $status = 'SUCCEEDED'): void
{
    Http::fake([
        'api.apify.com/v2/acts/*/runs*' => Http::response(['data' => ['id' => 'run-test', 'defaultDatasetId' => 'dataset-test']]),
        'api.apify.com/v2/actor-runs/*' => Http::response(['data' => ['status' => $status]]),
        'api.apify.com/v2/datasets/*' => Http::response($items),
    ]);
}

it('reserves at most three rows per batch and ignores double dispatch', function () {
    $rows = collect(range(1, 7))->map(fn ($i) => photoRow('ChI-'.$i));
    $service = app(PhotoScrapeQueue::class);
    expect($service->enqueue($rows->pluck('id')->all()))->toBe(7);
    expect($service->enqueue($rows->pluck('id')->all()))->toBe(0);
    Queue::assertPushed(ProcessBatchPhotoScrapeJob::class, 3);
    Queue::assertPushed(ProcessBatchPhotoScrapeJob::class, fn ($job) => count($job->photoJobIds) === 3 && $job->processingToken !== null);
});

it('rejects sync queues rather than blocking the Filament request', function () {
    $row = photoRow();
    config(['queue.default' => 'sync']);
    expect(fn () => app(PhotoScrapeQueue::class)->enqueue([$row->id]))->toThrow(RuntimeException::class, 'asynchronous');
    expect($row->fresh()->processing_token)->toBeNull();
});

it('starts once and completes on a later queue invocation', function () {
    $row = photoRow();
    fakePhotoRun([['placeId' => 'ChI-test', 'imageUrl' => 'https://lh3.googleusercontent.com/photo1']]);
    $worker = photoWorker([$row]);
    $worker->handle(app(PhotoScraperService::class));
    $worker->assertReleased(15);
    expect($row->fresh()->status)->toBe('processing')->and($row->fresh()->phase)->toBe('polling');
    expect(DB::table('photo_scrape_jobs')->value('apify_token'))->not->toBe('test-secret');
    expect($row->fresh()->toArray())->not->toHaveKey('apify_token');
    Http::assertSentCount(1);
    Http::assertSent(fn ($request) => $request['scrapePhotos'] === true && $request['startUrls'][0]['url'] === 'https://www.google.com/maps/place/?q=place_id:ChI-test' && str_contains($request->url(), 'compass~crawler-google-places'));
    $worker->handle(app(PhotoScraperService::class));
    expect($row->fresh()->status)->toBe('completed')->and($row->fresh()->photos_count)->toBe(1)
        ->and($row->fresh()->processing_token)->toBeNull()->and($row->fresh()->apify_token)->toBeNull();
    expect($row->post->fresh()->images)->toHaveCount(1);
    $worker->handle(app(PhotoScraperService::class));
    Http::assertSentCount(3); // no new run on duplicate delivery
});

it('releases worker while Apify is still running', function (string $status) {
    $row = photoRow();
    fakePhotoRun([], $status);
    $worker = photoWorker([$row]);
    $worker->handle(app(PhotoScraperService::class));
    $worker->handle(app(PhotoScraperService::class));
    $worker->assertReleased(15);
    expect($row->fresh()->status)->toBe('processing');
    Http::assertSentCount(2);
})->with(['READY', 'RUNNING', 'ABORTING', 'TIMING-OUT']);

it('fails terminal Apify states with run details', function (string $status) {
    $row = photoRow();
    fakePhotoRun([], $status);
    $worker = photoWorker([$row]);
    $worker->handle(app(PhotoScraperService::class));
    $worker->handle(app(PhotoScraperService::class));
    expect($row->fresh()->status)->toBe('failed')->and($row->fresh()->error_message)->toContain($status, 'run-test');
    Http::assertSentCount(2);
})->with(['FAILED', 'ABORTED', 'TIMED-OUT']);

it('preserves partial success and existing galleries without expensive fallback', function () {
    $a = photoRow('ChI-a');
    $b = photoRow('ChI-b', ['replace_existing' => true]);
    $b->post->images()->create(['image_url' => 'https://example.com/existing.jpg', 'order' => 0]);
    fakePhotoRun([['placeId' => 'ChI-a', 'imageUrl' => 'https://lh3.googleusercontent.com/new-photo']]);
    $worker = photoWorker([$a, $b]);
    $worker->handle(app(PhotoScraperService::class));
    $worker->handle(app(PhotoScraperService::class));
    expect($a->fresh()->status)->toBe('completed')->and($b->fresh()->status)->toBe('failed');
    expect($b->post->images()->value('image_url'))->toBe('https://example.com/existing.jpg');
    Http::assertSentCount(3);
});

it('does not reset live rows from any enqueue action', function () {
    $row = photoRow();
    fakePhotoRun();
    $worker = photoWorker([$row]);
    $worker->handle(app(PhotoScraperService::class));
    expect(app(PhotoScrapeQueue::class)->enqueue([$row->id]))->toBe(0);
    expect(app(PhotoScrapeQueue::class)->recoverStale())->toBe(0);
    expect($row->fresh()->status)->toBe('processing');
});

it('caps active runs without making HTTP calls for waiting batches', function () {
    $row = photoRow();
    fakePhotoRun();
    photoWorker([$row])->handle(app(PhotoScraperService::class));
    config(['services.apify.photo_max_active_runs' => 1]);
    $next = photoRow('ChI-next');
    $worker = photoWorker([$next]);
    $worker->handle(app(PhotoScraperService::class));
    $worker->assertReleased(15);
    expect($next->fresh()->status)->toBe('pending');
    Http::assertSentCount(1);
});

it('marks timeout failures using serialized ownership without touching newer retries', function () {
    $row = photoRow();
    $old = photoWorker([$row]);
    $old->failed(new RuntimeException('driver secret'));
    expect($row->fresh()->status)->toBe('failed')->and($row->fresh()->error_message)->not->toContain('secret');
    $new = photoWorker([$row]);
    $old->failed(new RuntimeException('late failure'));
    expect($row->fresh()->status)->toBe('pending')->and($row->fresh()->processing_token)->toBe($new->processingToken);
});

it('recovers SIGKILL and legacy stuck rows but leaves fresh work alone', function () {
    $old = photoRow('ChI-old', ['status' => 'processing', 'started_at' => now()->subMinutes(20)]);
    $fresh = photoRow('ChI-new', ['status' => 'processing', 'started_at' => now()]);
    $queued = photoRow('ChI-queued');
    photoWorker([$queued]);
    PhotoScrapeJob::whereKey($queued->id)->update(['updated_at' => now()->subMinutes(31)]);
    expect(app(PhotoScrapeQueue::class)->recoverStale())->toBe(2);
    expect($old->fresh()->status)->toBe('failed')->and($queued->fresh()->status)->toBe('failed')
        ->and($fresh->fresh()->status)->toBe('processing');
});

it('fails invalid place IDs and deleted posts without HTTP calls', function () {
    $a = photoRow('invalid');
    $b = photoRow('ChI-deleted');
    $b->post->delete();
    photoWorker([$a, $b])->handle(app(PhotoScraperService::class));
    expect($a->fresh()->status)->toBe('failed')->and($b->fresh()->status)->toBe('failed');
    Http::assertNothingSent();
});

it('never restarts a run after an ambiguous start failure', function () {
    $row = photoRow();
    Http::fake(['*' => Http::failedConnection()]);
    $worker = photoWorker([$row]);
    $worker->handle(app(PhotoScraperService::class));
    expect($row->fresh()->status)->toBe('failed')->and($row->fresh()->error_message)->toContain('run mungkin sudah dibuat');
    $worker->handle(app(PhotoScraperService::class));
    expect($row->fresh()->apify_run_id)->toBeNull();
});

it('reports invalid-input without retrying or leaking the provider body', function () {
    $row = photoRow();
    Http::fake(['*' => Http::response(['error' => ['type' => 'invalid-input', 'message' => 'test-secret']], 400)]);
    photoWorker([$row])->handle(app(PhotoScraperService::class));
    expect($row->fresh()->error_message)->toContain('invalid-input', 'all/menu')->not->toContain('test-secret');
    Http::assertSentCount(1);
});

it('fails missing credentials explicitly', function () {
    config(['services.apify.token' => null]);
    $row = photoRow();
    photoWorker([$row])->handle(app(PhotoScraperService::class));
    expect($row->fresh()->error_message)->toContain('Tidak ada akun/token');
    Http::assertNothingSent();
});

it('rotates exhausted credentials but not temporary rate limits', function () {
    $a = ApifyAccount::create(['token' => 'expired', 'status' => 'active']);
    $b = ApifyAccount::create(['token' => 'working', 'status' => 'active']);
    Http::fakeSequence()->push(['error' => ['type' => 'monthly-usage-hard-limit-exceeded']], 402)
        ->push(['data' => ['id' => 'r', 'defaultDatasetId' => 'd']]);
    expect(app(PhotoScraperService::class)->startPhotoRun(['ChI-test'])['token'])->toBe('working');
    expect($a->fresh()->status)->toBe('exhausted')->and($b->fresh()->usage_count)->toBe(1);
    Http::swap(new Factory);
    Http::fake(['*' => Http::response(['error' => ['type' => 'rate-limit-exceeded']], 429)]);
    expect(fn () => app(PhotoScraperService::class)->startPhotoRun(['ChI-test']))->toThrow(PhotoScrapeException::class, '429');
    expect($b->fresh()->status)->toBe('active');
});

it('bounds transient polling retries without starting another run', function () {
    $row = photoRow();
    fakePhotoRun();
    $worker = photoWorker([$row]);
    $worker->handle(app(PhotoScraperService::class));
    Http::swap(new Factory);
    Http::fake(['*' => Http::response(['error' => ['type' => 'temporary']], 503)]);
    $worker->handle(app(PhotoScraperService::class));
    expect($row->fresh()->status)->toBe('processing')->and($row->fresh()->poll_failures)->toBe(1);
    $worker->handle(app(PhotoScraperService::class));
    $worker->handle(app(PhotoScraperService::class));
    expect($row->fresh()->status)->toBe('failed')->and($row->fresh()->error_message)->toContain('503');
});

it('rejects foreign place IDs, bad URLs and duplicates but keeps video thumbnails', function () {
    Http::fake(['*' => Http::response([
        ['placeId' => 'ChI-foreign', 'imageUrl' => 'https://example.com/foreign.jpg'],
        ['placeId' => 'ChI-test', 'type' => 'video', 'imageUrl' => 'https://example.com/video.jpg'],
        ['placeId' => 'ChI-test', 'imageUrl' => 'data:bad'],
        ['placeId' => 'ChI-test', 'imageUrl' => 'https://example.com/good.jpg'],
        ['placeId' => 'ChI-test', 'imageUrl' => 'https://example.com/good.jpg'],
    ])]);
    expect(app(PhotoScraperService::class)->fetchPhotoRunResults('d', 't', ['ChI-test'], 8))
        ->toBe(['ChI-test' => ['https://example.com/video.jpg', 'https://example.com/good.jpg']]);
});

it('honours deadline even when the actor remains RUNNING', function () {
    $row = photoRow();
    fakePhotoRun([], 'RUNNING');
    $worker = photoWorker([$row]);
    $worker->handle(app(PhotoScraperService::class));
    $this->travel(601)->seconds();
    $worker->handle(app(PhotoScraperService::class));
    expect($row->fresh()->status)->toBe('failed')->and($row->fresh()->error_message)->toContain('600 detik');
    Http::assertSentCount(1);
});

it('rolls back gallery replacement when a post update fails', function () {
    $row = photoRow('ChI-test', ['replace_existing' => true]);
    $row->post->images()->create(['image_url' => 'https://example.com/original.jpg', 'order' => 0]);
    fakePhotoRun([['placeId' => 'ChI-test', 'imageUrl' => 'https://example.com/new.jpg']]);
    $worker = photoWorker([$row]);
    $worker->handle(app(PhotoScraperService::class));
    Post::updating(fn () => throw new RuntimeException('database unavailable'));
    try {
        $worker->handle(app(PhotoScraperService::class));
    } finally {
        Post::flushEventListeners();
        Post::clearBootedModels();
    }
    expect($row->fresh()->status)->toBe('failed');
    expect($row->post->images()->pluck('image_url')->all())->toBe(['https://example.com/original.jpg']);
});

it('upgrades legacy batches without reprocessing completed rows', function () {
    $pending = photoRow();
    $done = photoRow('ChI-done', ['status' => 'completed']);
    $worker = new ProcessBatchPhotoScrapeJob([$pending->id, $done->id]);
    $worker->handle(app(PhotoScraperService::class));
    Queue::assertPushed(ProcessBatchPhotoScrapeJob::class, 1);
    expect($pending->fresh()->processing_token)->not->toBeNull();
    expect($done->fresh()->status)->toBe('completed')->and($done->fresh()->processing_token)->toBeNull();
});

it('keeps payloads credential-free and completes with the real database queue', function () {
    Queue::swap($this->realQueue);
    $row = photoRow();
    fakePhotoRun([['placeId' => 'ChI-test', 'imageUrl' => 'https://example.com/a.jpg']]);
    app(PhotoScrapeQueue::class)->enqueue([$row->id]);
    $connection = Queue::connection('database');
    $queued = $connection->pop('photos');
    expect($queued)->not->toBeNull();
    $queued->fire();
    expect($queued->isReleased())->toBeTrue();
    $payload = DB::table('jobs')->value('payload');
    expect($payload)->not->toContain('test-secret');
    $this->travel(16)->seconds();
    $queued = $connection->pop('photos');
    $queued->fire();
    expect($row->fresh()->status)->toBe('completed')->and(DB::table('jobs')->count())->toBe(0);
});

it('terminalizes rows from Laravel queue timeout callback', function () {
    Queue::swap($this->realQueue);
    $row = photoRow();
    fakePhotoRun();
    app(PhotoScrapeQueue::class)->enqueue([$row->id]);
    $queued = Queue::connection('database')->pop('photos');
    $queued->fail(TimeoutExceededException::forJob($queued));
    expect($row->fresh()->status)->toBe('failed')->and($row->fresh()->finished_at)->not->toBeNull();
});

it('renders Filament errors and never resets active rows via header/bulk actions', function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('rahasia'));
    $active = photoRow();
    fakePhotoRun();
    photoWorker([$active])->handle(app(PhotoScraperService::class));
    $failed = photoRow('ChI-failed', ['status' => 'failed', 'error_message' => 'Apify invalid-input example']);
    Livewire::test(ListPhotoScrapeJobs::class)
        ->assertSuccessful()
        ->assertSee('Apify invalid-input example')
        ->callAction('runPendingBatch', ['target_status' => 'all_unfinished', 'limit' => '25'])
        ->assertHasNoActionErrors();
    expect($active->fresh()->status)->toBe('processing')->and($failed->fresh()->status)->toBe('pending');
    Livewire::test(ListPhotoScrapeJobs::class)
        ->callTableBulkAction('processBatchSelected', [$active, $failed])
        ->assertHasNoActionErrors();
    expect($active->fresh()->status)->toBe('processing');
    Http::assertSentCount(1);
});

it('retries transient connection failure on polling only', function () {
    $row = photoRow();
    fakePhotoRun();
    $worker = photoWorker([$row]);
    $worker->handle(app(PhotoScraperService::class));
    Http::swap(new Factory);
    Http::fake(['*' => Http::failedConnection()]);
    $worker->handle(app(PhotoScraperService::class));
    expect($row->fresh()->status)->toBe('processing')->and($row->fresh()->poll_failures)->toBe(1);
    $worker->assertReleased(15);
});

it('fails malformed dataset instead of reporting completion', function () {
    $row = photoRow();
    fakePhotoRun();
    $worker = photoWorker([$row]);
    $worker->handle(app(PhotoScraperService::class));
    Http::swap(new Factory);
    Http::fake([
        'api.apify.com/v2/actor-runs/*' => Http::response(['data' => ['status' => 'SUCCEEDED']]),
        'api.apify.com/v2/datasets/*' => Http::response('invalid json'),
    ]);
    $worker->handle(app(PhotoScraperService::class));
    expect($row->fresh()->status)->toBe('failed')->and($row->fresh()->error_message)->toContain('Dataset Apify');
});

it('keeps duplicate queued messages from starting a second actor run', function () {
    Queue::swap($this->realQueue);
    $row = photoRow();
    fakePhotoRun([], 'RUNNING');
    app(PhotoScrapeQueue::class)->enqueue([$row->id]);
    $connection = Queue::connection('database');
    $a = $connection->pop('photos');
    $connection->pushRaw($a->getRawBody(), 'photos');
    $b = $connection->pop('photos');
    $a->fire();
    $b->fire();
    Http::assertSentCount(2);
    expect($row->fresh()->phase)->toBe('polling');
});

it('rejects concurrent handling with a shared batch lock', function () {
    Queue::swap($this->realQueue);
    $row = photoRow();
    fakePhotoRun();
    app(PhotoScrapeQueue::class)->enqueue([$row->id]);
    $worker = new ProcessBatchPhotoScrapeJob([$row->id], $row->fresh()->processing_token);
    $middleware = $worker->middleware()[0];
    $lock = Cache::lock($middleware->getLockKey($worker), 80);
    $lock->get();
    try {
        $queued = Queue::connection('database')->pop('photos');
        $queued->fire();
        expect($queued->isReleased())->toBeTrue();
        Http::assertNothingSent();
    } finally {
        $lock->release();
    }
});

it('records broker dispatch failure immediately', function () {
    $row = photoRow();
    Queue::shouldReceive('connection')->andThrow(new RuntimeException('secret broker failure'));
    expect(app(PhotoScrapeQueue::class)->enqueue([$row->id]))->toBe(0);
    expect($row->fresh()->status)->toBe('failed')->and($row->fresh()->error_message)->toContain('queue backend')
        ->and($row->fresh()->processing_token)->toBeNull();
});

it('preserves the existing logo for job listings', function () {
    $row = photoRow('job-source-id');
    $category = Category::create(['name' => 'Lowongan Kerja Test', 'slug' => 'lowongan-test']);
    $row->post->update(['category_id' => $category->id, 'thumbnail' => 'https://example.com/company-logo.png']);
    photoWorker([$row])->handle(app(PhotoScraperService::class));
    expect($row->fresh()->status)->toBe('completed')->and($row->fresh()->photos_count)->toBe(1);
    Http::assertNothingSent();
});

it('stops a previously interrupted start without a new API request', function () {
    $row = photoRow();
    $worker = photoWorker([$row]);
    $row->refresh()->update(['status' => 'processing', 'phase' => 'starting', 'deadline_at' => now()->addMinutes(10)]);
    $worker->handle(app(PhotoScraperService::class));
    expect($row->fresh()->status)->toBe('failed');
    Http::assertNothingSent();
});

it('allows bulk cleanup only for terminal rows', function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('rahasia'));
    $active = photoRow();
    $done = photoRow('ChI-done', ['status' => 'completed']);
    Livewire::test(ListPhotoScrapeJobs::class)
        ->callTableBulkAction('delete', [$active, $done])->assertHasNoActionErrors();
    expect(PhotoScrapeJob::find($active->id))->not->toBeNull()->and(PhotoScrapeJob::find($done->id))->toBeNull();
});

it('bounds waiting for capacity even when the scheduler is offline', function () {
    $row = photoRow();
    $worker = photoWorker([$row]);
    $this->travel(31)->minutes();
    $worker->handle(app(PhotoScraperService::class));
    expect($row->fresh()->status)->toBe('failed')->and($row->fresh()->error_message)->toContain('30 menit');
    Http::assertNothingSent();
});

it('can roll back the additive migration without removing photo records', function () {
    $row = photoRow();
    (require database_path('migrations/2026_09_11_000000_add_run_state_to_photo_scrape_jobs.php'))->down();
    expect(Schema::hasColumn('photo_scrape_jobs', 'processing_token'))->toBeFalse();
    expect(DB::table('photo_scrape_jobs')->where('id', $row->id)->exists())->toBeTrue();
});

it('preserves the two-photo venue auto-publish threshold', function (int $count, string $status) {
    $row = photoRow('ChI-test', ['publish_on_complete' => true]);
    fakePhotoRun(array_map(fn ($i) => ['placeId' => 'ChI-test', 'imageUrl' => 'https://example.com/photo'.$i.'.jpg'], range(1, $count)));
    $worker = photoWorker([$row]);
    $worker->handle(app(PhotoScraperService::class));
    $worker->handle(app(PhotoScraperService::class));
    expect($row->fresh()->status)->toBe('completed')->and($row->post->fresh()->status)->toBe($status);
})->with([[1, 'draft'], [2, 'published']]);

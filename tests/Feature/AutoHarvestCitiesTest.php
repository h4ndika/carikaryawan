<?php

use App\Console\Commands\AutoHarvestJobsCommand;
use App\Models\ApifyAccount;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('rotates auto-harvest cities across Jabodetabek, West Java with Bandung priority, Yogyakarta, Surabaya, and Bali', function () {
    $cities = AutoHarvestJobsCommand::priorityCities();
    $counts = array_count_values($cities);

    expect($cities)->toContain('Jakarta', 'Bogor', 'Depok', 'Tangerang', 'Tangerang Selatan', 'Bekasi')
        ->and($cities)->toContain('Bandung', 'Cimahi', 'Karawang', 'Bandung Barat', 'Cirebon', 'Purwakarta', 'Sumedang', 'Subang', 'Sukabumi', 'Tasikmalaya', 'Garut')
        ->and($cities)->toContain('Yogyakarta', 'Surabaya', 'Bali', 'Denpasar')
        ->and($cities)->not->toContain('Medan', 'Makassar', 'Palembang', 'Balikpapan')
        ->and($counts['Bandung'])->toBeGreaterThan(1);

    Setting::set('auto_job_harvest_cities', json_encode(['Bandung', 'Yogyakarta']));
    expect(AutoHarvestJobsCommand::priorityCities())->toBe(['Bandung', 'Yogyakarta']);

    Setting::set('auto_job_harvest_cities', 'not-json');
    expect(AutoHarvestJobsCommand::priorityCities())->toBe(AutoHarvestJobsCommand::DEFAULT_PRIORITY_CITIES);
});

it('dispatches auto-harvest with the next city from priorityCities', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.apify.com/v2/acts/*/runs' => Http::response(['data' => ['id' => 'run-1', 'defaultDatasetId' => 'ds-1', 'status' => 'RUNNING']]),
        'api.apify.com/v2/actor-runs/run-1' => Http::response(['data' => ['status' => 'SUCCEEDED', 'defaultDatasetId' => 'ds-1']]),
        'api.apify.com/v2/datasets/ds-1/items*' => Http::response([]),
    ]);

    ApifyAccount::create(['label' => 'Test', 'token' => 'apify_test_token', 'status' => 'active']);
    Setting::set('auto_job_harvest_last_city_index', 0);

    $this->artisan('jobs:auto-harvest --engine=jobstreet --limit=5')->assertSuccessful();

    Http::assertSent(fn ($req) => str_contains($req->url(), 'shahidirfan~jobstreet-scraper/runs')
        && $req['location'] === 'Bandung');
    expect((int) Setting::get('auto_job_harvest_last_city_index'))->toBe(1);
});

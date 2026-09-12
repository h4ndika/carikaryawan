<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-process pending AI generation jobs in parallel batches of 10 every minute
Schedule::command('ai:process-batch --limit=10 --concurrency=10')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();



// Terminalize orphaned photo runs after worker crashes/SIGKILL, without resetting live work.
Artisan::command('photos:recover-stale', function () {
    $count = app(\App\Services\PhotoScrapeQueue::class)->recoverStale();
    $this->info("{$count} antrean foto kedaluwarsa ditandai gagal.");
})->purpose('Recover orphaned photo scraping jobs');

Schedule::command('photos:recover-stale')->everyMinute()->withoutOverlapping();

// Broadcast 1 konten loker / rekomendasi ke Threads 3x sehari pada jam aktif audiens (08:00, 12:30, 19:00 WIB / UTC+7)
Schedule::command('threads:broadcast')->dailyAt('01:00')->withoutOverlapping(); // 08:00 WIB (01:00 UTC)
Schedule::command('threads:broadcast')->dailyAt('05:30')->withoutOverlapping(); // 12:30 WIB (05:30 UTC)
Schedule::command('threads:broadcast')->dailyAt('12:00')->withoutOverlapping(); // 19:00 WIB (12:00 UTC)

// Auto-refresh Threads Long-Lived Token weekly (resets 60 days validity so it never expires)
Schedule::call(function () {
    app(\App\Services\ThreadsService::class)->refreshLongLivedToken();
})->weekly();

// Auto-harvest loker se-Indonesia setiap subuh jam 05:00 WIB (22:00 UTC)
Schedule::command('jobs:auto-harvest --limit=20 --engine=glints')
    ->dailyAt('22:00')
    ->withoutOverlapping()
    ->runInBackground();

// Auto-curate artikel blog harian se-Indonesia 2x sehari (06:30 WIB & 15:00 WIB)
Schedule::command('articles:auto-curate --limit=5')
    ->dailyAt('23:30') // 06:30 WIB
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('articles:auto-curate --limit=5')
    ->dailyAt('08:00') // 15:00 WIB
    ->withoutOverlapping()
    ->runInBackground();



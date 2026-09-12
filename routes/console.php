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

// Broadcast Threads 3x sehari dengan slot kategori tematik terpisah (menghindari spam & bentrok):
// 1. Pagi (08:00 WIB / 01:00 UTC): Khusus Lowongan Kerja Terbaru
Schedule::command('threads:broadcast --type=jobs')->dailyAt('01:00')->withoutOverlapping();

// 2. Siang (12:30 WIB / 05:30 UTC): Khusus Rekomendasi Tempat (Kuliner, Kafe, Wisata, Hotel)
Schedule::command('threads:broadcast --type=places')->dailyAt('05:30')->withoutOverlapping();

// 3. Malam (19:00 WIB / 12:00 UTC): Khusus Artikel Blog & Panduan Kurasi
Schedule::command('threads:broadcast --type=articles')->dailyAt('12:00')->withoutOverlapping();

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

// Drip-Publish 10 Post Tempat dari Stok Auto-Harvest (3x sehari = 30 post/hari)
// Dijadwalkan ~30 menit sebelum jadwal broadcast Threads (08:00, 12:30, 19:00 WIB)
Schedule::command('maps:drip-publish --limit=10')
    ->dailyAt('00:30') // 07:30 WIB (00:30 UTC)
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('maps:drip-publish --limit=10')
    ->dailyAt('05:00') // 12:00 WIB (05:00 UTC)
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('maps:drip-publish --limit=10')
    ->dailyAt('11:30') // 18:30 WIB (11:30 UTC)
    ->withoutOverlapping()
    ->runInBackground();

// Auto-harvest scraping Google Maps (cek sisa stok, jika stok < 50 scrape 300 data baru dari kota minim post)
// Dijalankan tengah malam jam 02:00 WIB (19:00 UTC)
Schedule::command('maps:auto-harvest --limit=300 --min-stock=50')
    ->dailyAt('19:00') // 02:00 WIB
    ->withoutOverlapping()
    ->runInBackground();




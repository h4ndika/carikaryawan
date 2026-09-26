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

// Broadcast Threads 6x sehari: 4x Loker (performa tertinggi), 1x Tempat Maps, 1x Artikel Blog:
// 1. Pagi (07:30 WIB / 00:30 UTC): Lowongan Kerja Terbaru #1
Schedule::command('threads:broadcast --type=jobs --slot=slot_1')->dailyAt('00:30')->withoutOverlapping();

// 2. Siang Menjelang Siang (10:30 WIB / 03:30 UTC): Lowongan Kerja Terbaru #2
Schedule::command('threads:broadcast --type=jobs --slot=slot_2')->dailyAt('03:30')->withoutOverlapping();

// 3. Istirahat Siang (12:30 WIB / 05:30 UTC): Rekomendasi Tempat (Kuliner, Kafe, Wisata, Hotel)
Schedule::command('threads:broadcast --type=places --slot=slot_3')->dailyAt('05:30')->withoutOverlapping();

// 4. Sore Hari (15:30 WIB / 08:30 UTC): Lowongan Kerja Terbaru #3
Schedule::command('threads:broadcast --type=jobs --slot=slot_4')->dailyAt('08:30')->withoutOverlapping();

// 5. Pulang Kerja (18:30 WIB / 11:30 UTC): Lowongan Kerja Terbaru #4
Schedule::command('threads:broadcast --type=jobs --slot=slot_5')->dailyAt('11:30')->withoutOverlapping();

// 6. Santai Malam (20:30 WIB / 13:30 UTC): Artikel Blog & Panduan Kurasi
Schedule::command('threads:broadcast --type=articles --slot=slot_6')->dailyAt('13:30')->withoutOverlapping();

// Auto-reply responder komentar di Threads (memantau hingga 100 post, baik post baru maupun lama, untuk komentar 48 jam terakhir)
Schedule::command('threads:monitor-replies --posts-limit=100 --reply-hours=48')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Auto-refresh Threads Long-Lived Token weekly (resets 60 days validity so it never expires)
Schedule::call(function () {
    app(\App\Services\ThreadsService::class)->refreshLongLivedToken();
})->weekly();

// Auto-harvest loker se-Indonesia setiap subuh jam 05:00 WIB (22:00 UTC) - Rotasi selang-seling Glints <-> Jobstreet
Schedule::command('jobs:auto-harvest --limit=20 --engine=auto')
    ->dailyAt('22:00')
    ->withoutOverlapping()
    ->runInBackground();

// Auto-curate artikel blog harian se-Indonesia 2x sehari (06:30 WIB & 15:00 WIB)
// Generate 3 artikel per run dengan limit rekomendasi acak (3, 5, 7, 10 tempat)
Schedule::command('articles:auto-curate --count=3')
    ->dailyAt('23:30') // 06:30 WIB
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('articles:auto-curate --count=3')
    ->dailyAt('08:00') // 15:00 WIB
    ->withoutOverlapping()
    ->runInBackground();

// Drip-Publish stok tempat ke AI & publish (50 post setiap 1 jam)
Schedule::command('maps:drip-publish --limit=50 --concurrency=10')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Auto-harvest scraping Google Maps (cek sisa stok, jika stok < 50 scrape 300 data baru dari kota minim post)
// Dijalankan tengah malam jam 02:00 WIB (19:00 UTC)
Schedule::command('maps:auto-harvest --limit=300 --min-stock=50')
    ->dailyAt('19:00') // 02:00 WIB
    ->withoutOverlapping()
    ->runInBackground();

// Auto-harvest Landmark Kota se-Indonesia (Prioritas kota tanpa landmark, 3 kota per eksekusi, 2x sehari jam 03:00 WIB & 14:00 WIB)
Schedule::command('landmark:auto-harvest --cities-count=3 --limit=25 --method=ai')
    ->dailyAt('20:00') // 03:00 WIB
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('landmark:auto-harvest --cities-count=3 --limit=25 --method=ai')
    ->dailyAt('07:00') // 14:00 WIB
    ->withoutOverlapping()
    ->runInBackground();




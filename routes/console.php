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

// Broadcast Threads 4x sehari, semuanya Lowongan Kerja (performa tertinggi):
// 1. Pagi (07:30 WIB / 00:30 UTC): Lowongan Kerja Terbaru #1
Schedule::command('threads:broadcast --type=jobs --slot=slot_1')->dailyAt('00:30')->withoutOverlapping();

// 2. Siang Menjelang Siang (10:30 WIB / 03:30 UTC): Lowongan Kerja Terbaru #2
Schedule::command('threads:broadcast --type=jobs --slot=slot_2')->dailyAt('03:30')->withoutOverlapping();

// 3. Sore Hari (15:30 WIB / 08:30 UTC): Lowongan Kerja Terbaru #3
Schedule::command('threads:broadcast --type=jobs --slot=slot_4')->dailyAt('08:30')->withoutOverlapping();

// 4. Pulang Kerja (18:30 WIB / 11:30 UTC): Lowongan Kerja Terbaru #4
Schedule::command('threads:broadcast --type=jobs --slot=slot_5')->dailyAt('11:30')->withoutOverlapping();

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

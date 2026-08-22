<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-process pending AI generation jobs in parallel batches of 10 every minute
Schedule::command('ai:process-batch --limit=10 --concurrency=5')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

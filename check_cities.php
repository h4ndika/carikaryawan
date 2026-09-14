<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Setting raw: " . var_export(\App\Models\Setting::get('threads_target_cities'), true) . "\n";
echo "Default cities: " . json_encode(\App\Services\ThreadsService::getDefaultCityIds()) . "\n";
$cities = \App\Models\City::whereIn('id', [276, 277, 205])->get(['id', 'name']);
foreach ($cities as $c) {
    echo "ID {$c->id}: {$c->name}\n";
}

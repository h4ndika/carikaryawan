<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Target City IDs ===\n";
$targetCities = App\Services\ThreadsService::getTargetCityIds();
echo json_encode($targetCities) . "\n\n";

echo "=== 5 Artikel Terbaru ===\n";
$articles = App\Models\Article::withTrashed()->latest('id')->limit(5)->get();
foreach ($articles as $a) {
    echo "ID: {$a->id}\n";
    echo "Title: {$a->title}\n";
    echo "Status: {$a->status}\n";
    echo "City ID: " . ($a->city_id ?? 'NULL') . "\n";
    echo "Published At: " . ($a->published_at ? $a->published_at->toDateTimeString() : 'NULL') . "\n";
    echo "Posted to Threads At: " . ($a->posted_to_threads_at ? $a->posted_to_threads_at->toDateTimeString() : 'NULL') . "\n";
    echo "Deleted At: " . ($a->deleted_at ? $a->deleted_at->toDateTimeString() : 'NULL') . "\n";
    echo "------------------------\n";
}

echo "=== Cek Query Broadcast Articles ===\n";
$articleQuery = App\Models\Article::query()
    ->published()
    ->whereNull('posted_to_threads_at');

if (!empty($targetCities)) {
    $articleQuery->where(function ($q) use ($targetCities) {
        $q->whereIn('city_id', $targetCities)
          ->orWhereNull('city_id');
    });
}
$found = $articleQuery->latest('published_at')->first();
echo "Found for broadcast: " . ($found ? "ID {$found->id} - {$found->title}" : "NONE") . "\n";

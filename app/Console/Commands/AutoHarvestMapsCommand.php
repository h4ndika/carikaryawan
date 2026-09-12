<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('maps:auto-harvest 
                            {--limit=300 : Target jumlah data tempat yang ingin di-scrape}
                            {--city-id= : ID kota tujuan spesifik (opsional)}
                            {--category-id= : ID kategori spesifik (opsional)}
                            {--min-stock=50 : Minimal sisa stok draft sebelum scrape baru dipicu}
                            {--force : Paksa scrape meskipun sisa stok pending masih banyak}')]
#[Description('Otomatis scrape tempat dari Google Maps untuk kota/kategori yang minim post dan masukkan ke antrean stok auto-harvest')]
class AutoHarvestMapsCommand extends Command
{
    /**
     * Kategori target rotasi untuk tempat umum (non-loker)
     */
    protected array $targetCategorySlugs = [
        'hotel',
        'tempat-wisata',
        'kafe',
        'restoran',
        'fasilitas',
    ];

    public function handle(
        \App\Services\ApifyScraperService $apifyService,
        \App\Services\GoogleMapsImportService $importService
    ): int {
        $enabled = (bool) \App\Models\Setting::get('maps_auto_harvest_enabled', true);
        $forced = (bool) $this->option('force');

        if (!$enabled && !$forced) {
            $this->warn('Otomasi scrape Google Maps sedang dinonaktifkan di pengaturan.');
            return self::SUCCESS;
        }

        $minStock = (int) $this->option('min-stock');
        $currentPendingStock = \App\Models\AutoHarvestQueue::where('status', 'pending')->count();

        $this->info("Stok antrean tempat (pending): {$currentPendingStock} data.");

        if ($currentPendingStock >= $minStock && !$forced) {
            $this->info("Stok antrean masih mencukupi (>= {$minStock}). Scraping tidak perlu dijalankan saat ini.");
            return self::SUCCESS;
        }

        // 1. Resolve Target Kota (Pilih kota dengan jumlah postingan paling sedikit)
        $targetCityId = $this->option('city-id');
        if ($targetCityId) {
            $city = \App\Models\City::find($targetCityId);
        } else {
            // Ambil dari kumpulan 30 kota yang memiliki postingan paling sedikit se-Indonesia
            $lowestCities = \App\Models\City::withCount('posts')
                ->orderBy('posts_count', 'asc')
                ->limit(30)
                ->get();
            $city = $lowestCities->isNotEmpty() ? $lowestCities->random() : \App\Models\City::inRandomOrder()->first();
        }

        if (!$city) {
            $this->error('Gagal menentukan kota target.');
            return self::FAILURE;
        }

        // 2. Resolve Kategori
        $targetCategoryId = $this->option('category-id');
        if ($targetCategoryId) {
            $category = \App\Models\Category::find($targetCategoryId);
        } else {
            // Pilih kategori dari daftar slug target
            $category = \App\Models\Category::whereIn('slug', $this->targetCategorySlugs)
                ->inRandomOrder()
                ->first() ?: \App\Models\Category::where('slug', 'not like', '%loker%')->inRandomOrder()->first();
        }

        if (!$category) {
            $this->error('Gagal menentukan kategori target.');
            return self::FAILURE;
        }

        // 3. Resolve Query Pencarian
        $cleanedCityName = preg_replace('/^(KAB\.|KABUPATEN|KOTA)\s+/i', '', trim($city->name));
        $query = "{$category->name} di {$cleanedCityName}";
        $location = "{$cleanedCityName}, Indonesia";
        $targetLimit = (int) ($this->option('limit') ?: 300);

        $this->info("=== Memulai Auto Harvest Google Maps ===");
        $this->line("Kota      : <comment>{$city->name}</comment> (ID: {$city->id})");
        $this->line("Kategori  : <comment>{$category->name}</comment> (ID: {$category->id})");
        $this->line("Query     : <comment>{$query}</comment>");
        $this->line("Target    : <comment>{$targetLimit} tempat</comment>");

        // 4. Resolve Akun Apify
        $account = \App\Models\ApifyAccount::getNextActiveAccount();
        $token = $account?->token ?? config('services.apify.token') ?? env('APIFY_TOKEN');

        if (!$token) {
            $this->error('Gagal: Tidak ada token Apify aktif yang tersedia.');
            \Illuminate\Support\Facades\Log::error('AutoHarvestMapsCommand: No active Apify token.');
            return self::FAILURE;
        }

        try {
            $this->info("Mengirim tugas crawling ke Apify Actor...");
            $runData = $apifyService->startGooglePlacesCrawler(
                token: $token,
                query: $query,
                location: $location,
                maxPlaces: $targetLimit,
                includeImages: true,
                includeReviews: false
            );

            $runId = $runData['runId'];
            $datasetId = $runData['datasetId'];

            $this->info("Crawler berjalan (Run ID: {$runId}). Menunggu selesai...");
            $completedRun = $apifyService->waitForRunCompletion(
                runId: $runId,
                token: $token,
                maxWaitSeconds: 600,
                pollIntervalSeconds: 5
            );

            $runStatus = $completedRun['status'] ?? 'UNKNOWN';
            if ($runStatus !== 'SUCCEEDED') {
                $snippet = $apifyService->getRunLogSnippet($runId, $token);
                $this->warn("Crawler selesai dengan status {$runStatus}: {$snippet}");
            }

            if ($account) {
                $account->recordUsage();
            }

            $rawItems = $apifyService->fetchDatasetItems(
                datasetId: $datasetId,
                token: $token,
                limit: $targetLimit + 50
            );

            $totalScraped = count($rawItems);
            $this->info("Berhasil mengunduh {$totalScraped} data mentah dari Apify.");

            if ($totalScraped === 0) {
                $this->warn("Tidak ada data yang ditemukan untuk query ini.");
                return self::SUCCESS;
            }

            // 5. Import ke Post dengan status 'draft' (tidak mencampuri antrean AI manual)
            $importOptions = [
                'skip_duplicates' => true,
                'category_id' => $category->id,
                'city_id' => $city->id,
                'status' => 'draft',
            ];

            $stats = $importService->importFromRecords($rawItems, $importOptions);

            $importedCount = $stats['imported'] ?? 0;
            $skippedCount = $stats['skipped'] ?? 0;
            $importedPostIds = $stats['imported_post_ids'] ?? [];

            $this->info("Hasil Import Database: {$importedCount} tempat baru (draft), {$skippedCount} duplikat di-skip.");

            // 6. Masukkan post baru ke tabel penampungan stok auto_harvest_queues
            $queuedCount = 0;
            foreach ($importedPostIds as $postId) {
                $exists = \App\Models\AutoHarvestQueue::where('post_id', $postId)->exists();
                if (!$exists) {
                    \App\Models\AutoHarvestQueue::create([
                        'post_id' => $postId,
                        'city_id' => $city->id,
                        'category_id' => $category->id,
                        'status' => 'pending',
                    ]);
                    $queuedCount++;
                }
            }

            $this->info("Berhasil menambahkan {$queuedCount} tempat ke Stok Auto-Harvest.");
            \Illuminate\Support\Facades\Log::info("AutoHarvestMapsCommand completed: {$importedCount} imported, {$queuedCount} added to stock.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $this->error("Auto Harvest Google Maps gagal: {$msg}");
            \Illuminate\Support\Facades\Log::error("AutoHarvestMapsCommand error: {$msg}");

            if ($account && (str_contains($msg, 'Quota Exceeded') || str_contains($msg, '402'))) {
                $account->update(['status' => 'exhausted', 'notes' => 'Otomatis ditandai limit habis oleh auto-harvest pada ' . now()]);
            }

            return self::FAILURE;
        }
    }
}

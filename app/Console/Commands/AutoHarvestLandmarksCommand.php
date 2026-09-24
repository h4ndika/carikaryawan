<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Setting;
use App\Services\LandmarkScraperService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

#[Signature('landmark:auto-harvest 
                            {--city-id= : ID kota target spesifik (opsional)}
                            {--method=ai : Metode scraping (ai, osm, apify)}
                            {--limit=25 : Target jumlah landmark per kota (default 25)}
                            {--cities-count=1 : Jumlah kota yang diproses per eksekusi}
                            {--force : Tetap jalankan meskipun setting dimatikan}
                            {--skip-existing : Lewati landmark yang sudah ada (default true)}')]
#[Description('Otomatis temukan & simpan landmark kota se-Indonesia dengan memprioritaskan kota yang belum memiliki landmark')]
class AutoHarvestLandmarksCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(LandmarkScraperService $service): int
    {
        $enabled = (bool) Setting::get('landmark_auto_harvest_enabled', true);
        $forced = (bool) $this->option('force');

        if (!$enabled && !$forced) {
            $this->warn('Otomasi harvest landmark sedang dinonaktifkan di pengaturan.');
            return self::SUCCESS;
        }

        $citiesCount = max(1, (int) ($this->option('cities-count') ?: 1));
        $method = (string) ($this->option('method') ?: 'ai');
        $limit = max(3, min(50, (int) ($this->option('limit') ?: 25)));
        $skipExisting = !$this->option('force');

        $targetCityId = $this->option('city-id');

        for ($i = 1; $i <= $citiesCount; $i++) {
            if ($citiesCount > 1) {
                $this->line("=== Memproses Kota ke-{$i} dari {$citiesCount} ===");
            }

            $city = $this->resolveTargetCity($targetCityId);

            if (!$city) {
                $this->warn('Tidak ada kota target yang perlu diproses.');
                break;
            }

            $this->processCity($service, $city, $method, $limit, $skipExisting);
        }

        return self::SUCCESS;
    }

    /**
     * Tentukan kota target dengan memprioritaskan kota yang belum memiliki landmark sama sekali,
     * atau yang memiliki jumlah landmark paling sedikit.
     */
    protected function resolveTargetCity(?string $targetCityId): ?City
    {
        if ($targetCityId) {
            return City::with('province')->find($targetCityId);
        }

        // 1. Prioritaskan kota yang memiliki post tapi belum punya landmark sama sekali
        $city = City::with('province')
            ->whereDoesntHave('landmarks')
            ->whereHas('posts')
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->first();

        // 2. Jika semua kota ber-post sudah punya landmark, cari kota apapun yang belum punya landmark
        if (!$city) {
            $city = City::with('province')
                ->whereDoesntHave('landmarks')
                ->inRandomOrder()
                ->first();
        }

        // 3. Jika SEMUA kota di Indonesia sudah punya landmark, ambil kota dengan landmark paling sedikit
        if (!$city) {
            $city = City::with('province')
                ->withCount('landmarks')
                ->orderBy('landmarks_count', 'asc')
                ->first();
        }

        return $city;
    }

    /**
     * Proses scraping dan penyimpanan landmark untuk satu kota.
     */
    protected function processCity(
        LandmarkScraperService $service,
        City $city,
        string $method,
        int $limit,
        bool $skipExisting
    ): void {
        $this->info("🔍 Memulai Auto Harvest Landmark untuk [{$city->name}] (ID: {$city->id})");
        $this->line("   • Metode : <comment>{$method}</comment>");
        $this->line("   • Target : <comment>{$limit} landmark</comment>");

        try {
            $discovered = $service->discover($city, $method, [
                'limit' => $limit,
            ]);

            if (empty($discovered)) {
                $this->warn("   ⚠️ Tidak ada landmark yang ditemukan untuk {$city->name}.");
                return;
            }

            $stats = $service->saveLandmarks($city, $discovered, $skipExisting);

            $this->info("   ✅ Berhasil memproses landmark untuk {$city->name}:");
            $this->line("      - Disimpan Baru : <info>{$stats['saved']}</info>");
            $this->line("      - Diperbarui    : <comment>{$stats['updated']}</comment>");
            $this->line("      - Dilewati      : {$stats['skipped']}");

            Log::info("AutoHarvestLandmarksCommand success for {$city->name}: {$stats['saved']} saved, {$stats['updated']} updated.");
        } catch (Throwable $e) {
            $this->error("   ❌ Gagal memproses {$city->name}: " . $e->getMessage());
            Log::error("AutoHarvestLandmarksCommand error for {$city->name}: " . $e->getMessage());
        }
    }
}

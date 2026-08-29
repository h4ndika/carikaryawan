<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Services\LandmarkScraperService;
use Illuminate\Console\Command;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ScrapeLandmarksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'landmark:scrape 
                            {city? : ID, Nama, atau Slug Kota/Kabupaten}
                            {--method=ai : Metode scraping: ai, osm, atau apify}
                            {--limit=15 : Target jumlah landmark yang dicari}
                            {--force : Update jika landmark sudah ada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape dan generate landmark otomatis berdasarkan kota (Metode: AI, OpenStreetMap, Apify)';

    /**
     * Execute the console command.
     */
    public function handle(LandmarkScraperService $service): int
    {
        $cityInput = $this->argument('city');

        if (!$cityInput) {
            $cities = City::with('province')->orderBy('name')->get();
            if ($cities->isEmpty()) {
                $this->error('Belum ada data Kota di database.');
                return self::FAILURE;
            }

            $options = $cities->mapWithKeys(fn (City $c) => [
                (string) $c->id => "{$c->name}" . ($c->province ? " ({$c->province->name})" : ''),
            ])->toArray();

            $selectedId = select(
                label: 'Pilih Kota target untuk generate Landmark:',
                options: $options,
                scroll: 15
            );

            $city = City::with('province')->find($selectedId);
        } else {
            $city = City::with('province')
                ->where('id', $cityInput)
                ->orWhere('slug', $cityInput)
                ->orWhere('name', 'like', "%{$cityInput}%")
                ->first();
        }

        if (!$city) {
            $this->error("Kota [{$cityInput}] tidak ditemukan.");
            return self::FAILURE;
        }

        $method = (string) ($this->option('method') ?: 'ai');
        if (!in_array($method, ['ai', 'osm', 'apify'])) {
            $method = select(
                label: 'Pilih metode scraping / discovery:',
                options: [
                    'ai' => '🧠 AI Generator (OpenAI GPT) — Sangat Cepat & Lengkap',
                    'osm' => '🗺️ OpenStreetMap (Overpass API) — Gratis Real-World POI',
                    'apify' => '📍 Apify Google Places Scraper — Data Google Maps',
                ],
                default: 'ai'
            );
        }

        $limit = (int) ($this->option('limit') ?: 15);
        $skipExisting = !$this->option('force');

        $this->info("🔍 Memulai pencarian landmark untuk {$city->name} menggunakan metode [{$method}] (Target: {$limit} item)...");

        try {
            $discovered = $service->discover($city, $method, ['limit' => $limit]);

            if (empty($discovered)) {
                $this->warn("Tidak ada landmark yang ditemukan untuk {$city->name}.");
                return self::SUCCESS;
            }

            $this->info("Ditemukan " . count($discovered) . " landmark. Menyimpan ke database...");

            $stats = $service->saveLandmarks($city, $discovered, $skipExisting);

            $this->table(
                ['Nama Landmark', 'Tipe', 'Latitude', 'Longitude', 'Radius (km)'],
                collect($discovered)->map(fn ($item) => [
                    $item['name'],
                    $item['type'],
                    $item['latitude'],
                    $item['longitude'],
                    $item['default_radius_km'],
                ])
            );

            $this->newLine();
            $this->info("✅ Berhasil memproses landmark untuk {$city->name}:");
            $this->line("   • {$stats['saved']} Baru Disimpan");
            $this->line("   • {$stats['updated']} Diperbarui");
            $this->line("   • {$stats['skipped']} Dilewati (Sudah Ada)");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Terjadi kesalahan: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}

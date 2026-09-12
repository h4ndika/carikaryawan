<?php

namespace App\Console\Commands;

use App\Models\ApifyAccount;
use App\Models\Category;
use App\Models\City;
use App\Models\AiGenerationJob;
use App\Models\Post;
use App\Models\Setting;
use App\Services\ApifyScraperService;
use App\Services\GoogleMapsImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutoHarvestJobsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:auto-harvest 
                            {--limit=20 : Jumlah target loker yang ingin diambil}
                            {--engine=auto : Pilihan scraper (auto, glints, atau jobstreet)}
                            {--keyword= : Override kata kunci pencarian (opsional)}
                            {--city= : Override kota tujuan (opsional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis ekstrak loker harian se-Indonesia, simpan, masukkan ke antrean AI, dan terbitkan';

    /**
     * Daftar kata kunci rotasi harian untuk menjaring variasi loker populer
     */
    protected array $keywords = [
        'Staff Admin',
        'Customer Service',
        'Staff Keuangan Finance Accounting',
        'Marketing Digital Sales',
        'Graphic Designer',
        'Content Creator Social Media',
        'Barista Resto F&B',
        'Kasir Store Crew',
        'Warehouse Staff Gudang',
        'Operator Produksi',
        'Human Resources HRD',
        'IT Support Technician',
    ];

    /**
     * Kota prioritas rotasi harian se-Indonesia
     */
    protected array $priorityCities = [
        'Jakarta',
        'Surabaya',
        'Bandung',
        'Medan',
        'Semarang',
        'Yogyakarta',
        'Denpasar',
        'Makassar',
        'Bekasi',
        'Tangerang',
        'Depok',
        'Bogor',
        'Malang',
        'Batam',
        'Balikpapan',
        'Palembang',
    ];

    public function handle(
        ApifyScraperService $apifyService,
        GoogleMapsImportService $importService
    ): int {
        $enabled = (bool) Setting::get('auto_job_harvest_enabled', true);
        if (!$enabled) {
            $this->warn('Otomasi panen loker (auto harvest) sedang dinonaktifkan di sistem.');
            return self::SUCCESS;
        }

        $targetLimit = (int) $this->option('limit');
        $inputEngine = $this->option('engine');

        // Resolve Engine (Rotasi selang-seling Glints <-> Jobstreet jika diset 'auto' atau null)
        if (empty($inputEngine) || $inputEngine === 'auto') {
            $lastEngine = Setting::get('auto_job_harvest_last_engine', 'jobstreet');
            $engine = ($lastEngine === 'glints') ? 'jobstreet' : 'glints';
            Setting::set('auto_job_harvest_last_engine', $engine);
        } else {
            $engine = $inputEngine;
        }

        // 1. Resolve Kata Kunci (Rotasi harian)
        $overrideKeyword = $this->option('keyword');
        if ($overrideKeyword) {
            $query = $overrideKeyword;
        } else {
            $lastKeywordIndex = (int) Setting::get('auto_job_harvest_last_kw_index', 0);
            $query = $this->keywords[$lastKeywordIndex % count($this->keywords)];
            Setting::set('auto_job_harvest_last_kw_index', ($lastKeywordIndex + 1) % count($this->keywords));
        }

        // 2. Resolve Kota Target (Rotasi atau Nasional)
        $overrideCityName = $this->option('city');
        $targetCityName = '';
        if ($overrideCityName) {
            $targetCityName = $overrideCityName;
        } else {
            $lastCityIndex = (int) Setting::get('auto_job_harvest_last_city_index', 0);
            $targetCityName = $this->priorityCities[$lastCityIndex % count($this->priorityCities)];
            Setting::set('auto_job_harvest_last_city_index', ($lastCityIndex + 1) % count($this->priorityCities));
        }

        $this->info("=== Memulai Auto Harvest Lowongan Kerja ===");
        $this->info("Engine   : {$engine}");
        $this->info("Kueri    : {$query}");
        $this->info("Kota     : {$targetCityName}");
        $this->info("Target   : {$targetLimit} loker");

        // 3. Resolve Apify Token
        $account = ApifyAccount::getNextActiveAccount();
        $token = $account?->token ?? config('services.apify.token') ?? env('APIFY_TOKEN');

        if (!$token) {
            $this->error('Gagal: Tidak ada token Apify aktif yang tersedia.');
            Log::error('AutoHarvestJobsCommand failed: No active Apify account/token available.');
            return self::FAILURE;
        }

        // 4. Resolve Kategori Lowongan Kerja
        $jobCategory = Category::where('slug', 'lowongan-kerja')
            ->orWhere('name', 'LIKE', '%Lowongan%')
            ->orWhere('name', 'LIKE', '%Loker%')
            ->first();

        $categoryId = $jobCategory?->id;

        // Resolve City Model if exists
        $cityModel = City::where('name', 'LIKE', "%{$targetCityName}%")->first();
        $cityId = $cityModel?->id;

        try {
            // 5. Jalankan Crawler di Apify
            $this->info("Menghubungi Apify Actor untuk {$engine}...");
            $runData = $apifyService->startJobCrawler(
                token: $token,
                query: $query,
                engine: $engine,
                location: $targetCityName,
                maxItems: $targetLimit
            );

            $runId = $runData['runId'];
            $datasetId = $runData['datasetId'];

            $this->info("Crawler berjalan (Run ID: {$runId}). Menunggu dataset...");

            $completedRun = $apifyService->waitForRunCompletion(
                runId: $runId,
                token: $token,
                maxWaitSeconds: 480,
                pollIntervalSeconds: 5
            );

            $runStatus = $completedRun['status'] ?? 'UNKNOWN';
            if ($runStatus !== 'SUCCEEDED') {
                $snippet = $apifyService->getRunLogSnippet($runId, $token);
                $this->warn("Crawler selesai dengan status {$runStatus}: {$snippet}");
                return self::SUCCESS;
            }

            // 6. Ambil Dataset dari Apify
            $rawItems = $apifyService->fetchDatasetItems($datasetId, $token);
            $totalFound = count($rawItems);
            $this->info("Berhasil mengambil {$totalFound} data mentah dari Apify.");

            if ($totalFound === 0) {
                $this->info('Tidak ada lowongan baru yang ditemukan untuk kueri ini.');
                return self::SUCCESS;
            }

            // 7. Normalisasi Data
            $normalizedItems = [];
            if ($engine === 'glints') {
                $normalizedItems = array_map(function ($item) use ($targetCityName) {
                    $pos = $item['title'] ?? 'Lowongan Kerja';
                    $comp = $item['company_name'] ?? ($item['company'] ?? 'Perusahaan');
                    $loc = is_array($item['location'] ?? null) 
                        ? ($item['location']['locality'] ?? ($item['location']['area'] ?? $targetCityName))
                        : ($item['location'] ?? $targetCityName);
                    $desc = $item['description'] ?? ($item['snippet'] ?? '');
                    $apply = $item['platform_url'] ?? ($item['url'] ?? '');
                    $logo = $item['company_logo'] ?? null;

                    $salary = '-';
                    if (!empty($item['salary_minimum']) || !empty($item['salary_maximum'])) {
                        $curr = $item['salary_currency'] ?? 'IDR';
                        $min = !empty($item['salary_minimum']) ? number_format($item['salary_minimum'], 0, ',', '.') : '';
                        $max = !empty($item['salary_maximum']) ? number_format($item['salary_maximum'], 0, ',', '.') : '';
                        $salary = $min && $max ? "{$curr} {$min} - {$max}" : "{$curr} " . ($min ?: $max);
                    }

                    $skills = is_array($item['skills'] ?? null) 
                        ? implode(', ', array_map(fn($s) => is_array($s) ? ($s['name'] ?? '') : (string)$s, $item['skills'])) 
                        : ($item['skills'] ?? '');

                    $cleanCompany = preg_replace('/^(pt\.?|cv\.?|ud\.?|yayasan)\s+/i', '', trim($comp));
                    $normCompany  = strtolower(preg_replace('/[^a-z0-9]/i', '', $cleanCompany));
                    $normPosition = strtolower(preg_replace('/[^a-z0-9]/i', '', $pos));
                    $normCity     = strtolower(preg_replace('/[^a-z0-9]/i', '', $loc));
                    $placeId      = 'job_' . substr(md5("{$normCompany}_{$normPosition}_{$normCity}"), 0, 24);

                    return [
                        'placeId' => $placeId,
                        'title' => "{$pos} - {$comp}",
                        'category' => 'Lowongan Kerja',
                        'location' => $loc,
                        'address' => $loc,
                        'description' => $desc,
                        'apply_url' => $apply,
                        'website' => $apply,
                        'images' => $logo ? [$logo] : [],
                        'details' => [
                            'posisi' => $pos,
                            'perusahaan' => $comp,
                            'lokasi' => $loc,
                            'gaji' => $salary,
                            'keahlian' => $skills,
                            'cara_melamar' => $apply ?: 'Cek deskripsi lowongan',
                        ],
                    ];
                }, $rawItems);
            } else {
                $normalizedItems = array_map(function ($item) use ($targetCityName) {
                    $pos = $item['title'] ?? ($item['job_title'] ?? 'Lowongan Kerja');
                    $comp = $item['company'] ?? ($item['company_name'] ?? 'Perusahaan');
                    $loc = $item['location'] ?? $targetCityName;
                    $desc = $item['description'] ?? ($item['snippet'] ?? '');
                    $apply = $item['url'] ?? ($item['apply_url'] ?? ($item['link'] ?? ''));
                    $salary = $item['salary'] ?? '-';
                    $workType = $item['job_type'] ?? ($item['work_type'] ?? 'Full Time');

                    $cleanCompany = preg_replace('/^(pt\.?|cv\.?|ud\.?|yayasan)\s+/i', '', trim($comp));
                    $normCompany  = strtolower(preg_replace('/[^a-z0-9]/i', '', $cleanCompany));
                    $normPosition = strtolower(preg_replace('/[^a-z0-9]/i', '', $pos));
                    $normCity     = strtolower(preg_replace('/[^a-z0-9]/i', '', $loc));
                    $placeId      = 'job_' . substr(md5("{$normCompany}_{$normPosition}_{$normCity}"), 0, 24);

                    return [
                        'placeId' => $placeId,
                        'title' => "{$pos} - {$comp}",
                        'category' => 'Lowongan Kerja',
                        'location' => $loc,
                        'address' => $loc,
                        'description' => $desc,
                        'apply_url' => $apply,
                        'website' => $apply,
                        'details' => [
                            'posisi' => $pos,
                            'perusahaan' => $comp,
                            'lokasi' => $loc,
                            'gaji' => $salary,
                            'tipe_pekerjaan' => $workType,
                            'cara_melamar' => $apply ?: 'Cek deskripsi lowongan',
                        ],
                    ];
                }, $rawItems);
            }

            // 8. Import Post ke Database (Status: draft sampai di-generate AI)
            $importOptions = [
                'skip_duplicates' => true,
                'category_id' => $categoryId,
                'city_id' => $cityId,
                'status' => 'draft',
            ];

            $stats = $importService->importFromRecords($normalizedItems, $importOptions);

            $importedCount = $stats['imported'] ?? 0;
            $skippedCount = $stats['skipped'] ?? 0;
            $importedPostIds = $stats['imported_post_ids'] ?? [];

            $this->info("Hasil Import: {$importedCount} loker baru disimpan, {$skippedCount} duplikat di-skip.");

            // 9. Masukkan Loker Baru ke Antrean AI (publish_on_complete = true)
            $queuedCount = 0;
            foreach ($importedPostIds as $postId) {
                $exists = AiGenerationJob::where('post_id', $postId)
                    ->whereIn('status', ['pending', 'processing', 'completed'])
                    ->exists();

                if (!$exists) {
                    AiGenerationJob::create([
                        'post_id' => $postId,
                        'status' => 'pending',
                        'publish_on_complete' => true,
                        'prompt_additional' => 'Fokuskan kualifikasi spesifik, ringkasan tanggung jawab harian, dan petunjuk melamar.',
                    ]);
                    $queuedCount++;
                }
            }

            $this->info("Berhasil memasukkan {$queuedCount} loker ke Antrean AI (akan diproses paralel dan otomatis diterbitkan).");
            Log::info("AutoHarvestJobsCommand completed successfully. Imported: {$importedCount}, Queued to AI: {$queuedCount}.");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            $this->error("Auto Harvest gagal: {$msg}");
            Log::error("AutoHarvestJobsCommand exception: {$msg}");

            if ($account && (str_contains($msg, 'Quota Exceeded') || str_contains($msg, '402'))) {
                $account->update(['status' => 'exhausted', 'notes' => 'Otomatis ditandai limit habis oleh auto-harvest pada ' . now()]);
            }

            return self::FAILURE;
        }
    }
}

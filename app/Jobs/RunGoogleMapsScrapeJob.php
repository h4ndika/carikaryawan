<?php

namespace App\Jobs;

use App\Models\ApifyAccount;
use App\Models\GoogleMapsScrapeJob;
use App\Services\ApifyScraperService;
use App\Services\GoogleMapsImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunGoogleMapsScrapeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900; // 15 menit
    public int $tries = 1;

    public function __construct(
        public int $scrapeJobId
    ) {}

    public function handle(
        ApifyScraperService $apifyService,
        GoogleMapsImportService $importService
    ): void {
        $scrapeJob = GoogleMapsScrapeJob::find($this->scrapeJobId);
        if (!$scrapeJob) {
            return;
        }

        // 1. Resolve Apify Account
        $account = null;
        if ($scrapeJob->apify_account_id) {
            $account = ApifyAccount::find($scrapeJob->apify_account_id);
        } else {
            $account = ApifyAccount::getNextActiveAccount();
        }

        $token = $account?->token ?? config('services.apify.token') ?? env('APIFY_TOKEN');

        if (!$token) {
            $scrapeJob->update([
                'status' => 'failed',
                'error_message' => 'Tidak ada token Apify yang tersedia. Silakan tambahkan akun aktif di menu Akun Apify.',
                'finished_at' => now(),
            ]);
            return;
        }

        // 2. Update status to scraping
        $scrapeJob->update([
            'apify_account_id' => $account?->id,
            'status' => 'scraping',
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            // 3. Start Apify Actor based on engine
            $engine = $scrapeJob->engine ?? 'google-maps';

            if ($engine === 'jobstreet' || $engine === 'glints') {
                $locationStr = $scrapeJob->location;
                if (empty($locationStr) && $scrapeJob->city) {
                    $locationStr = $scrapeJob->city->name;
                }

                // Bersihkan prefix administratif (KOTA, KABUPATEN, KAB., KODYA) agar dikenali oleh search API JobStreet/Glints
                if (!empty($locationStr)) {
                    $locationStr = trim(preg_replace('/^(KOTA|KABUPATEN|KAB\.?|KODYA)\s+/i', '', trim($locationStr)));
                }

                $runData = $apifyService->startJobCrawler(
                    token: $token,
                    query: $scrapeJob->query,
                    engine: $engine,
                    location: $locationStr,
                    maxItems: $scrapeJob->target_limit
                );
            } else {
                $locationStr = $scrapeJob->location;
                if (empty($locationStr) && $scrapeJob->city) {
                    $locationStr = $scrapeJob->city->name . ', Indonesia';
                }

                $runData = $apifyService->startGooglePlacesCrawler(
                    token: $token,
                    query: $scrapeJob->query,
                    location: $locationStr,
                    maxPlaces: $scrapeJob->target_limit,
                    includeImages: $scrapeJob->include_images,
                    includeReviews: $scrapeJob->include_reviews
                );
            }

            $runId = $runData['runId'];
            $datasetId = $runData['datasetId'];

            $scrapeJob->update([
                'apify_run_id' => $runId,
                'apify_dataset_id' => $datasetId,
            ]);

            // 4. Wait for Actor execution to complete
            $completedRun = $apifyService->waitForRunCompletion(
                runId: $runId,
                token: $token,
                maxWaitSeconds: 720,
                pollIntervalSeconds: 6
            );

            $runStatus = $completedRun['status'] ?? 'UNKNOWN';
            if ($runStatus !== 'SUCCEEDED') {
                $logSnippet = $apifyService->getRunLogSnippet($runId, $token);

                // Cek jika actor Jobstreet/Glints gagal hanya karena tidak ada lowongan ditemukan untuk query/lokasi ini
                if ($logSnippet && stripos($logSnippet, 'No jobs were extracted') !== false) {
                    $scrapeJob->update([
                        'status' => 'completed',
                        'total_scraped' => 0,
                        'total_imported' => 0,
                        'total_updated' => 0,
                        'finished_at' => now(),
                        'error_message' => 'Tidak ada lowongan ditemukan di sumber untuk kombinasi kata kunci dan kota ini (0 lowongan).',
                    ]);
                    return;
                }

                $detailStr = $logSnippet ? " - {$logSnippet}" : '';
                throw new \RuntimeException("Proses crawler Apify selesai dengan status: {$runStatus}{$detailStr}");
            }

            // 5. Fetch dataset items
            $scrapeJob->update(['status' => 'importing']);
            if ($account) {
                $account->recordUsage();
            }

            $items = $apifyService->fetchDatasetItems(
                datasetId: $datasetId,
                token: $token,
                limit: $scrapeJob->target_limit + 100
            );

            $totalScraped = count($items);
            $scrapeJob->update(['total_scraped' => $totalScraped]);

            // 6. If engine is jobstreet/glints, normalize items before import
            if ($engine === 'jobstreet') {
                $items = array_map(function ($item) {
                    $pos = $item['title'] ?? 'Lowongan Kerja';
                    $comp = $item['company'] ?? 'Perusahaan';
                    $loc = $item['location'] ?? ($item['area'] ?? '');
                    $desc = $item['description_text'] ?? ($item['description'] ?? '');
                    $salary = $item['salary'] ?? '-';
                    $apply = $item['url'] ?? '';
                    $workType = $item['workType'] ?? ($item['jobType'] ?? '');

                    // Canonical place_id for cross-platform deduplication
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
                }, $items);
            } elseif ($engine === 'glints') {
                $items = array_map(function ($item) {
                    $pos = $item['title'] ?? 'Lowongan Kerja';
                    $comp = $item['company_name'] ?? ($item['company'] ?? 'Perusahaan');
                    $loc = is_array($item['location'] ?? null) 
                        ? ($item['location']['locality'] ?? ($item['location']['area'] ?? 'Indonesia'))
                        : ($item['location'] ?? 'Indonesia');
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

                    // Canonical place_id for cross-platform deduplication
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
                }, $items);
            }

            // 7. Import directly into MySQL posts tables
            $importOptions = [
                'skip_duplicates' => false,
                'category_id' => $scrapeJob->category_id,
                'city_id' => $scrapeJob->city_id,
                'status' => $scrapeJob->publish_imported ? 'published' : 'draft',
            ];

            $stats = $importService->importFromRecords($items, $importOptions);

            $scrapeJob->update([
                'status' => 'completed',
                'total_imported' => $stats['imported'] ?? 0,
                'total_updated' => $stats['updated'] ?? 0,
                'total_skipped' => $stats['skipped'] ?? 0,
                'total_failed' => $stats['failed'] ?? 0,
                'finished_at' => now(),
            ]);

        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();

            // Jika error karena kuota habis, tandai status akun Apify
            if ($account && (str_contains($errorMessage, 'Quota Exceeded') || str_contains($errorMessage, '402'))) {
                $account->update(['status' => 'exhausted', 'notes' => 'Otomatis ditandai limit habis pada ' . now()]);
            }

            $scrapeJob->update([
                'status' => 'failed',
                'error_message' => $errorMessage,
                'finished_at' => now(),
            ]);

            Log::error("Google Maps Scrape Job #{$this->scrapeJobId} failed: {$errorMessage}");
            throw $e;
        }
    }
}

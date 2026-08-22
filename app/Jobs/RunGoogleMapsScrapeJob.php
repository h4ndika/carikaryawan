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
            // 3. Start Apify Actor
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
                throw new \RuntimeException("Proses crawler Apify selesai dengan status: {$runStatus}");
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

            // 6. Import directly into MySQL posts tables
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

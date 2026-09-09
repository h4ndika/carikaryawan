<?php

namespace App\Services;

use App\Models\ApifyAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ApifyScraperService
{
    public const DEFAULT_ACTOR = 'compass~crawler-google-places';

    /**
     * Test token validity and retrieve account information.
     */
    public function testToken(string $token): array
    {
        try {
            $response = Http::withToken($token)
                ->timeout(15)
                ->get('https://api.apify.com/v2/users/me');

            if ($response->successful()) {
                $data = $response->json('data', []);
                return [
                    'valid' => true,
                    'username' => $data['username'] ?? null,
                    'email' => $data['email'] ?? null,
                    'plan' => $data['plan']['id'] ?? 'Free',
                    'message' => 'Token valid dan terhubung ke Apify.',
                ];
            }

            if ($response->status() === 401) {
                return [
                    'valid' => false,
                    'message' => 'Token tidak valid (Unauthorized). Periksa kembali API Token.',
                ];
            }

            if ($response->status() === 402 || $response->status() === 429) {
                return [
                    'valid' => false,
                    'message' => 'Token valid tapi batas kuota/kredit tercapai (Exhausted/Rate Limited).',
                ];
            }

            return [
                'valid' => false,
                'message' => 'Gagal validasi token (HTTP ' . $response->status() . '): ' . $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'valid' => false,
                'message' => 'Error koneksi ke Apify: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Start Google Places crawler Actor on Apify.
     */
    public function startGooglePlacesCrawler(
        string $token,
        string $query,
        ?string $location = null,
        int $maxPlaces = 20,
        bool $includeImages = true,
        bool $includeReviews = false,
        string $actorId = self::DEFAULT_ACTOR
    ): array {
        $input = [
            'searchStringsArray' => [$query],
            'locationQuery' => $location ?: '',
            'maxCrawledPlacesPerSearch' => $maxPlaces,
            'language' => 'id',
            'countryCode' => 'id',
            'includeReviews' => $includeReviews,
            'maxReviews' => $includeReviews ? 5 : 0,
            'includeImages' => $includeImages,
            'maxImages' => $includeImages ? 20 : 0,
            'maxPhotos' => $includeImages ? 20 : 0,
            'photosCount' => $includeImages ? 20 : 0,
            'scrapePhotos' => $includeImages,
            'scrapePhotosCount' => $includeImages ? 20 : 0,
            'exportPlaceUrls' => true,
            'scrapeCompanyContacts' => false,
            'scrapeLeads' => false,
            'proxyConfig' => [
                'useApifyProxy' => true,
            ],
        ];

        // Format actorId for REST URL (replace slash with tilde if needed)
        $cleanActorId = str_replace('/', '~', $actorId);
        $url = "https://api.apify.com/v2/acts/{$cleanActorId}/runs";

        $response = Http::withToken($token)
            ->timeout(30)
            ->post($url, $input);

        if (!$response->successful()) {
            $status = $response->status();
            $body = $response->body();
            
            if ($status === 402 || $status === 429) {
                throw new RuntimeException("Apify Quota Exceeded (HTTP {$status}): Saldo/kuota token ini telah habis.");
            }

            throw new RuntimeException("Gagal memulai crawler Apify (HTTP {$status}): {$body}");
        }

        $data = $response->json('data', []);
        return [
            'runId' => $data['id'] ?? null,
            'actorId' => $data['actId'] ?? null,
            'datasetId' => $data['defaultDatasetId'] ?? null,
            'status' => $data['status'] ?? 'READY',
        ];
    }

    /**
     * Start Job crawler Actor on Apify (Google Jobs or Instagram).
     */
    public function startJobCrawler(
        string $token,
        string $query,
        string $engine = 'jobstreet',
        ?string $location = null,
        int $maxItems = 20
    ): array {
        if ($engine === 'glints' || $engine === 'truefetch/glints-job-listing') {
            $actorId = 'truefetch/glints-job-listing';
            $keyword = !empty($location) ? "{$query} {$location}" : $query;
            $input = [
                'keyword' => $keyword,
                'country' => 'Indonesia',
                'max_results' => $maxItems,
            ];
        } else {
            $actorId = 'shahidirfan/jobstreet-scraper';
            $input = [
                'keyword' => $query,
                'location' => $location ?? '',
                'country' => 'id',
                'results_wanted' => $maxItems,
            ];
        }

        $cleanActorId = str_replace('/', '~', $actorId);
        $url = "https://api.apify.com/v2/acts/{$cleanActorId}/runs";

        $response = Http::withToken($token)
            ->timeout(30)
            ->post($url, $input);

        if (!$response->successful()) {
            $status = $response->status();
            $body = $response->body();

            if ($status === 402 || $status === 429) {
                throw new RuntimeException("Apify Quota Exceeded (HTTP {$status}): Saldo/kuota token ini telah habis.");
            }

            throw new RuntimeException("Gagal memulai crawler lowongan Apify (HTTP {$status}): {$body}");
        }

        $data = $response->json('data', []);
        return [
            'runId' => $data['id'] ?? null,
            'actorId' => $data['actId'] ?? null,
            'datasetId' => $data['defaultDatasetId'] ?? null,
            'status' => $data['status'] ?? 'READY',
        ];
    }

    /**
     * Check run status on Apify.
     */
    public function checkRunStatus(string $runId, string $token): array
    {
        $url = "https://api.apify.com/v2/actor-runs/{$runId}";

        $response = Http::withToken($token)
            ->timeout(20)
            ->get($url);

        if (!$response->successful()) {
            throw new RuntimeException("Gagal mengecek status Apify run {$runId}: " . $response->body());
        }

        $data = $response->json('data', []);
        return [
            'status' => $data['status'] ?? 'UNKNOWN', // READY, RUNNING, SUCCEEDED, FAILED, TIMED-OUT, ABORTED
            'statusMessage' => $data['statusMessage'] ?? null,
            'datasetId' => $data['defaultDatasetId'] ?? null,
            'finishedAt' => $data['finishedAt'] ?? null,
            'exitCode' => $data['exitCode'] ?? null,
        ];
    }

    /**
     * Poll run until it completes or reaches timeout.
     */
    public function waitForRunCompletion(string $runId, string $token, int $maxWaitSeconds = 600, int $pollIntervalSeconds = 5): array
    {
        $startTime = time();

        while ((time() - $startTime) < $maxWaitSeconds) {
            $statusData = $this->checkRunStatus($runId, $token);
            $status = $statusData['status'];

            if (in_array($status, ['SUCCEEDED', 'FAILED', 'TIMED-OUT', 'ABORTED'])) {
                return $statusData;
            }

            sleep($pollIntervalSeconds);
        }

        throw new RuntimeException("Crawler Apify melewati batas waktu tunggu ({$maxWaitSeconds} detik).");
    }

    /**
     * Fetch dataset items from Apify.
     */
    public function fetchDatasetItems(string $datasetId, string $token, int $limit = 500): array
    {
        $url = "https://api.apify.com/v2/datasets/{$datasetId}/items?clean=true&limit={$limit}";

        $response = Http::withToken($token)
            ->timeout(60)
            ->get($url);

        if (!$response->successful()) {
            throw new RuntimeException("Gagal mengambil dataset {$datasetId}: " . $response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * Get error or recent log snippet from Apify run.
     */
    public function getRunLogSnippet(string $runId, string $token, int $maxLines = 3): ?string
    {
        try {
            $url = "https://api.apify.com/v2/actor-runs/{$runId}/log";
            $response = Http::withToken($token)
                ->timeout(15)
                ->get($url);

            if ($response->successful()) {
                $raw = trim($response->body());
                if (!empty($raw)) {
                    $lines = array_values(array_filter(array_map('trim', explode("\n", $raw))));
                    $errorLines = array_values(array_filter($lines, fn ($l) => stripos($l, 'Error:') !== false || stripos($l, 'Exception') !== false || stripos($l, 'No jobs were extracted') !== false));
                    if (!empty($errorLines)) {
                        $cleaned = array_map(fn($line) => preg_replace('/^\d{4}-\d{2}-\d{2}T[^\s]+\s+/', '', $line), $errorLines);
                        return implode(" | ", array_slice($cleaned, -2));
                    }
                    $cleaned = array_map(fn($line) => preg_replace('/^\d{4}-\d{2}-\d{2}T[^\s]+\s+/', '', $line), $lines);
                    return implode(" | ", array_slice($cleaned, -$maxLines));
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return null;
    }
}

<?php

namespace App\Jobs;

use App\Models\PhotoScrapeJob;
use App\Services\PhotoScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessBatchPhotoScrapeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 menit
    public int $tries = 2;

    /**
     * Create a new job instance.
     *
     * @param array<int> $photoJobIds
     */
    public function __construct(
        public array $photoJobIds
    ) {}

    /**
     * Execute the job in parallel batches of up to 10 places.
     */
    public function handle(PhotoScraperService $photoService): void
    {
        if (empty($this->photoJobIds)) {
            return;
        }

        $jobs = PhotoScrapeJob::with('post.city', 'post.province', 'post.images')
            ->whereIn('id', $this->photoJobIds)
            ->whereIn('status', ['pending', 'processing'])
            ->get();

        if ($jobs->isEmpty()) {
            return;
        }

        // 1. Mark all jobs as processing immediately
        PhotoScrapeJob::whereIn('id', $jobs->pluck('id')->all())->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ]);

        // 2. Prepare placeIds map
        $placeIdToJobs = [];
        $fallbackJobs = [];

        foreach ($jobs as $job) {
            $post = $job->post;
            if (!$post) {
                $job->update([
                    'status' => 'failed',
                    'error_message' => 'Post tidak ditemukan atau telah dihapus.',
                    'finished_at' => now(),
                ]);
                continue;
            }

            if (!empty($post->place_id) && str_starts_with($post->place_id, 'ChI')) {
                $placeIdToJobs[$post->place_id][] = $job;
            } else {
                $fallbackJobs[] = $job;
            }
        }

        // 3. Process all placeIds in parallel batch via Apify (up to 10 at once)
        $scrapedPhotosByPlaceId = [];
        $placeIds = array_keys($placeIdToJobs);

        if (!empty($placeIds)) {
            try {
                // Maximum limit requested among jobs
                $maxLimit = $jobs->max('limit') ?: 8;
                // Chunk to 3 places max per Apify run to guarantee ~20-30s turnaround and avoid container timeouts
                foreach (array_chunk($placeIds, 3) as $chunkPlaceIds) {
                    $chunkPhotos = $photoService->scrapeBatchFromApifyActor($chunkPlaceIds, null, $maxLimit);
                    foreach ($chunkPhotos as $pid => $pList) {
                        $scrapedPhotosByPlaceId[$pid] = $pList;
                    }
                }
            } catch (Throwable $e) {
                Log::warning("Batch Apify parallel scrape error: " . $e->getMessage());
            }
        }

        // 4. Attach results to posts
        foreach ($placeIdToJobs as $placeId => $matchedJobs) {
            $photos = $scrapedPhotosByPlaceId[$placeId] ?? [];

            foreach ($matchedJobs as $job) {
                $post = $job->post;

                try {
                    if (!empty($photos)) {
                        $result = $photoService->attachPhotosToPost(
                            post: $post,
                            photos: array_slice($photos, 0, $job->limit ?: 8),
                            replaceExisting: (bool) $job->replace_existing,
                            publishOnComplete: (bool) $job->publish_on_complete
                        );

                        $count = $result['count'] ?? 0;
                        $total = $result['total'] ?? $count;

                        if ($total < 1) {
                            $job->update([
                                'status' => 'failed',
                                'photos_count' => 0,
                                'error_message' => 'Tidak ada foto yang ditemukan dari sumber.',
                                'finished_at' => now(),
                            ]);
                        } else {
                            $job->update([
                                'status' => 'completed',
                                'photos_count' => $total,
                                'error_message' => null,
                                'finished_at' => now(),
                            ]);
                        }
                    } else {
                        // Fallback single scrape if batch didn't return photos for this specific placeId
                        $result = $photoService->scrapeAndAttachForPost(
                            post: $post,
                            limit: $job->limit ?: 8,
                            replaceExisting: (bool) $job->replace_existing,
                            publishOnComplete: (bool) $job->publish_on_complete
                        );

                        $count = $result['count'] ?? 0;
                        $total = $result['total'] ?? $count;

                        if ($total < 1) {
                            $isJob = $post->isJob();
                            $job->update([
                                'status' => $isJob ? 'completed' : 'failed',
                                'photos_count' => $count,
                                'error_message' => $isJob ? 'Lowongan kerja: Logo resmi tidak ditemukan di web (menggunakan kartu standar).' : 'Tidak ada foto yang ditemukan dari semua sumber.',
                                'finished_at' => now(),
                            ]);
                        } else {
                            $job->update([
                                'status' => 'completed',
                                'photos_count' => $total,
                                'error_message' => null,
                                'finished_at' => now(),
                            ]);
                        }
                    }
                } catch (Throwable $e) {
                    Log::error("Photo scrape failed for Job #{$job->id} (Post #{$post->id}): " . $e->getMessage());
                    $job->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'finished_at' => now(),
                    ]);
                }
            }
        }

        // 5. Process any fallback jobs (places without place_id)
        foreach ($fallbackJobs as $job) {
            $post = $job->post;
            try {
                $result = $photoService->scrapeAndAttachForPost(
                    post: $post,
                    limit: $job->limit ?: 8,
                    replaceExisting: (bool) $job->replace_existing,
                    publishOnComplete: (bool) $job->publish_on_complete
                );

                $count = $result['count'] ?? 0;
                $total = $result['total'] ?? $count;

                if ($total < 1) {
                    $isJob = $post->isJob();
                    $job->update([
                        'status' => $isJob ? 'completed' : 'failed',
                        'photos_count' => $count,
                        'error_message' => $isJob ? 'Lowongan kerja: Logo resmi tidak ditemukan di web (menggunakan kartu standar).' : 'Tidak ada foto yang ditemukan dari semua sumber.',
                        'finished_at' => now(),
                    ]);
                } else {
                    $job->update([
                        'status' => 'completed',
                        'photos_count' => $total,
                        'error_message' => null,
                        'finished_at' => now(),
                    ]);
                }
            } catch (Throwable $e) {
                Log::error("Photo scrape failed for Job #{$job->id} (Post #{$post->id}): " . $e->getMessage());
                $job->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'finished_at' => now(),
                ]);
            }
        }
    }
}

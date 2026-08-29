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
     * Execute the job.
     */
    public function handle(PhotoScraperService $photoService): void
    {
        if (empty($this->photoJobIds)) {
            return;
        }

        $jobs = PhotoScrapeJob::with('post.city', 'post.province')
            ->whereIn('id', $this->photoJobIds)
            ->where('status', 'pending')
            ->get();

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

            $job->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            try {
                $result = $photoService->scrapeAndAttachForPost(
                    post: $post,
                    limit: $job->limit ?: 8,
                    replaceExisting: (bool) $job->replace_existing,
                    publishOnComplete: (bool) $job->publish_on_complete
                );

                $count = $result['count'] ?? 0;
                $total = $result['total'] ?? $count;

                if ($total <= 1) {
                    $job->update([
                        'status' => 'failed',
                        'photos_count' => $count,
                        'error_message' => $count === 0 
                            ? 'Tidak ada foto yang ditemukan dari semua sumber.' 
                            : 'Hanya 1 foto ditemukan (minimal 2 foto agar valid).',
                        'finished_at' => now(),
                    ]);
                } else {
                    $job->update([
                        'status' => 'completed',
                        'photos_count' => $count,
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

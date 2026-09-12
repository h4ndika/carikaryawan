<?php

namespace App\Jobs;

use App\Exceptions\PhotoScrapeException;
use App\Models\PhotoScrapeJob;
use App\Services\PhotoScrapeQueue;
use App\Services\PhotoScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessBatchPhotoScrapeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Less than the default database/Redis retry_after (90 seconds).
    public int $timeout = 75;

    public int $tries = 0; // releases for polling/capacity are not failures

    public int $maxExceptions = 1;

    public bool $failOnTimeout = true;

    public ?string $processingToken = null;

    public function __construct(public array $photoJobIds, ?string $processingToken = null)
    {
        $this->onQueue('photos');
        $this->processingToken = $processingToken;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('photo-batch:'.($this->processingToken ?? sha1(json_encode($this->photoJobIds)))))
            ->releaseAfter(5)->expireAfter(80)];
    }

    public function handle(PhotoScraperService $service): void
    {
        // Upgrade old serialized batches (including batches of 25) safely.
        if (! $this->processingToken) {
            $pending = PhotoScrapeJob::whereIn('id', $this->photoJobIds)
                ->where('status', 'pending')->whereNull('processing_token')->pluck('id')->all();
            app(PhotoScrapeQueue::class)->enqueue($pending);

            return;
        }

        $jobs = $this->owned()->with('post.category', 'post.city', 'post.province', 'post.images')->get();
        if ($jobs->isEmpty()) {
            return;
        }
        if ($jobs->first()->status === 'pending') {
            if ($jobs->first()->updated_at->lt(now()->subMinutes(30))) {
                $this->failed(new PhotoScrapeException('Batas waktu menunggu worker/slot terlewati (30 menit). Kurangi batch atau tambah kapasitas worker.'));

                return;
            }
            // Lock only while claiming a capacity slot, never during network I/O.
            $lock = Cache::lock('photo-scrape:capacity', 10);
            if (! $lock->get()) {
                $this->release(15);

                return;
            }
            try {
                $active = PhotoScrapeJob::where('status', 'processing')->whereNotNull('processing_token')
                    ->distinct()->count('processing_token');
                if ($active >= max(1, (int) config('services.apify.photo_max_active_runs', 3))) {
                    $this->release(15);

                    return;
                }
                $this->owned()->where('status', 'pending')->update([
                    'phase' => 'preparing', 'status' => 'processing', 'started_at' => now(),
                    'deadline_at' => now()->addSeconds(PhotoScrapeQueue::RUN_SECONDS),
                ]);
            } finally {
                $lock->release();
            }
            $jobs = $this->owned()->with('post.category', 'post.city', 'post.province', 'post.images')->get();
        }

        try {
            if ($jobs->contains(fn ($job) => ! $job->deadline_at || $job->deadline_at->isPast())) {
                throw new PhotoScrapeException('Batas waktu scrape foto terlewati (600 detik). Periksa run Apify dan worker sebelum mengulang.');
            }
            $handledLogo = false;
            $placeIds = [];
            foreach ($jobs as $row) {
                if (! $row->post) {
                    $this->finish($row->id, 'failed', 0, 'Post tidak ditemukan atau telah dihapus.');
                } elseif ($row->post->isJob()) {
                    $handledLogo = true;
                    try {
                        $result = $service->scrapeAndAttachForPost($row->post, $row->limit, $row->replace_existing, $row->publish_on_complete);
                        $ok = ($result['total'] ?? 0) > 0;
                        $this->finish($row->id, $ok ? 'completed' : 'failed', $result['total'] ?? 0, $ok ? null : ($result['message'] ?? 'Logo tidak ditemukan.'));
                    } catch (Throwable $e) {
                        $this->finish($row->id, 'failed', 0, 'Gagal mengambil/menyimpan logo perusahaan.');
                    }
                } elseif (! str_starts_with($row->post->place_id ?? '', 'ChI')) {
                    $this->finish($row->id, 'failed', 0, 'Google Maps place_id tidak valid/tidak tersedia. Import ulang data tempat terlebih dahulu.');
                } else {
                    $placeIds[] = $row->post->place_id;
                }
            }
            $jobs = $this->owned()->with('post')->get();
            if ($jobs->isEmpty()) {
                return;
            }
            $first = $jobs->first();
            $limit = max(1, min(100, (int) $jobs->max('limit')));
            $placeIds = array_values(array_unique($placeIds));
            if (! $first->apify_run_id) {
                if ($handledLogo) {
                    $this->release(1);

                    return;
                }
                if ($first->phase === 'starting') {
                    throw new PhotoScrapeException('Apify start sebelumnya terputus. Periksa Runs di Apify sebelum mengulang; run mungkin sudah dibuat.');
                }
                if (! $this->owned()->update(['phase' => 'starting'])) {
                    return;
                }
                $run = $service->startPhotoRun($placeIds, null, $limit);
                // Apply encryption before one atomic, ownership-guarded update.
                $encrypted = (new PhotoScrapeJob(['apify_token' => $run['token']]))->getAttributes()['apify_token'];
                $this->owned()->update(['phase' => 'polling', 'apify_run_id' => $run['id'], 'apify_dataset_id' => $run['dataset_id'], 'apify_token' => $encrypted]);
                $this->release(15);

                return;
            }
            if ($service->photoRunStatus($first->apify_run_id, $first->apify_token) !== 'SUCCEEDED') {
                $this->owned()->update(['poll_failures' => 0, 'error_message' => null]);
                $this->release(15);

                return;
            }
            $photos = $service->fetchPhotoRunResults($first->apify_dataset_id, $first->apify_token, $placeIds, $limit);
            // Persist successes independently; empty results never start another paid scrape.
            foreach ($jobs as $row) {
                try {
                    DB::transaction(function () use ($row, $service, $photos) {
                        $current = $this->owned()->whereKey($row->id)->lockForUpdate()->first();
                        if (! $current) {
                            return;
                        }
                        $post = $current->post()->lockForUpdate()->first();
                        $images = $post ? ($photos[$post->place_id] ?? []) : [];
                        if (! $post || ! $images) {
                            $this->finish($row->id, 'failed', 0, $post ? 'Run Apify selesai tetapi tidak menghasilkan foto valid untuk tempat ini.' : 'Post telah dihapus.');

                            return;
                        }
                        $result = $service->attachPhotosToPost($post, array_slice($images, 0, max(1, min(100, (int) $current->limit))), $current->replace_existing, $current->publish_on_complete);
                        $this->finish($row->id, 'completed', $result['total']);
                    });
                } catch (Throwable $e) {
                    $this->finish($row->id, 'failed', 0, 'Gagal menyimpan foto. Perubahan galeri dibatalkan; periksa database/storage.');
                }
            }
        } catch (Throwable $e) {
            if ($e instanceof PhotoScrapeException && $e->retryable) {
                $failures = (int) $this->owned()->max('poll_failures') + 1;
                if ($failures < 3) {
                    $this->owned()->update(['poll_failures' => $failures, 'error_message' => $e->getMessage()]);
                    $this->release(15);

                    return;
                }
            }
            $this->failed($e);
        }
    }

    private function owned(): Builder
    {
        return PhotoScrapeJob::whereIn('id', $this->photoJobIds)
            ->where('processing_token', $this->processingToken)
            ->whereIn('status', ['pending', 'processing']);
    }

    private function finish(int $id, string $status, int $count, ?string $error = null): void
    {
        $this->owned()->whereKey($id)->update([
            'phase' => $status, 'status' => $status, 'photos_count' => $count, 'error_message' => $error,
            'finished_at' => now(), 'processing_token' => null, 'apify_token' => null,
        ]);
    }

    public function failed(?Throwable $e): void
    {
        if (! $this->processingToken) {
            return;
        }
        $message = $e instanceof PhotoScrapeException
            ? $e->getMessage() : 'Worker scrape foto gagal/timeout. Periksa queue worker dan run Apify sebelum mengulang.';
        $this->owned()->update([
            'phase' => 'failed', 'status' => 'failed', 'error_message' => $message,
            'finished_at' => now(), 'processing_token' => null, 'apify_token' => null,
        ]);
    }
}

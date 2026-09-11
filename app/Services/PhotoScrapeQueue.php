<?php

namespace App\Services;

use App\Jobs\ProcessBatchPhotoScrapeJob;
use App\Models\PhotoScrapeJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PhotoScrapeQueue
{
    public const BATCH_SIZE = 3;

    public const RUN_SECONDS = 600;

    /** Atomically reserve rows before dispatch; double clicks never reset live work. */
    public function enqueue(array $ids): int
    {
        $connection = config('queue.default');
        if (in_array(config("queue.connections.{$connection}.driver"), ['sync', 'null', 'deferred', 'background'], true)) {
            throw new RuntimeException('Scrape foto membutuhkan queue asynchronous (database/redis) dan queue:work aktif.');
        }

        sort($ids);
        $count = 0;
        foreach (array_chunk(array_values(array_unique($ids)), self::BATCH_SIZE) as $chunk) {
            $reservation = DB::transaction(function () use ($chunk) {
                $rows = PhotoScrapeJob::whereIn('id', $chunk)
                    ->whereIn('status', ['pending', 'failed', 'completed'])
                    ->whereNull('processing_token')->lockForUpdate()->get();
                if ($rows->isEmpty()) {
                    return null;
                }
                $token = (string) Str::uuid();
                foreach ($rows as $row) {
                    $row->update([
                        'poll_failures' => 0, 'phase' => 'queued', 'status' => 'pending', 'processing_token' => $token,
                        'photos_count' => 0, 'error_message' => null,
                        'started_at' => null, 'finished_at' => null, 'deadline_at' => null,
                        'apify_run_id' => null, 'apify_dataset_id' => null, 'apify_token' => null,
                    ]);
                }

                return ['ids' => $rows->modelKeys(), 'token' => $token];
            });
            if (! $reservation) {
                continue;
            }
            try {
                ProcessBatchPhotoScrapeJob::dispatch($reservation['ids'], $reservation['token'])
                    ->onConnection($connection)->afterCommit();
                $count += count($reservation['ids']);
            } catch (\Throwable $e) {
                // A broker outage should not leave a misleading pending row.
                PhotoScrapeJob::where('processing_token', $reservation['token'])->where('status', 'pending')->update([
                    'status' => 'failed', 'phase' => 'failed', 'processing_token' => null, 'finished_at' => now(),
                    'error_message' => 'Gagal mengirim ke queue backend. Periksa koneksi worker/Redis/database lalu ulangi.',
                ]);
            }
        }

        return $count;
    }

    /** Also covers SIGKILL/OOM or a lost queue message, where failed() cannot run. */
    public function recoverStale(): int
    {
        return PhotoScrapeJob::where(function ($query) {
            $query->where(function ($q) {
                $q->where('status', 'processing')->where(function ($q) {
                    $q->where('deadline_at', '<', now()->subSeconds(90))
                        ->orWhere(function ($q) {
                            $q->whereNull('deadline_at')->where(function ($q) {
                                $q->where('started_at', '<', now()->subSeconds(self::RUN_SECONDS + 90))
                                    ->orWhere(function ($q) {
                                        $q->whereNull('started_at')->where('updated_at', '<', now()->subSeconds(self::RUN_SECONDS + 90));
                                    });
                            });
                        });
                });
            })->orWhere(function ($q) {
                $q->where('status', 'pending')->whereNotNull('processing_token')
                    ->where('updated_at', '<', now()->subMinutes(30));
            });
        })->update([
            'phase' => 'failed', 'status' => 'failed', 'processing_token' => null, 'apify_token' => null,
            'finished_at' => now(),
            'error_message' => 'Batas waktu antrean terlewati. Worker mungkin berhenti/timeout atau antrean terlalu padat. Periksa queue:work lalu ulangi job ini.',
        ]);
    }
}

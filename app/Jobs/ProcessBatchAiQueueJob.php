<?php

namespace App\Jobs;

use App\Models\AiGenerationJob;
use App\Services\PostAiContentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBatchAiQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Timeout for the batch job in seconds.
     * 10 posts with concurrency 5 take ~20-30s, timeout 300s is safe.
     */
    public int $timeout = 300;

    /**
     * Number of attempts before failing.
     */
    public int $tries = 2;

    /**
     * Create a new job instance.
     *
     * @param array<int> $aiJobIds
     * @param int $concurrency
     */
    public function __construct(
        public array $aiJobIds,
        public int $concurrency = 10
    ) {}

    /**
     * Execute the job.
     */
    public function handle(PostAiContentService $aiService): void
    {
        if (empty($this->aiJobIds)) {
            return;
        }

        $jobs = AiGenerationJob::with('post.category', 'post.city', 'post.province')
            ->whereIn('id', $this->aiJobIds)
            ->where('status', 'pending')
            ->get();

        if ($jobs->isNotEmpty()) {
            $aiService->generateBatchConcurrent($jobs, $this->concurrency);
        }
    }
}

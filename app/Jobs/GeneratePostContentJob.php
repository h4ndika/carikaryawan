<?php

namespace App\Jobs;

use App\Models\AiGenerationJob;
use App\Models\Post;
use App\Services\PostAiContentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GeneratePostContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;
    public int $tries = 2;

    public function __construct(
        public int $aiJobId
    ) {}

    public function handle(PostAiContentService $aiService): void
    {
        $aiJob = AiGenerationJob::find($this->aiJobId);
        if (!$aiJob) {
            return;
        }

        $post = Post::find($aiJob->post_id);
        if (!$post) {
            $aiJob->update([
                'status' => 'failed',
                'error_message' => 'Post tidak ditemukan (mungkin sudah dihapus).',
                'finished_at' => now(),
            ]);
            return;
        }

        $aiJob->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            $aiService->generateForPost(
                post: $post,
                additionalPrompt: $aiJob->prompt_additional,
                publishOnComplete: $aiJob->publish_on_complete
            );

            $aiJob->update([
                'status' => 'completed',
                'finished_at' => now(),
            ]);
        } catch (Throwable $e) {
            $aiJob->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            throw $e;
        }
    }
}

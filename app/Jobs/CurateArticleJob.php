<?php

namespace App\Jobs;

use App\Services\ArticleCuratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class CurateArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 240;
    public int $tries = 2;
    public string $queue = 'ai';

    public function __construct(
        public int $cityId,
        public ?int $categoryId = null,
        public int $limit = 5,
        public ?string $customAngle = null
    ) {}

    public function handle(ArticleCuratorService $curator): void
    {
        Log::info("CurateArticleJob: Memulai kurasi artikel otomatis untuk city_id={$this->cityId}, category_id={$this->categoryId}, limit={$this->limit}");

        try {
            $article = $curator->curateListicle(
                cityId: $this->cityId,
                categoryId: $this->categoryId,
                limit: $this->limit,
                customAngle: $this->customAngle
            );

            Log::info("CurateArticleJob: Berhasil membuat artikel '{$article->title}' (ID: {$article->id})");
        } catch (Throwable $e) {
            Log::error("CurateArticleJob: Gagal membuat artikel - " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

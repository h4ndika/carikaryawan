<?php

namespace App\Console\Commands;

use App\Models\AiGenerationJob;
use App\Services\PostAiContentService;
use Illuminate\Console\Command;

class ProcessBatchAiQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:process-batch 
                            {--limit=10 : Jumlah maksimal antrean yang diproses} 
                            {--concurrency=5 : Jumlah request simultan/paralel ke AI}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memproses antrean generate konten AI secara paralel/concurrent batch';

    /**
     * Execute the console command.
     */
    public function handle(PostAiContentService $aiService): int
    {
        $limit = (int) $this->option('limit');
        $concurrency = (int) $this->option('concurrency');

        $pendingJobs = AiGenerationJob::with('post.category', 'post.city', 'post.province')
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        $count = $pendingJobs->count();
        if ($count === 0) {
            $this->info('Tidak ada antrean AI yang berstatus pending.');
            return self::SUCCESS;
        }

        $this->info("Memulai pemrosesan {$count} antrean AI secara paralel (Concurrency: {$concurrency})...");
        $startTime = microtime(true);

        $result = $aiService->generateBatchConcurrent($pendingJobs, $concurrency);

        $duration = round(microtime(true) - $startTime, 2);

        $this->info("Selesai dalam {$duration}s!");
        $this->table(
            ['Total', 'Berhasil', 'Gagal', 'Durasi'],
            [[$result['total'], $result['completed'], $result['failed'], "{$duration}s"]]
        );

        return self::SUCCESS;
    }
}

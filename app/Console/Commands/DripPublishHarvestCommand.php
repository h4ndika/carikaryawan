<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('maps:drip-publish 
                            {--limit=10 : Jumlah post yang di-generate AI dan diterbitkan}
                            {--concurrency=10 : Jumlah paralel request ke AI}')]
#[Description('Ambil sejumlah post draft dari stok auto-harvest, generate konten AI, dan ubah status jadi published')]
class DripPublishHarvestCommand extends Command
{
    public function handle(\App\Services\PostAiContentService $aiService): int
    {
        $enabled = (bool) \App\Models\Setting::get('maps_auto_publish_enabled', true);
        if (!$enabled) {
            $this->warn('Drip publish otomatis sedang dinonaktifkan di pengaturan.');
            return self::SUCCESS;
        }

        $limit = (int) ($this->option('limit') ?: 10);
        $concurrency = (int) ($this->option('concurrency') ?: 10);

        // Ambil antrean pending dari stok auto-harvest
        $queueItems = \App\Models\AutoHarvestQueue::with('post.category', 'post.city', 'post.province')
            ->where('status', 'pending')
            ->whereHas('post', fn ($q) => $q->where('status', 'draft'))
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        $count = $queueItems->count();
        if ($count === 0) {
            $this->info('Tidak ada stok antrean tempat yang pending.');
            return self::SUCCESS;
        }

        $this->info("Memproses {$count} tempat dari stok auto-harvest untuk di-generate AI & publish (Concurrency: {$concurrency})...");

        // Tandai processing
        $queueIds = $queueItems->pluck('id')->toArray();
        \App\Models\AutoHarvestQueue::whereIn('id', $queueIds)->update(['status' => 'processing']);

        $publishedCount = 0;
        $failedCount = 0;

        foreach ($queueItems as $item) {
            $post = $item->post;
            if (!$post) {
                $item->update(['status' => 'failed', 'error_message' => 'Post tidak ditemukan']);
                $failedCount++;
                continue;
            }

            try {
                // Generate konten AI dan langsung publish
                $aiService->generateAndSaveContent(
                    post: $post,
                    publishOnComplete: true,
                    additionalPrompt: 'Buat ulasan komprehensif, menarik, dan informatif untuk pengunjung tentang daya tarik tempat ini.'
                );

                $item->update([
                    'status' => 'published',
                    'published_at' => now(),
                    'error_message' => null,
                ]);

                $publishedCount++;
                $this->line("✓ Berhasil terbit: <info>[ID: {$post->id}] {$post->title}</info>");
            } catch (\Throwable $e) {
                $item->update([
                    'status' => 'failed',
                    'error_message' => \Illuminate\Support\Str::limit($e->getMessage(), 500),
                ]);
                $failedCount++;
                $this->error("✗ Gagal [ID: {$post->id}]: " . $e->getMessage());
            }
        }

        $this->info("=== Selesai Drip Publish ===");
        $this->info("Berhasil diterbitkan: {$publishedCount} post");
        if ($failedCount > 0) {
            $this->warn("Gagal: {$failedCount} post");
        }

        \Illuminate\Support\Facades\Log::info("DripPublishHarvestCommand: {$publishedCount} published, {$failedCount} failed.");

        return self::SUCCESS;
    }
}

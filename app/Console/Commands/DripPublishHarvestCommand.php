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

        $limit = (int) ($this->option('limit') ?: 50);
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

        // Base AI Endpoint & Auth
        $baseUri = config('openai.base_uri') ?: 'https://api.openai.com/v1';
        if (!str_starts_with($baseUri, 'http://') && !str_starts_with($baseUri, 'https://')) {
            $baseUri = 'https://' . $baseUri;
        }
        $endpoint = rtrim($baseUri, '/') . '/chat/completions';
        $apiKey = config('openai.api_key');
        $headers = [
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ];
        if ($org = config('openai.organization')) {
            $headers['OpenAI-Organization'] = $org;
        }
        if ($project = config('openai.project')) {
            $headers['OpenAI-Project'] = $project;
        }
        $model = config('openai.model', 'gpt-4o-mini');

        // Chunking by concurrency
        $chunks = $queueItems->chunk(max(1, $concurrency));

        foreach ($chunks as $chunk) {
            $responses = \Illuminate\Support\Facades\Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($chunk, $aiService, $endpoint, $headers, $model) {
                $requests = [];
                foreach ($chunk as $item) {
                    $post = $item->post;
                    if (!$post) {
                        continue;
                    }
                    $prompt = $aiService->buildPrompt($post, 'Buat ulasan komprehensif, menarik, dan informatif untuk pengunjung tentang daya tarik tempat ini.');
                    $requests[(string) $item->id] = $pool->as((string) $item->id)
                        ->withHeaders($headers)
                        ->timeout(180)
                        ->post($endpoint, [
                            'model' => $model,
                            'messages' => [
                                ['role' => 'system', 'content' => 'Kamu adalah jurnalis dan editor konten profesional. JAWAB HANYA dengan konten HTML artikel langsung (dimulai dengan <h2>/<h1> judul) tanpa pengantar, tanpa pemikiran/thinking, tanpa kata pembuka, dan tanpa penutup.'],
                                ['role' => 'user', 'content' => $prompt],
                            ],
                            'temperature' => 0.7,
                            'max_tokens' => 3000,
                            'stream' => false,
                        ]);
                }
                return $requests;
            });

            foreach ($chunk as $item) {
                $post = $item->post;
                if (!$post) {
                    $item->update(['status' => 'failed', 'error_message' => 'Post tidak ditemukan']);
                    $failedCount++;
                    continue;
                }

                $itemKey = (string) $item->id;
                $res = $responses[$itemKey] ?? null;

                if ($res instanceof \Throwable) {
                    $item->update([
                        'status' => 'failed',
                        'error_message' => \Illuminate\Support\Str::limit($res->getMessage(), 500),
                    ]);
                    $failedCount++;
                    $this->error("✗ Gagal [ID: {$post->id}]: " . $res->getMessage());
                    continue;
                }

                if (!$res || !$res->successful()) {
                    $errText = $res ? ($res->json('error.message') ?? $res->body()) : 'Request AI gagal atau timeout.';
                    $item->update([
                        'status' => 'failed',
                        'error_message' => \Illuminate\Support\Str::limit($errText, 500),
                    ]);
                    $failedCount++;
                    $this->error("✗ Gagal [ID: {$post->id}]: {$errText}");
                    continue;
                }

                try {
                    $rawGenerated = \App\Services\PostAiContentService::parseAiResponseContent($res->body());
                    $aiService->applyGeneratedContent($post, $rawGenerated, true);

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

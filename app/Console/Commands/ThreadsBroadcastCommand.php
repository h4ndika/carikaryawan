<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('threads:broadcast {--post-id= : ID postingan spesifik yang ingin di-publish} {--article-id= : ID artikel blog spesifik yang ingin di-publish} {--type=all : Tipe konten (all, jobs, places, articles)}')]
#[Description('Publikasikan artikel blog / lowongan kerja / tempat terbaru ke akun resmi Threads')]
class ThreadsBroadcastCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(\App\Services\ThreadsService $threadsService)
    {
        $enabled = (bool) \App\Models\Setting::get('threads_auto_post_enabled', true);
        $specificPostId = $this->option('post-id');
        $specificArticleId = $this->option('article-id');

        if (!$enabled && !$specificPostId && !$specificArticleId) {
            $this->warn('Otomasi Threads sedang dinonaktifkan di pengaturan.');
            return self::SUCCESS;
        }

        if (!$threadsService->isConfigured()) {
            $this->error('Kredensial Threads (User ID / Access Token) belum diatur.');
            return self::FAILURE;
        }

        // 1. Jika ada specific article ID yang diminta
        if ($specificArticleId) {
            $article = \App\Models\Article::query()->published()->find($specificArticleId);
            if (!$article) {
                $this->error("Artikel ID {$specificArticleId} tidak ditemukan atau belum published.");
                return self::FAILURE;
            }

            $this->info("Memublikasikan Artikel Blog ke Threads: [ID: {$article->id}] {$article->title}");
            try {
                $res = $threadsService->publishArticle($article);
                $this->info('Berhasil terbit di Threads! Thread ID: ' . ($res['id'] ?? 'N/A'));
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error('Gagal memublikasikan ke Threads: ' . $e->getMessage());
                return self::FAILURE;
            }
        }

        // 2. Jika ada specific post ID yang diminta
        if ($specificPostId) {
            $post = \App\Models\Post::query()->where('status', 'published')->find($specificPostId);
            if (!$post) {
                $this->error("Postingan ID {$specificPostId} tidak ditemukan atau belum published.");
                return self::FAILURE;
            }

            $this->info("Memublikasikan Tempat/Loker ke Threads: [ID: {$post->id}] {$post->title}");
            try {
                $res = $threadsService->publishPost($post);
                $this->info('Berhasil terbit di Threads! Thread ID: ' . ($res['id'] ?? 'N/A'));
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error('Gagal memublikasikan ke Threads: ' . $e->getMessage());
                return self::FAILURE;
            }
        }

        // 3. Mode Antrean Otomatis Berdasarkan Tipe
        $type = $this->option('type') ?: \App\Models\Setting::get('threads_content_type', 'all');

        // Dapatkan filter kota target (Bandung, Jakarta, Yogyakarta, Bali, dll.)
        $targetCityIds = \App\Services\ThreadsService::getTargetCityIds();

        // Jika type adalah 'articles', khusus ambil artikel blog yang belum terbit di Threads
        if ($type === 'articles') {
            $articleQuery = \App\Models\Article::query()
                ->published()
                ->whereNull('posted_to_threads_at');

            if (!empty($targetCityIds)) {
                $articleQuery->where(function ($q) use ($targetCityIds) {
                    $q->whereIn('city_id', $targetCityIds)
                      ->orWhereNull('city_id'); // Izinkan artikel umum/nasional
                });
            }

            $article = $articleQuery->latest('published_at')->first();

            if (!$article) {
                $this->info('Tidak ada artikel blog baru yang perlu di-broadcast ke Threads.');
                return self::SUCCESS;
            }

            $this->info("Memublikasikan Artikel Blog ke Threads: [ID: {$article->id}] {$article->title}");
            try {
                $res = $threadsService->publishArticle($article);
                $this->info('Berhasil terbit di Threads! Thread ID: ' . ($res['id'] ?? 'N/A'));
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error('Gagal memublikasikan ke Threads: ' . $e->getMessage());
                return self::FAILURE;
            }
        }

        // Jika type 'all', prioritaskan artikel blog jika ada artikel baru yang belum pernah diposting
        if ($type === 'all') {
            $pendingArticleQuery = \App\Models\Article::query()
                ->published()
                ->whereNull('posted_to_threads_at');

            if (!empty($targetCityIds)) {
                $pendingArticleQuery->where(function ($q) use ($targetCityIds) {
                    $q->whereIn('city_id', $targetCityIds)
                      ->orWhereNull('city_id');
                });
            }

            $pendingArticle = $pendingArticleQuery->latest('published_at')->first();

            // Jika ada artikel blog yang belum pernah terbit ke Threads, bagikan artikel blog
            if ($pendingArticle) {
                $this->info("Memublikasikan Artikel Blog ke Threads: [ID: {$pendingArticle->id}] {$pendingArticle->title}");
                try {
                    $res = $threadsService->publishArticle($pendingArticle);
                    $this->info('Berhasil terbit di Threads! Thread ID: ' . ($res['id'] ?? 'N/A'));
                    return self::SUCCESS;
                } catch (\Throwable $e) {
                    $this->error('Gagal memublikasikan artikel ke Threads: ' . $e->getMessage());
                    return self::FAILURE;
                }
            }
        }

        // Query default untuk Posts (Loker / Rekomendasi Tempat)
        $query = \App\Models\Post::query()
            ->where('status', 'published')
            ->whereNull('posted_to_threads_at');

        if ($type === 'jobs') {
            $query->whereHas('category', fn ($q) => $q->where('slug', 'lowongan-kerja'))
                  ->where(function ($q) {
                      $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
                  });
        } elseif ($type === 'places') {
            $query->whereHas('category', fn ($q) => $q->where('slug', '!=', 'lowongan-kerja'));
        }

        // Terapkan filter kota prioritas trafik tinggi jika ada
        if (!empty($targetCityIds)) {
            $query->whereIn('city_id', $targetCityIds);
        }

        // Prioritaskan konten terbitan terbaru
        $post = $query->latest('published_at')->first();

        if (!$post) {
            $this->info('Tidak ada postingan baru yang perlu di-broadcast ke Threads.');
            return self::SUCCESS;
        }

        $this->info("Memublikasikan ke Threads: [ID: {$post->id}] {$post->title}");

        try {
            $res = $threadsService->publishPost($post);
            $this->info('Berhasil terbit di Threads! Thread ID: ' . ($res['id'] ?? 'N/A'));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal memublikasikan ke Threads: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('threads:broadcast {--post-id= : ID postingan spesifik yang ingin di-publish} {--type=all : Tipe konten (all, jobs, places)}')]
#[Description('Publikasikan artikel/lowongan kerja terbaru ke akun resmi Threads')]
class ThreadsBroadcastCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(\App\Services\ThreadsService $threadsService)
    {
        $enabled = (bool) \App\Models\Setting::get('threads_auto_post_enabled', true);
        $specificId = $this->option('post-id');

        if (!$enabled && !$specificId) {
            $this->warn('Otomasi Threads sedang dinonaktifkan di pengaturan.');
            return self::SUCCESS;
        }

        if (!$threadsService->isConfigured()) {
            $this->error('Kredensial Threads (User ID / Access Token) belum diatur.');
            return self::FAILURE;
        }

        $type = $this->option('type') ?: \App\Models\Setting::get('threads_content_type', 'all');

        $query = \App\Models\Post::query()
            ->where('status', 'published')
            ->whereNull('posted_to_threads_at');

        if ($specificId) {
            $post = $query->find($specificId);
        } else {
            if ($type === 'jobs') {
                $query->whereHas('category', fn ($q) => $q->where('slug', 'lowongan-kerja'))
                      ->where(function ($q) {
                          $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
                      });
            } elseif ($type === 'places') {
                $query->whereHas('category', fn ($q) => $q->where('slug', '!=', 'lowongan-kerja'));
            }

            // Prioritaskan konten terbitan terbaru
            $post = $query->latest('published_at')->first();
        }

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

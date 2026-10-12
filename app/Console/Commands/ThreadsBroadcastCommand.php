<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Setting;
use App\Services\ThreadsService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('threads:broadcast {--post-id= : ID lowongan spesifik yang ingin di-publish} {--type=jobs : Tipe konten (hanya jobs, dipertahankan agar jadwal lama tetap valid)} {--slot= : Kunci jadwal slot spesifik (slot_1, slot_2, dst)}')]
#[Description('Publikasikan lowongan kerja terbaru ke akun resmi Threads')]
class ThreadsBroadcastCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ThreadsService $threadsService)
    {
        $enabled = (bool) Setting::get('threads_auto_post_enabled', true);
        $specificPostId = $this->option('post-id');
        $slot = $this->option('slot');

        if (!$enabled && !$specificPostId) {
            $this->warn('Otomasi Threads sedang dinonaktifkan di pengaturan utama.');
            return self::SUCCESS;
        }

        // Cek apakah slot jadwal ini dinonaktifkan secara khusus
        if ($slot && !Setting::get("threads_schedule_{$slot}_enabled", true)) {
            $this->warn("Jadwal Threads untuk slot {$slot} sedang dinonaktifkan.");
            return self::SUCCESS;
        }

        if (!$threadsService->isConfigured()) {
            $this->error('Kredensial Threads (User ID / Access Token) belum diatur.');
            return self::FAILURE;
        }

        // 1. Jika ada specific post ID yang diminta
        if ($specificPostId) {
            $post = Post::query()->where('status', 'published')->find($specificPostId);
            if (!$post) {
                $this->error("Postingan ID {$specificPostId} tidak ditemukan atau belum published.");
                return self::FAILURE;
            }

            $this->info("Memublikasikan Loker ke Threads: [ID: {$post->id}] {$post->title}");
            return $this->publish($threadsService, $post);
        }

        // 2. Mode antrean otomatis: loker aktif yang belum terbit di Threads
        // Dapatkan filter kota target (Bandung, Jakarta, Yogyakarta, Bali, dll.)
        $targetCityIds = ThreadsService::getTargetCityIds();

        $baseQuery = fn () => Post::query()
            ->where('status', 'published')
            ->whereNull('posted_to_threads_at')
            ->jobs()
            ->activeJob();

        // Prioritaskan konten terbitan terbaru di kota prioritas trafik tinggi
        $post = !empty($targetCityIds)
            ? $baseQuery()->whereIn('city_id', $targetCityIds)->latest('published_at')->first()
            : null;

        // Fallback: Jika tidak ada postingan baru di kota prioritas, ambil dari kota manapun
        $post ??= $baseQuery()->latest('published_at')->first();

        if (!$post) {
            $this->info('Tidak ada postingan baru yang perlu di-broadcast ke Threads.');
            return self::SUCCESS;
        }

        $this->info("Memublikasikan ke Threads: [ID: {$post->id}] {$post->title}");

        return $this->publish($threadsService, $post);
    }

    private function publish(ThreadsService $threadsService, Post $post): int
    {
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

<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;

class ThreadsService
{
    protected string $baseUrl = 'https://graph.threads.net/v1.0';

    /**
     * Get Threads User ID from setting or config.
     */
    public function getUserId(): ?string
    {
        return Setting::get('threads_user_id', config('services.threads.user_id'));
    }

    /**
     * Get Threads Access Token from setting or config.
     */
    public function getAccessToken(): ?string
    {
        return Setting::get('threads_access_token', config('services.threads.access_token'));
    }

    /**
     * Check if Threads posting is enabled and credentials exist.
     */
    public function isConfigured(): bool
    {
        return !empty($this->getUserId()) && !empty($this->getAccessToken());
    }

    /**
     * Verify token by fetching user profile.
     */
    public function verifyProfile(): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            throw new Exception('Threads Access Token belum diatur.');
        }

        $response = Http::get("{$this->baseUrl}/me", [
            'fields' => 'id,username,threads_profile_picture_url',
            'access_token' => $token,
        ]);

        if (!$response->successful()) {
            throw new Exception('Gagal verifikasi token: ' . ($response->json('error.message') ?? $response->body()));
        }

        return $response->json();
    }

    /**
     * Publish custom text directly to Threads.
     */
    public function postText(string $text, ?string $linkAttachment = null): array
    {
        $userId = $this->getUserId();
        $token = $this->getAccessToken();

        if (!$userId || !$token) {
            throw new Exception('Threads credentials belum lengkap.');
        }

        // Step 1: Create media container
        $containerPayload = [
            'media_type' => 'TEXT',
            'text' => $text,
            'access_token' => $token,
        ];

        $createRes = Http::post("{$this->baseUrl}/{$userId}/threads", $containerPayload);

        if (!$createRes->successful()) {
            throw new Exception('Gagal membuat container Threads: ' . ($createRes->json('error.message') ?? $createRes->body()));
        }

        $containerId = $createRes->json('id');

        // Step 2: Publish container (sleep 3 seconds to ensure Meta processing)
        sleep(3);

        $publishRes = Http::post("{$this->baseUrl}/{$userId}/threads_publish", [
            'creation_id' => $containerId,
            'access_token' => $token,
        ]);

        if (!$publishRes->successful()) {
            throw new Exception('Gagal publish ke Threads: ' . ($publishRes->json('error.message') ?? $publishRes->body()));
        }

        return $publishRes->json();
    }

    /**
     * Build engaging post copy for a Post model and publish it.
     */
    public function publishPost(Post $post): array
    {
        $url = $post->url();
        $cityName = $post->city?->name ?? 'Indonesia';
        $categoryName = $post->category?->name ?? 'Info Sekitar';
        $cleanTitle = trim($post->title);

        if ($post->isJob()) {
            $company = $post->company_name ?: ($post->job_company ?: '');
            $text = "📢 Lowongan Kerja Terbaru di {$cityName}!\n\n";
            $text .= "💼 Posisi: {$cleanTitle}\n";
            if ($company) {
                $text .= "🏢 Perusahaan: {$company}\n";
            }
            $text .= "\n📌 Info detail kualifikasi & cara melamar langsung:\n";
            $text .= "{$url}\n\n";
            $cityTag = str_replace([' ', '-', '.'], '', $cityName);
            $text .= "#Loker{$cityTag} #LowonganKerja #LokerTerbaru #Disekitar";
        } else {
            $text = "✨ Rekomendasi di {$cityName}: {$cleanTitle}\n\n";
            if ($post->excerpt) {
                $text .= $post->excerpt . "\n\n";
            }
            $text .= "📍 Selengkapnya baca ulasan & lokasi di sini:\n";
            $text .= "{$url}\n\n";
            $cityTag = str_replace([' ', '-', '.'], '', $cityName);
            $catTag = str_replace([' ', '-', '.'], '', $categoryName);
            $text .= "#Kuliner{$cityTag} #Wisata{$cityTag} #{$catTag} #Disekitar";
        }

        // Meta Threads text limit is 500 characters
        if (mb_strlen($text) > 495) {
            $text = mb_substr($text, 0, 490) . "...\n" . $url;
        }

        $result = $this->postText($text, $url);

        // Mark post as shared to Threads
        $post->updateQuietly(['posted_to_threads_at' => now()]);

        return $result;
    }
}

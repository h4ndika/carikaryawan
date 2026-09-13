<?php

namespace App\Services;

use App\Models\Article;
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
     * Refresh a long-lived access token before it expires (resets 60 days validity).
     */
    public function refreshLongLivedToken(): ?string
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        $response = Http::get('https://graph.threads.net/refresh_access_token', [
            'grant_type' => 'th_refresh_token',
            'access_token' => $token,
        ]);

        if ($response->successful() && !empty($response->json('access_token'))) {
            $newToken = $response->json('access_token');
            Setting::set('threads_access_token', $newToken);
            return $newToken;
        }

        return null;
    }

    /**
     * Publish custom text directly to Threads.
     */
    public function postText(string $text, ?string $linkAttachment = null, ?string $topicTag = null): array
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

        if (!empty($topicTag)) {
            $containerPayload['topic_tag'] = $topicTag;
        }

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
     * Publish image with caption directly to Threads.
     */
    public function postImage(string $imageUrl, string $text, ?string $topicTag = null): array
    {
        $userId = $this->getUserId();
        $token = $this->getAccessToken();

        if (!$userId || !$token) {
            throw new Exception('Threads credentials belum lengkap.');
        }

        // Step 1: Create IMAGE container
        $containerPayload = [
            'media_type' => 'IMAGE',
            'image_url' => $imageUrl,
            'text' => $text,
            'access_token' => $token,
        ];

        if (!empty($topicTag)) {
            $containerPayload['topic_tag'] = $topicTag;
        }

        $createRes = Http::post("{$this->baseUrl}/{$userId}/threads", $containerPayload);

        if (!$createRes->successful()) {
            throw new Exception('Gagal membuat container gambar Threads: ' . ($createRes->json('error.message') ?? $createRes->body()));
        }

        $containerId = $createRes->json('id');

        // Step 2: Publish container (sleep 5 seconds to ensure Meta image download and processing)
        sleep(5);

        $publishRes = Http::post("{$this->baseUrl}/{$userId}/threads_publish", [
            'creation_id' => $containerId,
            'access_token' => $token,
        ]);

        if (!$publishRes->successful()) {
            throw new Exception('Gagal publish gambar ke Threads: ' . ($publishRes->json('error.message') ?? $publishRes->body()));
        }

        return $publishRes->json();
    }

    /**
     * Build engaging post copy for a Post model and publish it.
     */
    public function publishPost(Post $post): array
    {
        $url = $post->url();
        $rawCityName = $post->city?->name ?? 'Indonesia';
        // Bersihkan prefix 'KOTA ' / 'KAB. ' / 'KABUPATEN '
        $cleanCityOnly = trim(preg_replace('/^(KOTA|KAB\.?|KABUPATEN)\s+/i', '', $rawCityName));
        // Format lowercase lalu ucwords untuk tampilan teks (contoh: "Kota Bandung")
        $titleCaseCity = ucwords(strtolower($cleanCityOnly));
        $topicTagCity = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $cleanCityOnly));

        $cleanTitle = trim($post->title);

        if ($post->isJob()) {
            $company = $post->company_name ?: ($post->job_company ?: '');
            $topicTag = $topicTagCity ? "loker{$topicTagCity}" : 'lowongankerja';

            $header = "📢 Lowongan Kerja Terbaru disekitar {$titleCaseCity}!\n\n";
            $position = "💼 Posisi: {$cleanTitle}\n";
            if ($company) {
                $position .= "🏢 Perusahaan: {$company}\n";
            }

            $info = "\n📌 Info detail kualifikasi & cara melamar langsung:\n{$url}\n\n";
            $hashtags = "#Loker{$topicTagCity} #LowonganKerja #LokerTerbaru #Disekitar";

            $fixedLen = mb_strlen($header) + mb_strlen($info) + mb_strlen($hashtags);
            $maxPositionLen = 495 - $fixedLen;

            if (mb_strlen($position) > $maxPositionLen) {
                $position = mb_substr($position, 0, max(0, $maxPositionLen - 3)) . "...\n";
            }

            $text = $header . $position . $info . $hashtags;
        } else {
            $categoryName = $post->category?->name ?? 'Tempat';
            $catTag = preg_replace('/[^a-zA-Z0-9]/', '', ucwords(strtolower($categoryName)));
            $cityTag = preg_replace('/[^a-zA-Z0-9]/', '', $titleCaseCity);

            $topicTag = $topicTagCity ? "rekomendasi{$topicTagCity}" : null;

            $header = "✨ Rekomendasi Tempat disekitar {$titleCaseCity}!\n\n";
            $headline = "📍 {$cleanTitle}\n\n";

            $excerpt = $post->excerpt ? "{$post->excerpt}\n\n" : '';
            $info = "Selengkapnya baca ulasan & info lokasi di sini:\n{$url}\n\n";
            $hashtags = "#Kuliner{$cityTag} #Wisata{$cityTag} #{$catTag} #Disekitar";

            $fixedLen = mb_strlen($header) + mb_strlen($headline) + mb_strlen($info) + mb_strlen($hashtags);
            $maxExcerptLen = 495 - $fixedLen;

            if ($excerpt && mb_strlen($excerpt) > $maxExcerptLen) {
                $excerpt = mb_substr($excerpt, 0, max(0, $maxExcerptLen - 3)) . "...\n\n";
            }

            $text = $header . $headline . $excerpt . $info . $hashtags;
        }

        // Hard safety limit: Meta Threads parameter text must be <= 500 characters
        if (mb_strlen($text) > 500) {
            $text = mb_substr($text, 0, 497) . '...';
        }

        // Get image URL (PNG/JPG only, SVG is not supported by Threads media_type IMAGE)
        $imageUrl = null;
        if (!empty($post->thumbnail) && !str_ends_with(strtolower($post->thumbnail), '.svg')) {
            $imageUrl = $post->thumbnail;
        } elseif ($post->relationLoaded('images') && $post->images->first()) {
            $first = $post->images->first()->image_url;
            if ($first && !str_ends_with(strtolower($first), '.svg')) {
                $imageUrl = $first;
            }
        } elseif ($post->images()->exists()) {
            $first = $post->images()->first()?->image_url;
            if ($first && !str_ends_with(strtolower($first), '.svg')) {
                $imageUrl = $first;
            }
        }

        // Make absolute URL if relative
        if ($imageUrl && !str_starts_with($imageUrl, 'http://') && !str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        if ($imageUrl) {
            $result = $this->postImage($imageUrl, $text, $topicTag);
        } else {
            $result = $this->postText($text, $url, $topicTag);
        }

        // Mark post as shared to Threads
        $post->updateQuietly(['posted_to_threads_at' => now()]);

        return $result;
    }

    /**
     * Build engaging post copy for an Article model (blog listicle) and publish it.
     */
    public function publishArticle(Article $article): array
    {
        $url = $article->url();
        $cityName = $article->city?->name ?? 'Indonesia';
        $cleanTitle = trim($article->title);

        $intros = [
            "Buat yang lagi ngerencanain main atau hunting spot di {$cityName}, kita baru beresin panduannya nih.",
            "Lagi butuh ide buat jalan-jalan di {$cityName}? Coba intip kurasi ini:",
            "Kumpulan tempat rekomendasi di {$cityName} yang patut kamu coba:",
        ];
        $intro = $intros[array_rand($intros)];

        $suffix = "\n\nDaftar tempat lengkapnya bisa dibaca di sini ya:\n{$url}";
        $maxBodyLen = 500 - mb_strlen($suffix);

        $body = "{$intro}\n\n{$cleanTitle}";
        if ($article->excerpt) {
            $body .= "\n\n{$article->excerpt}";
        }

        if (mb_strlen($body) > $maxBodyLen) {
            $body = mb_substr($body, 0, $maxBodyLen - 3) . '...';
        }

        $text = $body . $suffix;

        // Hard safety limit: Meta Threads parameter text must be <= 500 characters
        if (mb_strlen($text) > 500) {
            $text = mb_substr($text, 0, 497) . '...';
        }

        // Get image URL (PNG/JPG only)
        $imageUrl = null;
        if (!empty($article->featured_image_url) && !str_ends_with(strtolower($article->featured_image_url), '.svg')) {
            $imageUrl = $article->featured_image_url;
        }

        // Make absolute URL if relative
        if ($imageUrl && !str_starts_with($imageUrl, 'http://') && !str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        if ($imageUrl) {
            $result = $this->postImage($imageUrl, $text);
        } else {
            $result = $this->postText($text, $url);
        }

        // Mark article as shared to Threads
        $article->updateQuietly(['posted_to_threads_at' => now()]);

        return $result;
    }
}


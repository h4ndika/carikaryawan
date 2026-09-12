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
     * Publish image with caption directly to Threads.
     */
    public function postImage(string $imageUrl, string $text): array
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
        $cityName = $post->city?->name ?? 'Indonesia';
        $cleanTitle = trim($post->title);

        if ($post->isJob()) {
            $company = $post->company_name ?: ($post->job_company ?: '');
            $intros = [
                "Ada info loker baru nih buat daerah {$cityName}.",
                "Lagi cari loker di {$cityName}? Ini barusan masuk infonya.",
                "Info loker {$cityName} yang baru update hari ini:",
            ];
            $intro = $intros[array_rand($intros)];

            $text = "{$intro}\n\n";
            $text .= "Posisi: {$cleanTitle}\n";
            if ($company) {
                $text .= "Perusahaan: {$company}\n";
            }
            $text .= "\nKualifikasi lengkap sama link daftarnya bisa langsung cek di sini ya:\n";
            $text .= "{$url}";
        } else {
            $intros = [
                "Lagi nyari tempat nongkrong atau referensi di {$cityName}?",
                "Buat yang lagi nyari spot asik di {$cityName}, coba cek ini deh:",
                "Rekomendasi tempat di {$cityName} yang bisa kamu masukin wishlist:",
            ];
            $intro = $intros[array_rand($intros)];

            $text = "{$intro}\n\n";
            $text .= "{$cleanTitle}\n\n";
            if ($post->excerpt) {
                $text .= $post->excerpt . "\n\n";
            }
            $text .= "Info lokasi dan ulasan lengkapnya:\n";
            $text .= "{$url}";
        }

        // Meta Threads text limit is 500 characters
        if (mb_strlen($text) > 495) {
            $text = mb_substr($text, 0, 490) . "...\n" . $url;
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
            $result = $this->postImage($imageUrl, $text);
        } else {
            $result = $this->postText($text, $url);
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

        $text = "{$intro}\n\n";
        $text .= "{$cleanTitle}\n\n";

        if ($article->excerpt) {
            $text .= $article->excerpt . "\n\n";
        }

        $text .= "Daftar tempat lengkapnya bisa dibaca di sini ya:\n";
        $text .= "{$url}";

        // Meta Threads text limit is 500 characters
        if (mb_strlen($text) > 495) {
            $text = mb_substr($text, 0, 490) . "...\n" . $url;
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


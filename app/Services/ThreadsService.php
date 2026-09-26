<?php

namespace App\Services;

use App\Ai\DisekitarAgent;
use App\Models\Article;
use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
     * Get default target city IDs (Bandung, Jakarta, Yogyakarta, Bali)
     */
    public static function getDefaultCityIds(): array
    {
        return \App\Models\City::query()
            ->where(function ($q) {
                $q->where('name', 'like', '%Bandung%')
                  ->orWhere('name', 'like', '%Jakarta%')
                  ->orWhere('name', 'like', '%Yogyakarta%')
                  ->orWhere('name', 'like', '%Denpasar%')
                  ->orWhere('name', 'like', '%Badung%')
                  ->orWhere('name', 'like', '%Bali%')
                  ->orWhere('name', 'like', '%Gianyar%')
                  ->orWhere('name', 'like', '%Tabanan%');
            })
            ->orWhereHas('province', function ($q) {
                $q->where('name', 'like', '%DKI Jakarta%')
                  ->orWhere('name', 'like', '%Jakarta%')
                  ->orWhere('name', 'like', '%Bali%')
                  ->orWhere('name', 'like', '%Yogyakarta%')
                  ->orWhere('name', 'like', '%DI Yogyakarta%');
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get configured target city IDs from settings, or default cities if none configured.
     */
    public static function getTargetCityIds(): array
    {
        $raw = Setting::get('threads_target_cities');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded)) {
                return array_map('intval', $decoded);
            }
        }

        return self::getDefaultCityIds();
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

            // Format bervariasi santai (tanpa kalimat pembuka berulang yang kaku)
            $buildNaturalFormat = function () use ($cleanTitle, $company, $titleCaseCity, $topicTagCity) {
                $hooks = [
                    "Capek juga ya kalau tiap hari desak-desakan KRL atau macet ke kota seberang. Mending merapat nih warga {$titleCaseCity} 👀",
                    "Daripada habis waktu di jalan tiap hari, mending cari kerja yang deket rumah disekitar {$titleCaseCity}:",
                    "Ada info loker baru nih buat area {$titleCaseCity}, siapa tahu jodoh karirmu di sini:",
                    "Buat yang lagi cari kerja disekitar {$titleCaseCity} dan pengen kerja lebih santai dekat rumah, boleh dicek nih:",
                    "Warga {$titleCaseCity} merapat dulu! Ada update lowongan kerja menarik yang baru rilis:",
                ];
                $hook = $hooks[array_rand($hooks)];

                $body = "{$hook}\n\n💼 Posisi: {$cleanTitle}";
                if ($company) {
                    $body .= "\n🏢 Perusahaan: {$company}";
                }
                $body .= "\n📍 Lokasi: {$titleCaseCity}";
                $body .= "\n\nInfo detail & cara daftarnya sudah kita spill di komentar pertama ya!";
                $body .= "\n\nAda yang lagi berjuang cari kerja atau pernah kerja di bidang ini juga? Spill dong pengalamannya 👇";
                $body .= "\n\n#Loker{$topicTagCity} #Disekitar";

                return $body;
            };

            // Coba buat narasi santai percakapan AI (tanpa link)
            $text = $this->generateAiJobCopy($cleanTitle, $company, $titleCaseCity, $topicTagCity);

            if (empty($text)) {
                $text = $buildNaturalFormat();
            }
        } else {
            $categoryName = $post->category?->name ?? 'Tempat';
            $catTag = preg_replace('/[^a-zA-Z0-9]/', '', ucwords(strtolower($categoryName)));
            $cityTag = preg_replace('/[^a-zA-Z0-9]/', '', $titleCaseCity);

            $topicTag = $topicTagCity ?: 'kuliner';

            // Coba generate copy percakapan berbasis AI (tanpa link)
            $aiText = $this->generateAiPlaceCopy($cleanTitle, $categoryName, $titleCaseCity, $post->excerpt, $cityTag, $catTag);

            if ($aiText) {
                $text = $aiText;
            } else {
                // Fallback template
                $header = "✨ Rekomendasi tempat disekitar {$titleCaseCity}!\n\n";
                $headline = "📍 {$cleanTitle}\n\n";

                $excerpt = $post->excerpt ? "{$post->excerpt}\n\n" : '';
                $info = "Info ulasan & lokasi lengkap kita taruh di komentar pertama ya 👇\n\n";
                $hashtags = "#Kuliner{$cityTag} #Wisata{$cityTag} #{$catTag} #Disekitar";

                $text = $header . $headline . $excerpt . $info . $hashtags;
            }
        }

        // Hard safety limit: Meta Threads parameter text must be <= 500 characters
        if (mb_strlen($text) > 500) {
            $text = mb_substr($text, 0, 497) . '...';
        }

        // Get image URL (PNG/JPG only, SVG is not supported by Threads media_type IMAGE)
        $imageUrl = null;
        if ($post->isJob()) {
            if (!empty($post->thumbnail) && !str_ends_with(strtolower($post->thumbnail), '.svg')) {
                $imageUrl = $post->thumbnail;
            } else {
                $imageUrl = route('job.image.png', ['slug' => $post->slug]);
            }
        } elseif (!empty($post->thumbnail) && !str_ends_with(strtolower($post->thumbnail), '.svg')) {
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
            $result = $this->postText($text, null, $topicTag);
        }

        // Mark post as shared to Threads
        $post->updateQuietly(['posted_to_threads_at' => now()]);

        // Auto-reply: Letakkan link di komentar pertama (menghindari penalti jangkauan/shadowban)
        $publishedThreadId = $result['id'] ?? null;
        if ($publishedThreadId) {
            try {
                sleep(3); // Beri jeda 3 detik agar thread selesai terindeks oleh Meta
                $replyComment = $post->isJob()
                    ? "📌 Info kualifikasi lengkap, berkas, dan link lamaran langsung ada di sini ya:\n{$url}"
                    : "📍 Info selengkapnya & panduan lokasi bisa dibaca di sini ya:\n{$url}";

                $this->replyToThread($publishedThreadId, $replyComment);
            } catch (\Throwable $e) {
                Log::warning("Gagal auto-reply komentar pertama Threads post #{$post->id}: " . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Build engaging post copy for an Article model (blog listicle) and publish it.
     */
    public function publishArticle(Article $article): array
    {
        $url = $article->url();
        $rawCityName = $article->city?->name ?? 'Indonesia';
        // Bersihkan prefix 'KOTA ' / 'KAB. ' / 'KABUPATEN '
        $cleanCityOnly = trim(preg_replace('/^(KOTA|KAB\.?|KABUPATEN)\s+/i', '', $rawCityName));
        $titleCaseCity = ucwords(strtolower($cleanCityOnly));
        $topicTagCity = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $cleanCityOnly));
        $topicTag = $topicTagCity ?: 'rekomendasi';

        $cleanTitle = trim($article->title);

        $aiText = $this->generateAiArticleCopy($cleanTitle, $titleCaseCity, $article->excerpt, $topicTagCity);

        if ($aiText) {
            $text = $aiText;
        } else {
            $intros = [
                "Buat yang lagi ngerencanain main atau hunting spot disekitar {$titleCaseCity}, kita baru beresin panduannya nih.",
                "Lagi butuh ide buat jalan-jalan disekitar {$titleCaseCity}? Coba intip kurasi ini:",
                "Kumpulan tempat rekomendasi disekitar {$titleCaseCity} yang patut kamu coba:",
            ];
            $intro = $intros[array_rand($intros)];

            $suffix = "\n\nDaftar tempat lengkapnya sudah kita share di komentar pertama ya 👇";
            $maxBodyLen = 500 - mb_strlen($suffix);

            $body = "{$intro}\n\n{$cleanTitle}";
            if ($article->excerpt) {
                $body .= "\n\n{$article->excerpt}";
            }

            if (mb_strlen($body) > $maxBodyLen) {
                $body = mb_substr($body, 0, $maxBodyLen - 3) . '...';
            }

            $text = $body . $suffix;
        }

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
            $result = $this->postImage($imageUrl, $text, $topicTag);
        } else {
            $result = $this->postText($text, null, $topicTag);
        }

        // Mark article as shared to Threads
        $article->updateQuietly(['posted_to_threads_at' => now()]);

        // Auto-reply komentar pertama berisi link artikel
        $publishedThreadId = $result['id'] ?? null;
        if ($publishedThreadId) {
            try {
                sleep(3);
                $this->replyToThread(
                    $publishedThreadId,
                    "📖 Baca artikel & daftar tempat selengkapnya di sini ya:\n{$url}"
                );
            } catch (\Throwable $e) {
                Log::warning("Gagal auto-reply komentar pertama Threads article #{$article->id}: " . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Generate conversation-driven job post copy using AI.
     */
    protected function generateAiJobCopy(string $title, string $company, string $city, string $topicTagCity): ?string
    {
        try {
            $systemInstruction = "Kamu adalah social media writer untuk Threads @disekitarcom. Tulislah postingan Threads yang santai, sangat relatable untuk warga lokal atau komuter (seperti sesama pencari kerja/pejuang KRL/warga setempat yang curhat santai, BUKAN robot korporat atau akun broadcast kaku). Balas HANYA dengan teks postingan murni (plain text), tanpa code block, tanpa link URL, tanpa tanda petik pembuka/penutup.";

            $prompt = "Buat teks postingan Threads untuk lowongan kerja berikut:
- Posisi: {$title}
- Perusahaan: " . ($company ?: 'Brand Lokal') . "
- Lokasi: disekitar {$city}

FORMULA HOOK VIRAL (WAJIB DIIKUTI):
1. Mulai dengan hook emosional/relatable yang nendang terkait rutinitas warga/komuter {$city} (contoh topik: capek desak-desakan KRL/busway, macet di jalan tiap hari, pengen kerja deket rumah, struggle cari kerja di area {$city}, atau ajakan warga setempat merapat). Variasikan gayanya agar selalu segar!
2. Sebutkan posisi & perusahaan dengan luwes dalam satu alur cerita.
3. Sebutkan dengan santai: 'Info detail & cara daftarnya udah aku spill di komentar pertama ya!' (JANGAN taruh link URL di sini).
4. Akhiri dengan 1 PERTANYAAN PEMANTIK REPLY/DISKUSI yang ramah dan memancing orang bercerita di kolom komentar (contoh: tanya struggle posisi tsb, tanya siapa yang anak jurusan tsb, dsb).
5. Sertakan hashtag: #Loker{$topicTagCity} #Disekitar
6. Panjang TOTAL teks maksimal 380 karakter agar nyaman dibaca dan tidak kepotong.";

            $agent = new DisekitarAgent(instructions: $systemInstruction);
            $response = (string) $agent->prompt(
                prompt: $prompt,
                provider: 'openai-compatible',
                model: 'Content-blog',
                timeout: 30
            );
            $clean = trim($response);

            // Bersihkan markdown code block jika model membungkusnya
            $clean = preg_replace('/^```[a-zA-Z]*\s*/i', '', $clean);
            $clean = preg_replace('/\s*```$/', '', $clean);
            $clean = trim($clean);

            if (!empty($clean) && mb_strlen($clean) <= 495) {
                return $clean;
            }
        } catch (\Throwable $e) {
            Log::warning('Threads AI Job Copy failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Generate conversation-driven place discovery post copy using AI.
     */
    protected function generateAiPlaceCopy(string $title, string $category, string $city, ?string $excerpt, string $cityTag, string $catTag): ?string
    {
        try {
            $systemInstruction = "Kamu adalah content writer Threads @disekitarcom yang gemar eksplorasi tempat lokal Indonesia. Tulislah postingan Threads yang santai, visual, dan memancing reply warga lokal. Balas HANYA dengan teks postingan murni (plain text), tanpa link URL, tanpa markdown code block, tanpa pembuka/penutup.";

            $prompt = "Buat teks postingan Threads rekomendasi tempat:
- Tempat: {$title}
- Kategori: {$category}
- Kota: disekitar {$city}
- Catatan: " . ($excerpt ?: 'Spot asik dan nyaman') . "

ATURAN WAJIB:
1. Tulis pengalaman/observasi santai tentang suasana tempat atau kebiasaan nongkrong di {$city}.
2. Sebutkan nama tempat: {$title}.
3. JANGAN sertakan link URL. Arahkan pembaca bahwa ulasan lengkap ada di komentar pertama.
4. Akhiri dengan 1 PERTANYAAN PEMICU REPLY yang ramah.
5. Sertakan hashtag: #Kuliner{$cityTag} #Wisata{$cityTag} #{$catTag} #Disekitar
6. Panjang TOTAL teks harus di bawah 400 karakter.";

            $agent = new DisekitarAgent(instructions: $systemInstruction);
            $response = (string) $agent->prompt(
                prompt: $prompt,
                provider: 'openai-compatible',
                model: 'Content-blog',
                timeout: 30
            );
            $clean = trim($response);

            $clean = preg_replace('/^```[a-zA-Z]*\s*/i', '', $clean);
            $clean = preg_replace('/\s*```$/', '', $clean);
            $clean = trim($clean);

            if (!empty($clean) && mb_strlen($clean) <= 495) {
                return $clean;
            }
        } catch (\Throwable $e) {
            Log::warning('Threads AI Place Copy failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Generate conversation-driven article/curation post copy using AI.
     */
    protected function generateAiArticleCopy(string $title, string $city, ?string $excerpt, string $topicTagCity): ?string
    {
        try {
            $systemInstruction = "Kamu adalah editor kurasi perjalanan dan kuliner Threads @disekitarcom. Tulislah postingan Threads yang conversation-driven dan pancing opini warga lokal. Balas HANYA dengan teks murni tanpa link URL dan tanpa code block.";

            $prompt = "Buat teks Threads untuk panduan kurasi:
- Judul: {$title}
- Kota: disekitar {$city}

ATURAN WAJIB:
1. Awali dengan rasa penasaran atau opini jujur seputar kuliner/tempat main di {$city}.
2. JANGAN sertakan link URL di teks utama. Sebutkan bahwa link daftar lengkap ada di komentar pertama.
3. Beri 1 PERTANYAAN PEMICU REPLY yang seru.
4. Akhiri dengan hashtag #{$topicTagCity} #Disekitar
5. Panjang TOTAL di bawah 400 karakter.";

            $agent = new DisekitarAgent(instructions: $systemInstruction);
            $response = (string) $agent->prompt(
                prompt: $prompt,
                provider: 'openai-compatible',
                model: 'Content-blog',
                timeout: 30
            );
            $clean = trim($response);

            $clean = preg_replace('/^```[a-zA-Z]*\s*/i', '', $clean);
            $clean = preg_replace('/\s*```$/', '', $clean);
            $clean = trim($clean);

            if (!empty($clean) && mb_strlen($clean) <= 495) {
                return $clean;
            }
        } catch (\Throwable $e) {
            Log::warning('Threads AI Article Copy failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get recent published threads for the account.
     */
    public function getUserThreads(int $limit = 20): array
    {
        $userId = $this->getUserId();
        $token = $this->getAccessToken();

        if (!$userId || !$token) {
            return [];
        }

        $res = Http::withToken($token)->get("{$this->baseUrl}/{$userId}/threads", [
            'fields' => 'id,text,permalink,timestamp',
            'limit' => $limit,
        ]);

        return $res->json('data') ?? [];
    }

    /**
     * Get conversation / replies for a specific thread.
     */
    public function getThreadConversation(string $mediaId): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return [];
        }

        $res = Http::withToken($token)->get("{$this->baseUrl}/{$mediaId}/conversation", [
            'fields' => 'id,text,username,timestamp',
        ]);

        return $res->json('data') ?? [];
    }

    /**
     * Reply to a specific thread or comment.
     */
    public function replyToThread(string $replyToId, string $text): array
    {
        $userId = $this->getUserId();
        $token = $this->getAccessToken();

        if (!$userId || !$token) {
            throw new Exception('Threads credentials belum lengkap.');
        }

        // Step 1: Create reply media container
        $containerPayload = [
            'media_type' => 'TEXT',
            'text' => $text,
            'reply_to_id' => $replyToId,
            'access_token' => $token,
        ];

        $createRes = Http::post("{$this->baseUrl}/{$userId}/threads", $containerPayload);

        if (!$createRes->successful()) {
            throw new Exception('Gagal membuat container reply Threads: ' . ($createRes->json('error.message') ?? $createRes->body()));
        }

        $containerId = $createRes->json('id');

        // Step 2: Publish container
        sleep(2);

        $publishRes = Http::post("{$this->baseUrl}/{$userId}/threads_publish", [
            'creation_id' => $containerId,
            'access_token' => $token,
        ]);

        if (!$publishRes->successful()) {
            throw new Exception('Gagal publish reply Threads: ' . ($publishRes->json('error.message') ?? $publishRes->body()));
        }

        return $publishRes->json();
    }

    /**
     * Generate context-aware AI reply for a comment.
     */
    public function generateAiReply(string $parentPostText, string $commentAuthor, string $commentText): ?string
    {
        try {
            $systemInstruction = "Kamu adalah community engagement manager ramah untuk Threads @disekitarcom (portal info lokal, tempat nongkrong, & loker Indonesia). Tugasmu membalas komentar audiens secara ramah, santai (conversational), suportif, dan natural (bukan seperti robot cs). Balas HANYA dengan teks balasan murni, tanpa code block, tanpa penjelasan pembuka/penutup.";

            $prompt = "Konteks Postingan Utama Kita:
\"{$parentPostText}\"

Komentar dari pengguna (@{$commentAuthor}):
\"{$commentText}\"

Instruksi:
1. Sapa atau respon langsung isi komentar mereka secara relevan dan antusias.
2. Jika mereka bertanya seputar tempat atau loker, jawab dengan ramah dan sarankan cek detail di link pada postingan utama bila perlu.
3. Buat teks balasan pendek & padat (maksimal 250 karakter).";

            $agent = new DisekitarAgent(instructions: $systemInstruction);
            $response = (string) $agent->prompt(
                prompt: $prompt,
                provider: 'openai-compatible',
                model: 'Content-blog',
                timeout: 30
            );

            $clean = trim($response);
            $clean = preg_replace('/^```[a-zA-Z]*\s*/i', '', $clean);
            $clean = preg_replace('/\s*```$/', '', $clean);
            $clean = trim($clean);

            if (!empty($clean) && mb_strlen($clean) <= 450) {
                return $clean;
            }
        } catch (\Throwable $e) {
            Log::warning('Threads AI Reply Generation failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Generate 3 varied reply drafts for interacting with external threads.
     *
     * @return array<int, array{style: string, text: string}>
     */
    public function generateEngagementDrafts(string $threadContent, string $topic = 'umum'): array
    {
        try {
            // Deteksi kota dari postingan thread atau data database
            $detectedCity = null;
            $samplePost = null;
            $allCities = City::select('id', 'name', 'slug')->get();

            foreach ($allCities as $c) {
                $cleanName = trim(preg_replace('/^(KOTA|KAB\.?|KABUPATEN)\s+/i', '', $c->name));
                if (mb_strlen($cleanName) >= 3 && preg_match('/\b' . preg_quote($cleanName, '/') . '\b/i', $threadContent)) {
                    $detectedCity = $c;
                    break;
                }
            }

            // Ambil postingan relevan dari database jika kota terdeteksi atau sesuai topik
            $postQuery = Post::query()->published()->with(['city', 'category']);

            if ($topic === 'loker') {
                $postQuery->whereHas('category', fn ($q) => $q->where('slug', 'like', '%loker%')->orWhere('name', 'like', '%loker%')->orWhere('slug', 'like', '%lowongan%'));
            } elseif ($topic === 'kuliner') {
                $postQuery->whereHas('category', fn ($q) => $q->where('slug', 'like', '%kuliner%')->orWhere('name', 'like', '%kuliner%')->orWhere('slug', 'like', '%cafe%'));
            } elseif ($topic === 'wisata') {
                $postQuery->whereHas('category', fn ($q) => $q->where('slug', 'like', '%wisata%')->orWhere('name', 'like', '%wisata%'));
            }

            if ($detectedCity) {
                $samplePost = (clone $postQuery)->where('city_id', $detectedCity->id)->latest('published_at')->first();
            }

            if (!$samplePost) {
                $samplePost = (clone $postQuery)->latest('published_at')->first();
            }

            $contextHint = '';
            if ($samplePost) {
                $postUrl = $samplePost->url();
                $postTitle = $samplePost->title;
                $cityName = $samplePost->city ? trim(preg_replace('/^(KOTA|KAB\.?|KABUPATEN)\s+/i', '', $samplePost->city->name)) : '';
                $contextHint = "\n\nDATABASE POST RELEVAN DITEMUKAN:
- Judul Post: \"{$postTitle}\"
- Kota: {$cityName}
- URL Lengkap: {$postUrl}
(PENTING: Gunakan URL di atas untuk draft balasan yang solutif/rekomendasi agar netizen bisa langsung klik membaca info terkurasi di disekitar.com!)";
            } else {
                $contextHint = "\n\n(Jika tidak ada post spesifik, arahkan ke portal utama disekitar.com atau disekitar.com/loker)";
            }

            $systemInstruction = "Kamu adalah social media specialist & community builder untuk @disekitarcom (portal kurasi info loker, kuliner, wisata, dan info lokal terkurasi per kota di Indonesia: disekitar.com). Gaya komunikasimu hangat, luwes, cerdas, solutif, dan mengalir natural layaknya teman ngobrol di Threads. Tulisan tidak boleh terlalu pelit kata; berikan penjelasan atau tanggapan yang bernyawa dan berbobot (2-4 kalimat yang mengalir enak). Balas HANYA dalam format JSON valid berupa list array objek.";

            $prompt = "Berikut adalah postingan akun Thread seseorang (topik: {$topic}):
\"{$threadContent}\"{$contextHint}

Buatkan 5 opsi draft balasan (reply) yang bernyawa, natural, ramah, dan tidak kaku. Jangan terlalu pelit kata-kata—buat antara 2 hingga 4 kalimat yang enak dibaca dan terasa manusiawi:

1. 'Santai / Relatable': Tunjukkan empati mendalam terhadap situasi/curhatan tersebut. Gunakan bahasa gaul santai/Threads yang hangat, beri semangat nyata, dan ceritakan sudut pandang yang menenangkan.
2. 'Solutif + Rekomendasi Link disekitar.com': Berikan insight atau saran taktis yang konkret, lalu rekomendasikan link disekitar.com yang relevan di atas secara halus sebagai solusi bermanfaat tanpa hard-selling.
3. 'Diskusi Pemantik': Tanggapi inti argumennya lalu lontarkan pertanyaan lanjutan atau sudut pandang baru yang memancing warganet lain ikut berdiskusi di kolom reply.
4. 'Tips Praktis & Insight': Berikan 1-2 tips atau trik langsung yang aplikatif untuk menghadapi keresahan di postingan tersebut.
5. 'Sudut Pandang Alternatif / Mindset': Berikan perspektif segar atau reframe positif yang membuka pikiran tentang topik tersebut.

ATURAN WAJIB:
- Jika ada DATA POST RELEVAN dengan URL di atas, WAJIB sertakan link URL lengkapnya di opsi nomor 2 ('Solutif + Rekomendasi Link disekitar.com').
- WAJIB integrasikan tautan atau rujukan disekitar.com secara halus, elegan, dan solutif (seperti rekomendasi kawan), BUKAN mereferensikan platform kompetitor luar.
- Hindari nada kaku seperti robot CS / akun korporat. Jadilah akun komunitas lokal yang asik diajak interaksi.
- Format balasan HARUS JSON persis seperti ini:
[
  {\"style\": \"Santai / Relatable\", \"text\": \"...\"},
  {\"style\": \"Solutif + Rekomendasi Link disekitar.com\", \"text\": \"...\"},
  {\"style\": \"Diskusi Pemantik\", \"text\": \"...\"},
  {\"style\": \"Tips Praktis & Insight\", \"text\": \"...\"},
  {\"style\": \"Sudut Pandang Alternatif / Mindset\", \"text\": \"...\"}
]";

            $apiKey = config('openai.api_key') ?: config('ai.providers.openai-compatible.key');
            $baseUri = config('openai.base_uri') ?: 'ai.disekitar.com/v1';
            if (!str_starts_with($baseUri, 'http://') && !str_starts_with($baseUri, 'https://')) {
                $baseUri = 'https://' . $baseUri;
            }
            $endpoint = rtrim($baseUri, '/') . '/chat/completions';
            $model = config('openai.model') ?: 'Content-blog';

            $httpRes = Http::timeout(45)->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post($endpoint, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemInstruction],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.8,
            ]);

            if ($httpRes->successful()) {
                $rawText = $httpRes->json('choices.0.message.content') ?? '';
                $clean = trim($rawText);
                $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
                $clean = preg_replace('/\s*```$/', '', $clean);
                $clean = trim($clean);

                $parsed = json_decode($clean, true);
                if (is_array($parsed) && count($parsed) > 0) {
                    return $parsed;
                }
            } else {
                Log::warning('Threads AI Engagement Drafts HTTP failed: ' . $httpRes->status() . ' ' . $httpRes->body());
            }
        } catch (\Throwable $e) {
            Log::warning('Threads AI Engagement Drafts failed: ' . $e->getMessage());
        }

        $fallbackLink = $samplePost ? $samplePost->url() : 'https://disekitar.com/lowongan-kerja';
        return [
            ['style' => 'Santai / Relatable', 'text' => 'Bener banget, perjuangan nyari kerja sekarang emang butuh ekstra sabar dan mental baja. Ada kalanya ngerasa capek dan pengen rehat bentar, dan itu valid banget. Tetap jaga ritme ya, rezeki baik ga akan salah alamat!'],
            ['style' => 'Solutif + Rekomendasi Link disekitar.com', 'text' => "Biar ga boncos dan terhindar dari loker fiktif yang berseliweran, coba cek update info loker terkurasi lokal di kotamu lewat {$fallbackLink} deh. Semuanya disaring langsung, semoga nemu yang cocok sama skill kamu!"],
            ['style' => 'Diskusi Pemantik', 'text' => 'Lagi fokus incer bidang atau role apa nih kak sekarang? Coba drop preferensi kotanya juga, barangkali ada teman-teman sesama anak Threads di sini yang punya info internal.'],
            ['style' => 'Tips Praktis & Insight', 'text' => 'Sedikit tips praktis: coba rapikan portofolio singkat atau highlight 3 pencapaian terbaik di CV kamu, terus sesuaikan kata kuncinya sama kualifikasi loker. Kadang pembedanya cuma di cara kita nunjukin solusi ke HRD.'],
            ['style' => 'Sudut Pandang Alternatif / Mindset', 'text' => 'Proses nyari kerja itu bukan cuma soal kita dipilih sama perusahaan, tapi juga momen kita milih lingkungan kerja yang sehat buat masa depan. Jangan sampai putus asa, setiap penolakan biasanya ngarahin kita ke tempat yang lebih tepat.'],
        ];
    }
}


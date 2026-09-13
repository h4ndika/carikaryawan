<?php

namespace App\Services;

use App\Ai\DisekitarAgent;
use App\Models\Article;
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

            // Coba generate copy percakapan berbasis AI
            $aiText = $this->generateAiJobCopy($cleanTitle, $company, $titleCaseCity, $url, $topicTagCity);

            if ($aiText) {
                $text = $aiText;
            } else {
                // Fallback ke template percakapan terstruktur
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
            }
        } else {
            $categoryName = $post->category?->name ?? 'Tempat';
            $catTag = preg_replace('/[^a-zA-Z0-9]/', '', ucwords(strtolower($categoryName)));
            $cityTag = preg_replace('/[^a-zA-Z0-9]/', '', $titleCaseCity);

            $topicTag = $topicTagCity ?: 'kuliner';

            // Coba generate copy percakapan berbasis AI
            $aiText = $this->generateAiPlaceCopy($cleanTitle, $categoryName, $titleCaseCity, $post->excerpt, $url, $cityTag, $catTag);

            if ($aiText) {
                $text = $aiText;
            } else {
                // Fallback template
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
        $rawCityName = $article->city?->name ?? 'Indonesia';
        // Bersihkan prefix 'KOTA ' / 'KAB. ' / 'KABUPATEN '
        $cleanCityOnly = trim(preg_replace('/^(KOTA|KAB\.?|KABUPATEN)\s+/i', '', $rawCityName));
        $titleCaseCity = ucwords(strtolower($cleanCityOnly));
        $topicTagCity = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $cleanCityOnly));
        $topicTag = $topicTagCity ?: 'rekomendasi';

        $cleanTitle = trim($article->title);

        $aiText = $this->generateAiArticleCopy($cleanTitle, $titleCaseCity, $article->excerpt, $url, $topicTagCity);

        if ($aiText) {
            $text = $aiText;
        } else {
            $intros = [
                "Buat yang lagi ngerencanain main atau hunting spot disekitar {$titleCaseCity}, kita baru beresin panduannya nih.",
                "Lagi butuh ide buat jalan-jalan disekitar {$titleCaseCity}? Coba intip kurasi ini:",
                "Kumpulan tempat rekomendasi disekitar {$titleCaseCity} yang patut kamu coba:",
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
            $result = $this->postText($text, $url, $topicTag);
        }

        // Mark article as shared to Threads
        $article->updateQuietly(['posted_to_threads_at' => now()]);

        return $result;
    }

    /**
     * Generate conversation-driven job post copy using AI.
     */
    protected function generateAiJobCopy(string $title, string $company, string $city, string $url, string $topicTagCity): ?string
    {
        try {
            $systemInstruction = "Kamu adalah social media writer untuk Threads @disekitarcom. Tulislah postingan Threads yang santai, conversation-driven (seperti sesama pencari kerja/warga lokal, bukan robot/korporat). Balas HANYA dengan teks postingan murni (plain text), tanpa code block, tanpa penjelasan pembuka/penutup.";

            $prompt = "Buat teks postingan Threads untuk lowongan kerja berikut:
- Posisi: {$title}
- Perusahaan: " . ($company ?: 'Brand Lokal') . "
- Lokasi: disekitar {$city}
- Link: {$url}

ATURAN WAJIB:
1. Mulai dengan kalimat pembuka santai atau observasi persaingan kerja di {$city}.
2. Tulis posisi & perusahaan secara jelas tapi natural.
3. Cantumkan link: {$url}
4. Di bagian akhir, berikan 1 PERTANYAAN PEMANTIK REPLY yang relevan (misal tentang pengalaman interview, tantangan cari loker di kota tersebut).
5. Akhiri dengan hashtag: #Loker{$topicTagCity} #Disekitar
6. Panjang TOTAL teks harus di bawah 450 karakter.";

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

            if (!empty($clean) && mb_strlen($clean) <= 495 && str_contains($clean, $url)) {
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
    protected function generateAiPlaceCopy(string $title, string $category, string $city, ?string $excerpt, string $url, string $cityTag, string $catTag): ?string
    {
        try {
            $systemInstruction = "Kamu adalah content writer Threads @disekitarcom yang gemar eksplorasi tempat lokal Indonesia. Tulislah postingan Threads yang santai, visual, dan memancing reply warga lokal. Balas HANYA dengan teks postingan murni (plain text), tanpa markdown code block, tanpa pembuka/penutup.";

            $prompt = "Buat teks postingan Threads rekomendasi tempat:
- Tempat: {$title}
- Kategori: {$category}
- Kota: disekitar {$city}
- Catatan: " . ($excerpt ?: 'Spot asik dan nyaman') . "
- Link: {$url}

ATURAN WAJIB:
1. Tulis pengalaman/observasi santai tentang suasana tempat atau kebiasaan nongkrong di {$city}.
2. Sebutkan nama tempat: {$title}.
3. Cantumkan link ulasan: {$url}
4. Akhiri dengan 1 PERTANYAAN PEMICU REPLY yang ramah (misal tanya tempat nongkrong/hidden gem favorit warga lokal di {$city}).
5. Sertakan hashtag: #Kuliner{$cityTag} #Wisata{$cityTag} #{$catTag} #Disekitar
6. Panjang TOTAL teks harus di bawah 450 karakter.";

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

            if (!empty($clean) && mb_strlen($clean) <= 495 && str_contains($clean, $url)) {
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
    protected function generateAiArticleCopy(string $title, string $city, ?string $excerpt, string $url, string $topicTagCity): ?string
    {
        try {
            $systemInstruction = "Kamu adalah editor kurasi perjalanan dan kuliner Threads @disekitarcom. Tulislah postingan Threads yang conversation-driven dan pancing opini warga lokal. Balas HANYA dengan teks murni tanpa code block.";

            $prompt = "Buat teks Threads untuk panduan kurasi:
- Judul: {$title}
- Kota: disekitar {$city}
- Link: {$url}

ATURAN WAJIB:
1. Awali dengan rasa penasaran atau opini jujur seputar kuliner/tempat main di {$city}.
2. Cantumkan link bacaan: {$url}
3. Beri 1 PERTANYAAN PEMICU REPLY yang seru (misal mana tempat yang menurut warga lokal paling wajib dikunjungi).
4. Akhiri dengan hashtag #{$topicTagCity} #Disekitar
5. Panjang TOTAL di bawah 450 karakter.";

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

            if (!empty($clean) && mb_strlen($clean) <= 495 && str_contains($clean, $url)) {
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
            $systemInstruction = "Kamu adalah social media specialist untuk @disekitarcom di platform Threads. Gaya bicaramu autentik, santai, cerdas, dan membaur layaknya pengguna asli Threads (bukan sales atau akun promosi kaku). Balas HANYA dengan JSON valid berisi 3 draft balasan dengan gaya berbeda.";

            $prompt = "Berikut adalah konten postingan Thread seseorang (topik: {$topic}):
\"{$threadContent}\"

Buatkan 3 pilihan draft reply singkat (di bawah 200 karakter per draft) untuk merespon postingan tersebut:
1. 'Santai / Relatable' (sependapat, berbagi pengalaman serupa)
2. 'Nilai Tambah / Insight' (memberi saran atau info pelengkap yang bermanfaat)
3. 'Pertanyaan Pemantik' (mengajak diskusi lebih dalam secara hangat)

ATURAN PENTING:
- Jangan jualan terang-terangan / hard selling.
- Format balasan HARUS JSON persis seperti ini:
[
  {\"style\": \"Santai / Relatable\", \"text\": \"...\"},
  {\"style\": \"Insight / Nilai Tambah\", \"text\": \"...\"},
  {\"style\": \"Diskusi Pemantik\", \"text\": \"...\"}
]";

            $agent = new DisekitarAgent(instructions: $systemInstruction);
            $response = (string) $agent->prompt(
                prompt: $prompt,
                provider: 'openai-compatible',
                model: 'Content-blog',
                timeout: 30
            );

            $clean = trim($response);
            $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
            $clean = preg_replace('/\s*```$/', '', $clean);
            $clean = trim($clean);

            $parsed = json_decode($clean, true);
            if (is_array($parsed) && count($parsed) > 0) {
                return $parsed;
            }
        } catch (\Throwable $e) {
            Log::warning('Threads AI Engagement Drafts failed: ' . $e->getMessage());
        }

        return [
            ['style' => 'Santai / Relatable', 'text' => 'Setuju banget sih sama poin ini. Banyak yang ngerasain hal serupa belakangan ini.'],
            ['style' => 'Insight / Nilai Tambah', 'text' => 'Bener, apalagi kalau diperhatiin faktor lokasinya juga ngaruh banget ke hal ini.'],
            ['style' => 'Diskusi Pemantik', 'text' => 'Menarik nih pembahasannya! Menurut kalian apa faktor paling menantang dari ini?'],
        ];
    }
}


<?php

namespace App\Services;

use App\Models\AiGenerationJob;
use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use App\Models\Province;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

class PostAiContentService
{
    /**
     * Build standard prompt for a Post model.
     */
    public function buildPrompt(Post $post, ?string $additionalPrompt = null): string
    {
        $post->loadMissing(['category', 'city', 'province']);

        $categoryName = $post->category?->name ?? 'Tempat Menarik';
        $cityName = $post->city?->name ?? '';
        $provinceName = $post->province?->name ?? '';
        $title = $post->title;

        $promptUser = $additionalPrompt ?: 'Deskripsikan dengan format Tentang, Fasilitas, Lokasi, dan Harga/Informasi Praktis.';

        // Build context from existing metadata if present
        $extraContext = [];
        if ($post->formatted_address) {
            $extraContext[] = "Alamat: {$post->formatted_address}";
        }
        if ($post->rating) {
            $extraContext[] = "Rating: {$post->rating} ({$post->reviews_count} ulasan)";
        }
        if ($post->phone) {
            $extraContext[] = "Telepon: {$post->phone}";
        }
        if ($post->website) {
            $extraContext[] = "Website: {$post->website}";
        }
        $extraContextStr = !empty($extraContext) ? 'Informasi tempat dari data Google Maps: ' . implode(' | ', $extraContext) . '.' : '';

        return "Buatkan artikel editorial menarik dengan Judul : \"$title\", yang berlokasi di $cityName, $provinceName. " .
            "Artikel ini berkategori $categoryName. " .
            ($extraContextStr ? "$extraContextStr " : '') .
            'Tulis dengan bahasa Indonesia yang natural, informatif, dan mengalir seperti majalah perjalanan/lifestyle terpercaya. ' .
            'PENTING: Langsung mulai dengan judul artikel menggunakan tag <h2> atau <h1>. JANGAN berikan teks pengantar, penjelasan instruksi, pemikiran, atau salam pembuka/penutup. ' .
            'Format artikel dalam HTML bersih (gunakan tag <h2>, <h3>, <p>, <ul>, <li>), jangan gunakan blok markdown atau backticks. ' .
            'Tambahkan juga data detail dalam format JSON di akhir output, dibungkus tag <json>...</json> sesuai kategori, misal: ' .
            '- Kategori Hotel, Wisata, Kuliner dll : harga, lokasi, jam_buka, fasilitas, rekomendasi ' .
            '- Kategori Lowongan Kerja: posisi, gaji, lokasi, kualifikasi, deskripsi_pekerjaan ' .
            '- atau key spesifik lainnya yang relevan dengan topik ini. ' .
            'Format JSON: ' .
            '{
                "key1": "value1",
                "key2": "value2"
            } ' .
            "Prompt tambahan: $promptUser";
    }

    /**
     * Apply and clean generated AI content to a Post model.
     */
    public function applyGeneratedContent(Post $post, string $rawGenerated, bool $publishOnComplete = false): array
    {
        $cleaned = self::cleanGeneratedContent($rawGenerated);
        $cleanContent = $cleaned['content'];
        $detailsArray = $cleaned['details'];

        // Validasi: pastikan konten bersih bukan hanya komentar/pemikiran kosong
        $wordCount = str_word_count(strip_tags($cleanContent));
        if ($wordCount < 40) {
            throw new \RuntimeException("Hasil generate AI tidak valid atau terpotong (panjang konten hanya {$wordCount} kata). Silakan generate ulang.");
        }

        // Auto-generate excerpt if empty
        $plainText = strip_tags($cleanContent);
        $excerpt = $post->excerpt ?: Str::limit($plainText, 160);

        // Update Post
        $updateData = [
            'content' => $cleanContent,
            'excerpt' => $excerpt,
        ];

        if ($publishOnComplete) {
            $updateData['status'] = 'published';
            if (is_null($post->published_at)) {
                $updateData['published_at'] = now();
            }
        }

        $post->update($updateData);

        // Sync Details
        if (!empty($detailsArray)) {
            $post->details()->delete();
            foreach ($detailsArray as $detail) {
                $post->details()->create([
                    'meta_key' => $detail['meta_key'],
                    'meta_value' => $detail['meta_value'],
                ]);
            }
        }

        return [
            'content' => $cleanContent,
            'excerpt' => $excerpt,
            'details' => $detailsArray,
        ];
    }

    /**
     * Generate content, details, and excerpt for a single Post model.
     */
    public function generateForPost(Post $post, ?string $additionalPrompt = null, bool $publishOnComplete = false): array
    {
        $finalPrompt = $this->buildPrompt($post, $additionalPrompt);

        $response = OpenAI::chat()->create([
            'model' => config('openai.model', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => 'Kamu adalah jurnalis dan editor konten profesional. JAWAB HANYA dengan konten HTML artikel langsung (dimulai dengan <h2>/<h1> judul) tanpa pengantar, tanpa pemikiran/thinking, tanpa kata pembuka, dan tanpa penutup.'],
                ['role' => 'user', 'content' => $finalPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 3000,
            'stream' => false,
        ]);

        $rawGenerated = $response->choices[0]->message->content ?? '';

        return $this->applyGeneratedContent($post, $rawGenerated, $publishOnComplete);
    }

    /**
     * Process multiple AiGenerationJob items concurrently in parallel batches (e.g. 5-10 at once).
     */
    public function generateBatchConcurrent(Collection|array $aiJobs, int $concurrency = 5): array
    {
        if (is_array($aiJobs)) {
            $aiJobs = AiGenerationJob::with('post.category', 'post.city', 'post.province')->whereIn('id', collect($aiJobs)->pluck('id'))->get();
        } else {
            $aiJobs->loadMissing('post.category', 'post.city', 'post.province');
        }

        $total = $aiJobs->count();
        $completed = 0;
        $failed = 0;

        if ($total === 0) {
            return ['total' => 0, 'completed' => 0, 'failed' => 0];
        }

        // Prepare endpoint and headers
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

        // Split into parallel chunks of $concurrency
        $chunks = $aiJobs->chunk(max(1, $concurrency));

        foreach ($chunks as $chunk) {
            // Mark all jobs in this chunk as processing
            foreach ($chunk as $job) {
                $job->update([
                    'status' => 'processing',
                    'started_at' => now(),
                    'error_message' => null,
                ]);
            }

            // Dispatch concurrent requests via HTTP Pool
            $responses = Http::pool(function (Pool $pool) use ($chunk, $endpoint, $headers, $model) {
                $requests = [];
                foreach ($chunk as $job) {
                    if (!$job->post) {
                        continue;
                    }
                    $prompt = $this->buildPrompt($job->post, $job->prompt_additional);

                    $requests[(string) $job->id] = $pool->as((string) $job->id)
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

            // Process results for this chunk
            foreach ($chunk as $job) {
                if (!$job->post) {
                    $job->update([
                        'status' => 'failed',
                        'error_message' => 'Post tidak ditemukan.',
                        'finished_at' => now(),
                    ]);
                    $failed++;
                    continue;
                }

                $jobIdKey = (string) $job->id;
                $res = $responses[$jobIdKey] ?? null;

                if ($res instanceof Throwable) {
                    $job->update([
                        'status' => 'failed',
                        'error_message' => $res->getMessage(),
                        'finished_at' => now(),
                    ]);
                    $failed++;
                    continue;
                }

                if (!$res || !$res->successful()) {
                    $errText = $res ? ($res->json('error.message') ?? $res->body()) : 'Request gagal atau timeout.';
                    $job->update([
                        'status' => 'failed',
                        'error_message' => Str::limit($errText, 500),
                        'finished_at' => now(),
                    ]);
                    $failed++;
                    continue;
                }

                try {
                    $jsonBody = $res->json();
                    $rawGenerated = $jsonBody['choices'][0]['message']['content'] ?? '';

                    $this->applyGeneratedContent($job->post, $rawGenerated, (bool) $job->publish_on_complete);

                    $job->update([
                        'status' => 'completed',
                        'finished_at' => now(),
                    ]);
                    $completed++;
                } catch (Throwable $e) {
                    $job->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'finished_at' => now(),
                    ]);
                    $failed++;
                }
            }
        }

        return [
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
        ];
    }

    /**
     * Clean raw AI response from thoughts, preambles, markdown ticks, and extract JSON details.
     */
    public static function cleanGeneratedContent(string $raw): array
    {
        $text = $raw;

        // 1. Remove reasoning / thought tags (<think>...</think>, <thought>...</thought>, including unclosed ones)
        $text = preg_replace('/<think>.*?(?:<\/think>|$)/is', '', $text);
        $text = preg_replace('/<thought>.*?(?:<\/thought>|$)/is', '', $text);

        // 2. Remove markdown code fences
        $text = preg_replace('/^```(?:html)?\s*/i', '', $text);
        $text = preg_replace('/```$/', '', $text);

        // 3. Extract JSON details before cleaning HTML
        $detailsArray = [];
        if (preg_match('/<json>(.*?)(?:<\/json>|$)/is', $text, $jsonMatch)) {
            $jsonData = json_decode(trim($jsonMatch[1]), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                foreach ($jsonData as $key => $value) {
                    $detailsArray[] = [
                        'meta_key' => $key,
                        'meta_value' => is_array($value) ? implode(', ', $value) : (string) $value,
                    ];
                }
            }
            $text = preg_replace('/<json>.*?(?:<\/json>|$)/is', '', $text);
        }

        // 4. Strip English reasoning preambles or conversational filler before the first real HTML heading or paragraph
        if (preg_match('/(<h[1-3][^>]*>[\s\S]*?<\/h[1-3]>)/i', $text, $headingMatch, PREG_OFFSET_CAPTURE)) {
            $headingPos = $headingMatch[0][1];
            $text = substr($text, $headingPos);
        } elseif (preg_match('/(<p[^>]*>[\s\S]*?<\/p>)/i', $text, $pMatch, PREG_OFFSET_CAPTURE)) {
            $pPos = $pMatch[0][1];
            $beforeP = substr($text, 0, $pPos);
            if (preg_match('/(?:okay|alright|user|thinking|let\'s|here is|berikut)/i', $beforeP)) {
                $text = substr($text, $pPos);
            }
        }

        // 5. Bersihkan sisa-sisa pola reasoning umum jika masih terselip di awal
        $text = preg_replace('/^(?:\s*(?:Okay,?\s+so|Alright,?\s+|Thinking Process|The user wants|The user is asking|I will write|I need to|First,?\s+I\'ll|Let\'s create|Let\'s break down|We need to|Here is|Here\'s|Berikut adalah|Tentu|Baiklah|Sure|Certainly)[^\n<]*\n+)+/is', '', $text);

        // 6. Remove conversational closing sentences
        $closingPatterns = [
            '/<p>\s*(?:Semoga|Hope this|Let me know|Demikian|Sekian).*?<\/p>\s*$/is',
            '/(?:Semoga bermanfaat|Hope this helps|Let me know if you need).*$/is',
        ];
        foreach ($closingPatterns as $pattern) {
            $text = preg_replace($pattern, '', $text);
        }

        return [
            'content' => trim($text),
            'details' => $detailsArray,
        ];
    }
}

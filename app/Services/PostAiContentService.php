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
use RuntimeException;
use Throwable;

class PostAiContentService
{
    /**
     * Build the job-listing prompt for a Post model.
     */
    public function buildPrompt(Post $post, ?string $additionalPrompt = null): string
    {
        $post->loadMissing(['category', 'city', 'province']);

        $title = $post->title;

        // Build context from existing metadata if present
        $extraContext = [];
        if ($post->formatted_address) {
            $extraContext[] = "Alamat: {$post->formatted_address}";
        }
        if ($post->phone) {
            $extraContext[] = "Telepon: {$post->phone}";
        }
        if ($post->website) {
            $extraContext[] = "Website: {$post->website}";
        }

        $post->loadMissing('details');
        $existingDetails = $post->details->pluck('meta_value', 'meta_key')->toArray();
        $extraContext[] = 'Fakta sumber (jangan diubah): ' . json_encode($existingDetails, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $existingDesc = trim(strip_tags($post->content));
        if (!empty($existingDesc)) {
            $extraContext[] = "Deskripsi Lowongan Asli dari Perusahaan: " . Str::limit($existingDesc, 800);
        }
        if (!empty($existingDetails['cara_melamar'])) {
            $extraContext[] = "Cara Melamar Asli: " . $existingDetails['cara_melamar'];
        }

        $extraContextStr = !empty($extraContext) ? 'Informasi pelengkap: ' . implode(' | ', $extraContext) . '.' : '';

        return "Rapikan informasi lowongan kerja untuk posisi dan perusahaan: \"$title\" hanya berdasarkan fakta sumber. " .
            'Jangan mengarang lokasi, gaji, benefit, kualifikasi, atau tipe pekerjaan. Jika tidak disebutkan sumber, isi null dalam JSON dan jangan tambahkan klaim tersebut dalam artikel. ' .
            ($extraContextStr ? "$extraContextStr " : '') .
            'Tulis dengan bahasa Indonesia yang jelas, menarik bagi pencari kerja, dan terstruktur rapi. ' .
            'Gunakan susunan subjudul: <h2>Ringkasan Lowongan</h2>, <h3>Kualifikasi & Persyaratan</h3>, <h3>Tanggung Jawab Pekerjaan</h3>, dan <h3>Benefit & Cara Melamar</h3>. ' .
            'PENTING & WAJIB TERKAIT TAUTAN/URL: ' .
            'Pada bagian Cara Melamar, JANGAN melampirkan atau menuliskan URL tautan mentah (raw link seperti https://glints.com/..., https://jobstreet.co.id/..., linkedin, dll.). ' .
            'Sebagai gantinya, arahkan pembaca untuk mengklik tombol resmi "Lamar Sekarang" yang tersedia di halaman informasi lowongan ini, serta siapkan berkas CV/portofolio terbaik mereka. ' .
            'Langsung mulai dengan judul artikel menggunakan tag <h2>. JANGAN berikan teks pengantar, instruksi, atau salam pembuka/penutup. ' .
            'Format dalam HTML bersih (gunakan tag <h2>, <h3>, <p>, <ul>, <li>), tanpa blok markdown/backticks. ' .
            'Sertakan juga data detail terstruktur dalam format JSON di akhir output, dibungkus tag <json>...</json>: ' .
            'Format JSON: ' .
            '{
                "posisi": "Nama Posisi",
                "perusahaan": "Nama Perusahaan",
                "lokasi": null,
                "gaji": null,
                "kualifikasi": "Ringkasan kualifikasi utama",
                "tipe_pekerjaan": "Full Time / Kontrak / Part Time / Magang"
            } ' .
            ($additionalPrompt ? "Prompt tambahan: $additionalPrompt" : '');
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

        \Illuminate\Support\Facades\DB::transaction(function () use ($post, $cleanContent, $excerpt, $publishOnComplete, $detailsArray) {
            // Update Post
            $updateData = [
                'content' => $cleanContent,
                'excerpt' => $excerpt,
            ];

            if ($publishOnComplete && $post->city_id) {
                $updateData['status'] = 'published';
                if (is_null($post->published_at)) {
                    $updateData['published_at'] = now();
                }
            }

            $post->update($updateData);

            // Sync Details using upsert/merge to preserve important original fields (like cara_melamar / apply_url)
            if (!empty($detailsArray)) {
                $existingDetails = \App\Models\PostDetail::where('post_id', $post->id)->pluck('meta_value', 'meta_key')->toArray();
                \App\Models\PostDetail::where('post_id', $post->id)->forceDelete();
                $now = now();
                $merged = $existingDetails;
                foreach ($detailsArray as $detail) {
                    $key = (string) $detail['meta_key'];
                    $val = (string) $detail['meta_value'];
                    // Jaga agar cara_melamar / website link asli tidak tertimpa teks dummy AI
                    if (!in_array($key, ['posisi', 'perusahaan', 'lokasi', 'gaji', 'kualifikasi', 'tipe_pekerjaan'], true)
                        || trim((string) ($existingDetails[$key] ?? '')) !== '' || trim($val) === '') {
                        continue;
                    }
                    $merged[$key] = $val;
                }

                $insertRows = [];
                foreach ($merged as $k => $v) {
                    $insertRows[] = [
                        'post_id' => $post->id,
                        'meta_key' => $k,
                        'meta_value' => $v,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                \App\Models\PostDetail::insert($insertRows);
            }
        });

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

        $httpResponse = Http::withHeaders($headers)
            ->timeout(180)
            ->post($endpoint, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Kamu adalah editor konten lowongan kerja profesional. JAWAB HANYA dengan konten HTML artikel langsung (dimulai dengan <h2>/<h1> judul) tanpa pengantar, tanpa pemikiran/thinking, tanpa kata pembuka, dan tanpa penutup.'],
                    ['role' => 'user', 'content' => $finalPrompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 3000,
                'stream' => false,
            ]);

        if (!$httpResponse->successful()) {
            $err = $httpResponse->json('error.message') ?? $httpResponse->body();
            throw new RuntimeException("API AI Error ({$httpResponse->status()}): " . Str::limit($err, 300));
        }

        $rawGenerated = $this->parseAiResponseContent($httpResponse->body());

        return $this->applyGeneratedContent($post, $rawGenerated, $publishOnComplete);
    }

    /**
     * Process multiple AiGenerationJob items concurrently in parallel batches (e.g. 25 at once).
     */
    public function generateBatchConcurrent(Collection|array $aiJobs, int $concurrency = 25): array
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
            // Mark all jobs in this chunk as processing in ONE fast query
            AiGenerationJob::whereIn('id', $chunk->pluck('id'))->update([
                'status' => 'processing',
                'started_at' => now(),
                'error_message' => null,
            ]);

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
                                ['role' => 'system', 'content' => 'Kamu adalah editor konten lowongan kerja profesional. JAWAB HANYA dengan konten HTML artikel langsung (dimulai dengan <h2>/<h1> judul) tanpa pengantar, tanpa pemikiran/thinking, tanpa kata pembuka, dan tanpa penutup.'],
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
                    $rawGenerated = self::parseAiResponseContent($res->body());

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
     * Extract full content text from standard OpenAI JSON response or SSE text/event-stream chunks.
     */
    public static function parseAiResponseContent(string $rawBody): string
    {
        $rawBody = trim($rawBody);
        if ($rawBody === '') {
            return '';
        }

        // Standard single JSON payload
        if (str_starts_with($rawBody, '{')) {
            $json = json_decode($rawBody, true);
            if (isset($json['choices'][0]['message']['content'])) {
                return (string) $json['choices'][0]['message']['content'];
            }
            if (isset($json['choices'][0]['text'])) {
                return (string) $json['choices'][0]['text'];
            }
        }

        // SSE text/event-stream chunks (data: {...}\n\ndata: {...})
        $assembled = '';
        $lines = preg_split('/\r\n|\r|\n/', $rawBody);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!str_starts_with($line, 'data:')) {
                continue;
            }
            $dataStr = trim(substr($line, 5));
            if ($dataStr === '[DONE]' || $dataStr === '') {
                continue;
            }
            $chunk = json_decode($dataStr, true);
            if (!is_array($chunk)) {
                continue;
            }
            if (isset($chunk['choices'][0]['delta']['content'])) {
                $assembled .= $chunk['choices'][0]['delta']['content'];
            } elseif (isset($chunk['choices'][0]['text'])) {
                $assembled .= $chunk['choices'][0]['text'];
            } elseif (isset($chunk['choices'][0]['message']['content'])) {
                $assembled .= $chunk['choices'][0]['message']['content'];
            }
        }

        return trim($assembled);
    }

    /**
     * Clean raw AI response from thoughts, preambles, markdown ticks, and extract JSON details.
     */
    public static function cleanGeneratedContent(string $raw): array
    {
        $text = $raw;

        // 1. Remove reasoning / thought tags (<think>...</think>, <thought>...</thought>, including unclosed ones)
        $text = preg_replace('/<think>[\s\S]*?(?:<\/think>|$)/i', '', $text);
        $text = preg_replace('/<thought>[\s\S]*?(?:<\/thought>|$)/i', '', $text);

        // 2. Remove markdown code fences
        $text = preg_replace('/^```(?:html)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

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
            $text = preg_replace('/<json>[\s\S]*?(?:<\/json>|$)/is', '', $text);
        }

        // 4. Strip reasoning preambles or conversational filler before the first real HTML heading or paragraph
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

        // 5. Remove conversational closing sentences
        $closingPatterns = [
            '/<p>\s*(?:Semoga|Hope this|Let me know|Demikian|Sekian).*?<\/p>\s*$/is',
            '/(?:Semoga bermanfaat|Hope this helps|Let me know if you need).*$/is',
        ];
        foreach ($closingPatterns as $pattern) {
            $text = preg_replace($pattern, '', $text);
        }

        // 6. Clean up raw external job board links in content (e.g. Glints, Jobstreet, Kalibrr, LinkedIn job links)
        // ponytail: converts raw application URLs in article prose into a clear call-to-action for the page's "Lamar Sekarang" button
        $text = preg_replace('/<a\s+[^>]*href=["\']https?:\/\/(?:www\.)?(?:glints\.com|jobstreet\.co\.id|jobstreet\.com|kalibrr\.com|karir\.com|kitalulus\.com|linkedin\.com\/jobs)[^"\']*["\'][^>]*>.*?<\/a>/is', 'tombol <strong>"Lamar Sekarang"</strong> di halaman ini', $text);
        $text = preg_replace('/https?:\/\/(?:www\.)?(?:glints\.com|jobstreet\.co\.id|jobstreet\.com|kalibrr\.com|karir\.com|kitalulus\.com|linkedin\.com\/jobs)\/[^\s<"\'\)]+/is', 'tombol <strong>"Lamar Sekarang"</strong> di halaman ini', $text);

        return [
            'content' => trim($text),
            'details' => $detailsArray,
        ];
    }
}

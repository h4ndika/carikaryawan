<?php

namespace App\Services;

use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use App\Models\Province;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;

class PostAiContentService
{
    /**
     * Generate content, details, and excerpt for a Post model and save directly.
     */
    public function generateForPost(Post $post, ?string $additionalPrompt = null, bool $publishOnComplete = false): array
    {
        $post->loadMissing(['category', 'city', 'province', 'details']);

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

        $finalPrompt = "Buatkan artikel editorial menarik dengan Judul : \"$title\", yang berlokasi di $cityName, $provinceName. " .
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

        $response = OpenAI::chat()->create([
            'model' => config('openai.model', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => 'Kamu adalah jurnalis dan editor konten profesional. JAWAB HANYA dengan konten HTML artikel langsung (dimulai dengan <h2>/<h1> judul) tanpa pengantar, tanpa pemikiran, tanpa kata pembuka, dan tanpa penutup.'],
                ['role' => 'user', 'content' => $finalPrompt],
            ],
            'temperature' => 0.7,
            'stream' => false,
        ]);

        $rawGenerated = $response->choices[0]->message->content ?? '';

        $cleaned = self::cleanGeneratedContent($rawGenerated);
        $cleanContent = $cleaned['content'];
        $detailsArray = $cleaned['details'];

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
     * Clean raw AI response from thoughts, preambles, markdown ticks, and extract JSON details.
     */
    public static function cleanGeneratedContent(string $raw): array
    {
        $text = $raw;

        // 1. Remove reasoning / thought tags (<think>...</think>, <thought>...</thought>)
        $text = preg_replace('/<think>.*?<\/think>/is', '', $text);
        $text = preg_replace('/<thought>.*?<\/thought>/is', '', $text);

        // 2. Remove markdown code fences
        $text = preg_replace('/^```(?:html)?\s*/i', '', $text);
        $text = preg_replace('/```$/', '', $text);

        // 3. Extract JSON details before cleaning HTML
        $detailsArray = [];
        if (preg_match('/<json>(.*?)<\/json>/is', $text, $jsonMatch)) {
            $jsonData = json_decode(trim($jsonMatch[1]), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                foreach ($jsonData as $key => $value) {
                    $detailsArray[] = [
                        'meta_key' => $key,
                        'meta_value' => is_array($value) ? implode(', ', $value) : (string) $value,
                    ];
                }
            }
            $text = preg_replace('/<json>.*?<\/json>/is', '', $text);
        }

        // 4. Strip conversational preambles / thoughts before the first HTML heading (<h1..3>)
        if (preg_match('/(<h[1-3][^>]*>.*)$/is', $text, $headingMatch)) {
            $text = $headingMatch[1];
        } else {
            // If no <h1>-<h3> tag found, strip common conversational intro lines
            $text = preg_replace('/^(?:The user is asking|Here is|Here\'s|Berikut adalah|Tentu|Baiklah|Sure|Certainly|I will|I\'ll create|Let\'s create|This article)[^\n<]*\n+/i', '', $text);
        }

        // 5. Remove conversational closing sentences
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

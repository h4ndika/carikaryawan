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
            'Format artikel dalam HTML bersih (gunakan tag <h2>, <h3>, <p>, <ul>, <li>), jangan gunakan blok markdown atau backticks. Tanpa teks penutup atau salam. ' .
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
                ['role' => 'system', 'content' => 'Kamu adalah jurnalis dan editor konten profesional yang membuat artikel panduan lokal berkualitas tinggi dalam format HTML.'],
                ['role' => 'user', 'content' => $finalPrompt],
            ],
            'temperature' => 0.7,
            'stream' => false,
        ]);

        $generated = $response->choices[0]->message->content ?? '';

        // Clean markdown blocks
        $generated = preg_replace('/^```html\s*/i', '', $generated);
        $generated = preg_replace('/^```\s*/i', '', $generated);
        $generated = preg_replace('/```$/', '', $generated);
        $generated = preg_replace('/Semoga.*$/is', '', $generated);

        // Extract JSON details
        $detailsArray = [];
        if (preg_match('/<json>(.*?)<\/json>/s', $generated, $jsonMatch)) {
            $jsonData = json_decode(trim($jsonMatch[1]), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                foreach ($jsonData as $key => $value) {
                    $detailsArray[] = [
                        'meta_key' => $key,
                        'meta_value' => is_array($value) ? implode(', ', $value) : (string) $value,
                    ];
                }
            }
            $generated = preg_replace('/<json>.*<\/json>/s', '', $generated);
        }

        $cleanContent = trim($generated);

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
}

<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ArticleCuratorService
{
    /**
     * Kurasi artikel listicle otomatis dari tempat-tempat di database
     */
    public function curateListicle(int $cityId, ?int $categoryId = null, int $limit = 5, ?string $customAngle = null): Article
    {
        $city = City::findOrFail($cityId);
        $category = $categoryId ? Category::find($categoryId) : null;

        // Ambil tempat-tempat terbaik berdasarkan rating & ulasan di kota tsb
        $query = Post::published()
            ->where('city_id', $city->id);

        if ($category) {
            $query->where('category_id', $category->id);
        } else {
            // Hindari lowongan kerja jika kategori tidak dispesifikasikan (fokus ke wisata/kuliner/tempat)
            $query->whereHas('category', function ($q) {
                $q->where('slug', 'not like', '%loker%')
                  ->where('slug', 'not like', '%lowongan%');
            });
        }

        $places = $query->orderByDesc('rating')
            ->orderByDesc('reviews_count')
            ->with(['category', 'images', 'details'])
            ->take($limit)
            ->get();

        if ($places->isEmpty()) {
            throw new RuntimeException("Tidak ada data tempat di {$city->name} yang memenuhi kriteria.");
        }

        // Siapkan ringkasan data tempat untuk prompt AI
        $placesContext = [];
        foreach ($places as $index => $p) {
            $catName = $p->category?->name ?? 'Tempat Menarik';
            $num = $index + 1;
            $addr = $p->formatted_address ?: $p->address;
            $rating = $p->rating ? "Rating: {$p->rating} ({$p->reviews_count} ulasan)" : '';
            $desc = Str::limit(strip_tags($p->content), 250);
            $placesContext[] = "{$num}. Nama: \"{$p->title}\" ({$catName})\n   Alamat: {$addr}\n   {$rating}\n   Deskripsi Singkat: {$desc}\n   URL Detail: {$p->url()}\n   Foto: {$p->primary_image_url}";
        }

        $topicName = $category ? $category->name : 'Tempat Menarik & Populer';
        $prompt = "Buatkan artikel blog editorial listicle bergaya majalah gaya hidup/perjalanan yang sangat menarik dan mendalam untuk website \"DISEKITAR\".\n" .
            "Topik: Rekomendasi {$limit} {$topicName} Terbaik di {$city->name}.\n" .
            ($customAngle ? "Sudut Pandang / Angle Khusus: {$customAngle}\n" : '') .
            "Berikut adalah daftar {$places->count()} tempat yang WAJIB diulas dalam artikel:\n" .
            implode("\n\n", $placesContext) . "\n\n" .
            "Instruksi Penulisan:\n" .
            "1. Buat Judul Artikel yang menarik, ramah SEO, dan klikabel (misal: '5 Rekomendasi Tempat Wisata Terbaik di Bandung untuk Liburan Akhir Pekan').\n" .
            "2. Paragraf Pembuka: Sambutan hangat dan narasi pengantar mengenai pesona kota {$city->name} terkait topik ini.\n" .
            "3. Bagian Isi: Ulas satu per satu tempat secara berurutan menggunakan subjudul <h2>{Nomor}. {Nama Tempat}</h2>. Ceritakan keistimewaan tempat tersebut, daya tarik utamanya, suasana, dan tips berkunjung.\n" .
            "   PENTING: Di setiap ulasan tempat, sertakan link rujukan ke URL detailnya dengan tag <a href=\"URL_DETAIL\" class=\"text-amber-700 dark:text-amber-400 font-semibold underline\" target=\"_blank\">Lihat detail & lokasi {Nama Tempat} di DISEKITAR &rarr;</a>.\n" .
            "4. Paragraf Penutup: Tips praktis berkunjung (waktu terbaik, transportasi) dan kesimpulan penutup.\n" .
            "5. Format output: Gunakan tag HTML bersih (<h2>, <h3>, <p>, <ul>, <li>, <strong>, <a>). JANGAN gunakan blok markdown (tanpa ```html).\n" .
            "6. Di akhir teks, sertakan metadata dalam format tag <meta_json>...</meta_json> berisi:\n" .
            "   {\n" .
            "     \"title\": \"Judul Artikel Pilihan\",\n" .
            "     \"excerpt\": \"Ringkasan 2-3 kalimat menarik untuk deskripsi artikel\"\n" .
            "   }";

        $apiKey = config('openai.api_key');
        if (!$apiKey) {
            throw new RuntimeException("OpenAI API Key belum disetel.");
        }

        $baseUri = config('openai.base_uri') ?: 'https://api.openai.com/v1';
        $endpoint = rtrim($baseUri, '/') . '/chat/completions';
        $model = config('openai.model') ?: 'gpt-4o-mini';

        $response = Http::timeout(120)->withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Kamu adalah editor travel & lifestyle senior terkemuka di Indonesia. Kamu menulis artikel yang hidup, berjiwa lokal, informatif, dan terstruktur rapi dalam format HTML.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.7,
        ]);

        if (!$response->successful()) {
            throw new RuntimeException("Gagal menghubungi AI API: " . $response->body());
        }

        $rawText = $response->json('choices.0.message.content') ?? '';

        // Ekstrak Meta JSON
        $title = "{$limit} Rekomendasi {$topicName} di {$city->name}";
        $excerpt = "Panduan kurasi editorial {$limit} {$topicName} paling populer dan terbaik di {$city->name}.";

        if (preg_match('/<meta_json>(.*?)<\/meta_json>/is', $rawText, $m)) {
            $metaData = json_decode(trim($m[1]), true);
            if (!empty($metaData['title'])) {
                $title = trim($metaData['title']);
            }
            if (!empty($metaData['excerpt'])) {
                $excerpt = trim($metaData['excerpt']);
            }
            $cleanContent = trim(str_replace($m[0], '', $rawText));
        } else {
            $cleanContent = trim($rawText);
        }

        // Bersihkan pembungkus markdown ```html
        $cleanContent = preg_replace('/^```(?:html)?\s*/i', '', $cleanContent);
        $cleanContent = preg_replace('/\s*```$/', '', $cleanContent);

        // Pilih featured image dari foto tempat pertama yang tersedia
        $featuredImage = $places->first()?->primary_image_url ?: null;

        // Simpan sebagai draft atau langsung published
        $article = Article::create([
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(4),
            'excerpt' => $excerpt,
            'content' => $cleanContent,
            'featured_image_url' => $featuredImage,
            'city_id' => $city->id,
            'category_id' => $category?->id,
            'status' => 'published',
            'published_at' => now(),
            'meta_title' => $title,
            'meta_description' => $excerpt,
        ]);

        return $article;
    }
}

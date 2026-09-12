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

        // Siapkan ringkasan data tempat lengkap untuk prompt AI
        $placesContext = [];
        foreach ($places as $index => $p) {
            $catName = $p->category?->name ?? 'Tempat Populer';
            $num = $index + 1;
            $addr = $p->formatted_address ?: $p->address ?: "{$city->name}";
            $rating = $p->rating ? "Rating Google: {$p->rating} ({$p->reviews_count} ulasan)" : '';
            $desc = Str::limit(strip_tags($p->content), 350);
            $imgUrl = $p->primary_image_url ?: '';
            
            // Ambil info jam buka & harga jika ada
            $jamBuka = $p->details?->first(fn($d) => in_array(strtolower(trim($d->meta_key)), ['jam_buka', 'jam_operasional', 'opening_hours']))?->meta_value;
            $harga = $p->details?->first(fn($d) => in_array(strtolower(trim($d->meta_key)), ['harga', 'rentang_harga', 'tiket_masuk', 'price']))?->meta_value;

            $metaParts = [];
            if ($jamBuka) $metaParts[] = "Jam Buka: {$jamBuka}";
            if ($harga) $metaParts[] = "Kisaran Harga: {$harga}";
            $metaStr = !empty($metaParts) ? ("   Info: " . implode(" | ", $metaParts) . "\n") : '';

            $placesContext[] = "{$num}. Nama Tempat: \"{$p->title}\" ({$catName})\n" .
                "   Alamat: {$addr}\n" .
                ($rating ? "   {$rating}\n" : '') .
                $metaStr .
                "   Ringkasan Tempat: {$desc}\n" .
                "   URL Halaman: {$p->url()}\n" .
                "   Foto Utama: {$imgUrl}";
        }

        $topicName = $category ? $category->name : 'Tempat Menarik & Populer';
        $currentYear = date('Y');

        $prompt = "Buatkan artikel blog editorial listicle mendalam, otoritatif, dan ramah SEO untuk portal informasi perkotaan 'DISEKITAR' (disekitar.com).\n\n" .
            "TOPIK UTAMA: Rekomendasi {$limit} {$topicName} Terbaik & Paling Populer di {$city->name} ({$currentYear}).\n" .
            ($customAngle ? "SUDUT PANDANG / ANGLE KHUSUS: {$customAngle}\n" : '') .
            "DAFTAR {$places->count()} TEMPAT YANG DIBAHAS:\n" .
            implode("\n\n", $placesContext) . "\n\n" .
            "PANDUAN STRUKTUR & STANDAR SEO:\n" .
            "1. JANGAN mencantumkan judul utama (<h1> atau <h2> judul artikel) di awal isi konten HTML karena sistem website sudah otomatis menampilkannya sebagai header artikel.\n" .
            "2. PARAGRAF PEMBUKA: Langsung mulai dengan 2-3 paragraf pembuka yang mengalir, memperkenalkan pesona {$topicName} di {$city->name}, tren terkini, dan mengapa kurasi ini penting untuk pembaca.\n" .
            "3. BAGIAN ULASAN TEMPAT: Untuk setiap tempat, gunakan format hierarki berikut secara konsisten:\n" .
            "   - Gunakan heading: <h2>{Nomor}. {Nama Tempat}</h2>\n" .
            "   - Jika URL 'Foto Utama' tempat tersedia (bukan kosong), sematkan foto dengan tag HTML:\n" .
            "     <figure class=\"my-4 rounded-xl overflow-hidden shadow-xs border border-zinc-200 dark:border-zinc-800\">\n" .
            "       <img src=\"{URL_FOTO_UTAMA}\" alt=\"{Nama Tempat} di {$city->name}\" class=\"w-full max-h-[420px] object-cover rounded-xl\" loading=\"lazy\">\n" .
            "       <figcaption class=\"text-xs text-zinc-500 dark:text-zinc-400 mt-1.5 text-center italic\">Suasana {Nama Tempat}</figcaption>\n" .
            "     </figure>\n" .
            "   - Tulis 2 hingga 3 paragraf ulasan mendalam: keunikan tempat, daya tarik utama, suasana/ambience, menu/fasilitas unggulan, dan alasan mengapa wajib dikunjungi.\n" .
            "   - Buat box ringkasan informasi praktis dengan list:\n" .
            "     <ul class=\"bg-zinc-50 dark:bg-zinc-800/60 p-4 rounded-xl text-sm border border-zinc-200/70 dark:border-zinc-700/60 space-y-1.5 my-3\">\n" .
            "       <li><strong>Alamat:</strong> {Alamat lengkap}</li>\n" .
            "       <li><strong>Rating & Ulasan:</strong> {Rating Google ulasan}</li>\n" .
            "       <li><strong>Rekomendasi / Daya Tarik:</strong> {Tips/keunggulan utama}</li>\n" .
            "     </ul>\n" .
            "   - Tautkan internal link rujukan ke profil lengkap tempat di DISEKITAR:\n" .
            "     <p><a href=\"{URL_Halaman}\" class=\"inline-flex items-center gap-1.5 text-amber-800 dark:text-amber-400 font-semibold hover:underline\" target=\"_blank\">Lihat detail & lokasi lengkap {Nama Tempat} di DISEKITAR &rarr;</a></p>\n" .
            "4. TIPS PRAKTIS BERKUNJUNG: Berikan subjudul <h2>Tips Berkunjung ke {$city->name}</h2> berisi tips bermanfaat (waktu terbaik berkunjung, rute/transportasi, etika lokal, budget).\n" .
            "5. PARAGRAF PENUTUP / KESIMPULAN: Ringkasan penutup yang ramah dan mengajak pembaca menjelajahi kota.\n" .
            "6. FORMAT OUTPUT: Gunakan tag HTML bersih (<h2>, <h3>, <p>, <ul>, <li>, <strong>, <em>, <a>, <figure>, <img>, <figcaption>). DILARANG menggunakan markdown codeblock (tanpa ```html).\n" .
            "7. Di bagian paling akhir, sertakan metadata JSON di dalam tag <meta_json>...</meta_json>:\n" .
            "   {\n" .
            "     \"title\": \"Judul SEO yang sangat menarik, natural, dan clickable maksimal 65 karakter\",\n" .
            "     \"excerpt\": \"Deskripsi meta ramah mesin pencari berkisar 140-160 karakter yang merangkum artikel secara memikat\"\n" .
            "   }";

        $apiKey = config('openai.api_key');
        if (!$apiKey) {
            throw new RuntimeException("OpenAI API Key belum disetel.");
        }

        $baseUri = config('openai.base_uri') ?: 'https://api.openai.com/v1';
        if (!str_starts_with($baseUri, 'http://') && !str_starts_with($baseUri, 'https://')) {
            $baseUri = 'https://' . $baseUri;
        }
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
                    'content' => 'Kamu adalah editor lifestyle dan pakar SEO travel terkemuka di Indonesia. Kamu menulis artikel panduan lokal yang hidup, kaya visual, kaya data, dan diformat dalam HTML bersih berstandar tinggi.',
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
        $title = "{$limit} Rekomendasi {$topicName} Terbaik di {$city->name} ({$currentYear})";
        $excerpt = "Panduan kurasi editorial {$limit} {$topicName} paling populer dan terbaik di {$city->name}. Temukan daya tarik, lokasi, dan tips berkunjung lengkapnya di sini.";

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

        // Jika AI masih mengawali konten dengan tag <h1> atau <h2> judul artikel yang sama persis, bersihkan agar tidak dobel
        $cleanContent = preg_replace('/^\s*<h[12][^>]*>.*?<\/h[12]>\s*/i', '', $cleanContent, 1);

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

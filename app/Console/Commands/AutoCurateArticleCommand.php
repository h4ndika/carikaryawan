<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('articles:auto-curate 
                            {--city-id= : ID kota spesifik (opsional)}
                            {--category-id= : ID kategori spesifik (opsional)}
                            {--limit=5 : Jumlah rekomendasi tempat dalam artikel}
                            {--count=1 : Jumlah artikel yang ingin digenerate dalam satu kali run}
                            {--sync : Jalankan secara sinkron tanpa queue (untuk testing)}')]
#[Description('Otomatis kurasi artikel blog listicle dari database berdasarkan rotasi kota & topik populer')]
class AutoCurateArticleCommand extends Command
{
    /**
     * Sudut pandang / angle tematik yang dirotasi secara dinamis
     */
    protected array $angles = [
        'Rekomendasi terbaik dengan rating tertinggi yang wajib dicoba',
        'Spot favorit warga lokal dan wisatawan untuk bersantai',
        'Tempat aesthetic dan instagramable yang nyaman buat nongkrong santai',
        'Pilihan populer yang cocok untuk kumpul keluarga atau teman',
        'Spot tersembunyi (hidden gem) dan tempat legendaris paling dicari',
    ];

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\ArticleCuratorService $curator)
    {
        $enabled = (bool) \App\Models\Setting::get('blog_auto_curate_enabled', true);
        $forcedCityId = $this->option('city-id');
        $batchCount = max(1, (int) $this->option('count'));

        if (!$enabled && !$forcedCityId) {
            $this->warn('Otomasi kurasi artikel blog sedang dinonaktifkan di pengaturan.');
            return self::SUCCESS;
        }

        for ($iteration = 1; $iteration <= $batchCount; $iteration++) {
            if ($batchCount > 1) {
                $this->line("=== Menjalankan Batch Artikel {$iteration} dari {$batchCount} ===");
            }
            $this->processSingleArticle($curator);
        }

        return self::SUCCESS;
    }

    protected function processSingleArticle(\App\Services\ArticleCuratorService $curator): int
    {
        $forcedCityId = $this->option('city-id');
        $limit = (int) ($this->option('limit') ?: 5);
        $sync = (bool) $this->option('sync');

        // 1. Tentukan Kota
        $cityId = $forcedCityId ? (int) $forcedCityId : null;
        if (!$cityId) {
            // Cari kota yang memiliki minimal $limit tempat published (non-loker)
            // Prioritaskan kota yang paling jarang / belum pernah dibuat artikelnya baru-baru ini
            $city = \App\Models\City::query()
                ->whereHas('posts', function ($q) {
                    $q->published()->whereHas('category', fn ($c) => $c->where('slug', 'not like', '%loker%'));
                }, '>=', $limit)
                ->withCount(['posts' => function ($q) {
                    $q->published()->whereHas('category', fn ($c) => $c->where('slug', 'not like', '%loker%'));
                }])
                // Urutkan berdasarkan artikel yang paling sedikit dibuat di kota tsb
                ->withCount('articles')
                ->orderBy('articles_count', 'asc')
                ->inRandomOrder()
                ->first();

            if (!$city) {
                $this->error("Tidak ada kota dengan minimal {$limit} tempat yang memenuhi syarat.");
                return self::FAILURE;
            }

            $cityId = $city->id;
            $cityName = $city->name;
        } else {
            $cityName = \App\Models\City::find($cityId)?->name ?? "City #{$cityId}";
        }

        // 2. Tentukan Kategori / Topik
        $categoryId = $this->option('category-id') ? (int) $this->option('category-id') : null;
        if (!$categoryId) {
            // Cari kategori di kota ini yang punya minimal 3 tempat published
            $availableCategory = \App\Models\Category::query()
                ->where('slug', 'not like', '%loker%')
                ->where('slug', 'not like', '%lowongan%')
                ->whereHas('posts', function ($q) use ($cityId) {
                    $q->published()->where('city_id', $cityId);
                }, '>=', min($limit, 3))
                ->inRandomOrder()
                ->first();

            if ($availableCategory) {
                $categoryId = $availableCategory->id;
                $categoryName = $availableCategory->name;
            } else {
                $categoryName = 'Semua Tempat Populer';
            }
        } else {
            $categoryName = \App\Models\Category::find($categoryId)?->name ?? "Category #{$categoryId}";
        }

        // 3. Pilih Random Angle
        $angle = $this->angles[array_rand($this->angles)];

        $this->info("Menyiapkan Kurasi Artikel Otomatis:");
        $this->line("- Kota: <comment>{$cityName}</comment> (ID: {$cityId})");
        $this->line("- Topik: <comment>{$categoryName}</comment>");
        $this->line("- Limit: <comment>{$limit} Tempat</comment>");
        $this->line("- Angle: <comment>{$angle}</comment>");

        // 4. Eksekusi Sync atau Dispatch ke Queue
        if ($sync) {
            $this->info("Memproses artikel langsung (sync)...");
            try {
                $article = $curator->curateListicle(
                    cityId: $cityId,
                    categoryId: $categoryId,
                    limit: $limit,
                    customAngle: $angle
                );
                $this->info("Berhasil membuat artikel: [ID: {$article->id}] {$article->title}");
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error("Gagal kurasi artikel: " . $e->getMessage());
                return self::FAILURE;
            }
        } else {
            $curationJob = \App\Models\ArticleCurationJob::create([
                'city_id' => $cityId,
                'category_id' => $categoryId,
                'limit' => $limit,
                'custom_angle' => $angle,
                'status' => 'pending',
            ]);

            \App\Jobs\CurateArticleJob::dispatch(
                cityId: $cityId,
                categoryId: $categoryId,
                limit: $limit,
                customAngle: $angle,
                curationJobId: $curationJob->id
            );

            $this->info("Job kurasi artikel berhasil dikirim ke antrean latar belakang (Queue: 'ai', Tracking ID: {$curationJob->id}).");
            return self::SUCCESS;
        }
    }
}

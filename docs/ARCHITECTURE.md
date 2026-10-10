# Arsitektur

Dokumen ini menjelaskan **kondisi kode saat ini**, bukan target ideal. Jika kode berubah, perbarui dokumen ini di PR yang sama.

## Stack

| Lapisan | Teknologi |
|---|---|
| Framework | Laravel 13, PHP 8.3 |
| Frontend publik | Livewire (`app/Livewire`) + Blade + Vite/Tailwind |
| Admin | Filament 5, panel `rahasia` → `/rahasia` (`app/Filament/Rahasia`) |
| Database | MySQL/MariaDB (prod), SQLite in-memory (test) |
| Queue / Cache | Queue `database` dengan 4 antrean: `ai`, `photos`, `maps`, `jobs` (+`default`); cache `redis` di VPS (lihat `deploy/setup-vps.sh`) |
| AI | `openai-php/laravel` (konten, tools karier), `laravel/ai` (`app/Ai/DisekitarAgent.php` untuk AI Playground) |
| Scraping | Apify (Google Maps, JobStreet, Glints, foto) via token di tabel `apify_accounts` |
| Sosial | Threads API (`app/Services/ThreadsService.php`) |

## Struktur folder

```text
app/
├── Ai/                 Agent + tool DB untuk AI Playground admin
├── Console/Commands/   Pipeline otomatis (harvest, publish, threads, backfill)
├── Filament/Rahasia/   Admin: Resources (CRUD) + Pages (settings, analytics)
├── Helpers/            AddressParser
├── Http/Controllers/   Endpoint non-Livewire (sitemap, widget, job image, tools AI, CSR API)
├── Http/Middleware/    CheckMaintenanceMode, TrackVisitor, EnsureCsrApiEnabled
├── Jobs/               Queue job (AI batch, scrape Maps/loker, foto, kurasi artikel)
├── Livewire/           Semua halaman publik
├── Models/             Eloquent
└── Services/           Logika bisnis & integrasi eksternal
tools/                  3 tool Node.js standalone (bukan bagian dari runtime Laravel)
deploy/setup-vps.sh     Provisioning VPS Ubuntu 24.04
```

Pola yang dipakai: **Livewire/Controller/Command → Service → Model**. Tidak ada layer Action/Repository/FormRequest. Jangan menambahkannya kecuali ada kebutuhan nyata.

## Pipeline konten (jantung project)

Semua berjalan lewat scheduler di `routes/console.php`. Jam scheduler dalam **UTC**; komentar di file menyebut WIB.

```text
┌─ maps:auto-harvest (02:00 WIB) ── Apify Google Maps ──┐
├─ jobs:auto-harvest (05:00 WIB) ── JobStreet ⇄ Glints ─┼─► posts (status draft, stok)
└─ Filament "Ekstraksi" (manual/batch) ─────────────────┘
                     │
   maps:drip-publish (tiap jam, 10 post) / ai:process-batch (tiap menit)
                     │  PostAiContentService → OpenAI
                     ▼
             posts (published) ──► PhotoScrapeQueue (antrean `photos`)
                     │
   threads:broadcast (6x/hari) + threads:monitor-replies (tiap 15 menit)

landmark:auto-harvest (2x/hari)    → landmarks
articles:auto-curate (2x/hari)     → articles (blog listicle)
photos:recover-stale (tiap menit)  → menandai job foto yatim sebagai gagal
```

Setiap otomasi punya saklar on/off di tabel `settings` (misal `maps_auto_harvest_enabled`, `auto_job_harvest_enabled`, `threads_auto_post_enabled`), diatur dari halaman Settings di Filament.

### Threads broadcast

`ThreadsService::publishPost/publishArticle` mengikuti `threads_chain_mode_enabled` (default **OFF**) dari menu **Otomasi Threads**. OFF tetap 1 post utama + 1 balasan link. ON memakai `threads_chain_posts` (default **4**, rentang **3–6**, termasuk post utama dan post link): satu panggilan `DisekitarAgent` (`openai-compatible`, `Content-blog`) menghasilkan seluruh narasi, lalu setiap balasan menunjuk ID post sebelumnya. Gambar dan topic tag tetap pada post utama.

Output AI wajib JSON array dengan jumlah tepat, teks tidak kosong, tanpa nomor/URL, dan maksimal 480 karakter. Ruang untuk link terakhir dihitung sebelum AI dipanggil; teks tidak dipotong untuk menyisipkan link. Output tidak valid/error AI kembali ke format 2 post. `posted_to_threads_at` diisi setelah post utama berhasil; kegagalan balasan dicatat di log dan, bila gagal sebelum post terakhir, dicoba balasan link ke ID terakhir yang berhasil. Tidak ada penyimpanan progres/resume atau retry post link terakhir (timeout bisa berarti sudah terbit). Link fallback juga bisa gagal. Jadwal dan monitor balasan tidak berubah; tombol tes admin mengirim postingan nyata sesuai mode yang disimpan.

### Antrean foto

`PhotoScrapeQueue::enqueue` adalah satu-satunya pintu masuk. Job di-dispatch ke queue **`photos`** dengan batch maksimal 3 baris, lalu di-release setiap 15 detik selama polling Apify (worker tidak tidur). Run punya deadline 600 detik. Aturan wajib:
- Queue harus async (bukan `sync`) dan cache lock harus dibagi semua worker.
- `retry_after` > 80 detik (default 90).
- Worker khusus `photos` harus berjalan (lihat [DEPLOYMENT](DEPLOYMENT.md#worker-queue)).
- `APP_KEY` harus stabil karena token Apify disimpan terenkripsi di baris job.

## Konfigurasi runtime: tabel `settings`

`App\Models\Setting::get/set` adalah key-value yang di-cache selamanya per key dan di-invalidate saat `set()`. Isinya: iklan (AdSense/Adsterra per slot), analytics (GA/GTM/Umami), maintenance mode, CSR API, Threads token, afiliasi loker, dan saklar otomasi. **Jangan menulis langsung ke tabel `settings` lewat SQL**, karena cache tidak akan ter-invalidate.

## Middleware web

1. `CheckMaintenanceMode`: mengembalikan 503 di URL yang sama jika `maintenance_mode_enabled`. Admin yang login, IP whitelist, `/rahasia`, dan `/api/*` selalu lolos.
2. `TrackVisitor`: mencatat `page_views` dan menambah `posts.views_count`. Bot, admin, dan aset tidak dihitung.

## Admin (Filament `/rahasia`)

Resources: Posts, Categories, Cities, Provinces, Landmarks, Articles, GoogleMapsScrapeJobs (ekstraksi), JobHarvests, AutoHarvestQueues, AiGenerationJobs, ArticleCurationJobs, PhotoScrapeJobs, ApifyAccounts.
Pages: AiPlayground, AiReport, AnalyticsDashboard, ToolAnalyticsDashboard, Manage{Ad,Analytics,JobAffiliate,Maintenance,Threads}Settings.

# Arsitektur

Dokumen ini menjelaskan **kondisi kode saat ini**, bukan target ideal. Jika kode berubah, perbarui dokumen ini di PR yang sama.

## Stack

| Lapisan | Teknologi |
|---|---|
| Framework | Laravel 13, PHP 8.3 |
| Frontend publik | Livewire (`app/Livewire`) + Blade + Vite/Tailwind |
| Admin | Filament 5, panel `rahasia` → `/rahasia` (`app/Filament/Rahasia`) |
| Database | MySQL/MariaDB (prod), SQLite in-memory (test) |
| Queue / Cache | Queue `database` dengan antrean `ai`, `default`, `jobs`; cache `redis` di VPS (lihat `deploy/setup-vps.sh`) |
| AI | `openai-php/laravel` (konten loker, tools karier), `laravel/ai` (`app/Ai/DisekitarAgent.php` untuk AI Playground dan Threads) |
| Scraping | Apify (JobStreet, Glints) via token di tabel `apify_accounts` |
| Sosial | Threads API (`app/Services/ThreadsService.php`) |

## Ikon produk

Ikon CariKaryawan berupa huruf **C / kaca pembesar dengan siluet orang**, berwarna zinc gelap (`#18181b`), putih (`#fafaf9`), dan amber (`#fbbf24`). Sumber vektor: `public/favicon.svg`. PNG tersedia dalam ukuran 16, 32, 48, 180, 192, dan 512 piksel; `favicon.ico` memuat ukuran 16/32/48. Layout publik, favicon admin, dan `site.webmanifest` memakai versi URL `ck1` agar cache logo DISEKITAR tidak digunakan kembali.

Untuk membuat ulang aset raster: `python -I tools/generate-icons.py` (membutuhkan Pillow di mesin pengembang, bukan di server). Geometri generator harus disesuaikan bersama SVG jika desain diubah; generator juga memeriksa dimensi PNG dan ukuran ICO.

## Struktur folder

```text
app/
├── Ai/                 Agent + tool DB untuk AI Playground admin
├── Console/Commands/   Pipeline otomatis (harvest loker, AI batch, threads, backfill)
├── Filament/Rahasia/   Admin: Resources (CRUD) + Pages (settings, analytics, AI)
├── Http/Controllers/   Endpoint non-Livewire (sitemap, job image, tools AI, CSR API)
├── Http/Middleware/    CheckMaintenanceMode, TrackVisitor, EnsureCsrApiEnabled
├── Jobs/               Queue job (AI batch, ekstraksi loker)
├── Livewire/           Halaman publik
├── Models/             Eloquent
└── Services/           Logika bisnis & integrasi eksternal
tools/job-extractor     Tool Node.js standalone (bukan bagian dari runtime Laravel)
deploy/setup-vps.sh     Provisioning VPS Ubuntu 24.04
```

Pola yang dipakai: **Livewire/Controller/Command → Service → Model**. Tidak ada layer Action/Repository/FormRequest. Jangan menambahkannya kecuali ada kebutuhan nyata.

> Nama legacy yang sengaja dipertahankan: `GoogleMapsImportService`, `GoogleMapsScrapeJob`, `RunGoogleMapsScrapeJob`, `DisekitarAgent`. Semuanya kini hanya melayani **ekstraksi loker** (JobStreet/Glints). Jalur Google Maps/tempat sudah tidak ada. Mengganti nama kelas tidak diperlukan dan hanya menambah risiko.

## Pipeline loker (jantung project)

Semua berjalan lewat scheduler di `routes/console.php`. Jam scheduler dalam **UTC**; komentar di file menyebut WIB.

```text
┌─ jobs:auto-harvest (05:00 WIB) ── JobStreet ⇄ Glints ─┐
└─ Filament "Ekstraksi" (manual / batch) ───────────────┼─► posts (loker; stok/draft)
                                                        │
        ApifyScraperService::startJobCrawler            │
        GoogleMapsImportService::importFromRecords      │  (dedup via Post::jobPlaceId)
                                                        ▼
              ai_generation_jobs ── ai:process-batch (tiap menit)
                         │  PostAiContentService → OpenAI
                         ▼
                 posts (published)
                         │
   threads:broadcast (4 slot loker/hari) + threads:monitor-replies (tiap 15 menit)
```

### Dua jalur masuk

- **Otomatis**: `jobs:auto-harvest --engine=auto` merotasi kata kunci dan kota prioritas, bergantian antara Glints dan JobStreet. Tiap otomasi punya saklar di tabel `settings` (misal `auto_job_harvest_enabled`, `threads_auto_post_enabled`).
- **Manual**: menu Filament **Ekstraksi** (`GoogleMapsScrapeJobs`) dan **Harvest Loker** (`JobHarvests`) membuat baris `google_maps_scrape_jobs` dengan `engine` `jobstreet` atau `glints`, lalu mendispatch `RunGoogleMapsScrapeJob` ke queue `jobs`. Tersedia ekstraksi batch multi-keyword × multi-kota/provinsi.

Kedua jalur memakai pool token `apify_accounts` (dirotasi; fallback `APIFY_TOKEN`).

### Threads broadcast

Empat slot harian, **semuanya `--type=jobs`** (07:30, 10:30, 15:30, 18:30 WIB; definisi di `routes/console.php`). Tiap slot bisa dimatikan dari menu **Otomasi Threads**.

`ThreadsService::publishPost` mengikuti `threads_chain_mode_enabled` (default **OFF**). OFF tetap 1 post utama + 1 balasan link. ON memakai `threads_chain_posts` (default **4**, rentang **3-6**, termasuk post utama dan post link): satu panggilan `DisekitarAgent` (`openai-compatible`, `Content-blog`) menghasilkan seluruh narasi, lalu setiap balasan menunjuk ID post sebelumnya. Gambar dan topic tag tetap pada post utama.

Output AI wajib JSON array dengan jumlah tepat, teks tidak kosong, tanpa nomor/URL, dan maksimal 480 karakter. Ruang untuk link terakhir dihitung sebelum AI dipanggil; teks tidak dipotong untuk menyisipkan link. Output tidak valid/error AI kembali ke format 2 post. `posted_to_threads_at` diisi setelah post utama berhasil; kegagalan balasan dicatat di log dan, bila gagal sebelum post terakhir, dicoba balasan link ke ID terakhir yang berhasil. Tidak ada penyimpanan progres/resume atau retry post link terakhir (timeout bisa berarti sudah terbit). Link fallback juga bisa gagal. Tombol tes admin mengirim postingan nyata sesuai mode yang disimpan.

## Tools karier AI

Halaman `/tools` (hub), `/karir/cv-builder`, `/karir/interview-prep`, serta cover letter (modal di halaman loker). Endpoint `POST /job-tools/*` di-throttle 15-30 per menit; logika di `CvBuilderService`, `JobCvService`, `JobCoverLetterService`, `InterviewPrepService`, model dari env `OPENAI_MODEL_CV`. Pemakaian dicatat di `tool_usages`.

## API CSR (headless)

`routes/csr.php`, prefix `/api/csr`, dilindungi `EnsureCsrApiEnabled` (saklar `csr_api_enabled`). Hanya melayani loker `published`: `/jobs`, `/jobs/{id_or_slug}`, `/config`, `/sitemap.xml`. Script frontend statis ada di `public/csr/`. Panduan: [guides/csr-frontend.md](guides/csr-frontend.md).

## AI Playground & Report

`AiPlayground` dan `AiReport` (admin) memakai `DisekitarAgent` + `DisekitarDatabaseTool` (kueri baca-saja ke DB) untuk menanyakan data loker.

## Konfigurasi runtime: tabel `settings`

`App\Models\Setting::get/set` adalah key-value yang di-cache selamanya per key dan di-invalidate saat `set()`. Isinya: iklan (AdSense/Adsterra per slot), analytics (GA/GTM/Umami), maintenance mode, CSR API, Threads token, afiliasi loker, dan saklar otomasi. **Jangan menulis langsung ke tabel `settings` lewat SQL**, karena cache tidak akan ter-invalidate.

## Middleware web

1. `CheckMaintenanceMode`: mengembalikan 503 di URL yang sama jika `maintenance_mode_enabled`. Admin yang login, IP whitelist, `/rahasia`, dan `/api/*` selalu lolos.
2. `TrackVisitor`: mencatat `page_views` dan menambah `posts.views_count`. Bot, admin, dan aset tidak dihitung.

## Admin (Filament `/rahasia`)

Resources: Posts, Categories, Cities, Provinces, GoogleMapsScrapeJobs (label: Ekstraksi), JobHarvests, AiGenerationJobs, ApifyAccounts.
Pages: AiPlayground, AiReport, AnalyticsDashboard, ToolAnalyticsDashboard, Manage{Ad,Analytics,JobAffiliate,Maintenance,Threads}Settings.

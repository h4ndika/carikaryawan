# CariKaryawan — Milestone & Progress Log

> **Catatan:** proyek ini sebelumnya bernama **DISEKITAR** (direktori tempat, landmark, blog, loker). Seluruh entri di bawah entri 2026-10-12 adalah **riwayat DISEKITAR (legacy)** dan sengaja tidak ditulis ulang; fitur tempat/landmark/blog, `tools/google-map-extractor`, dan `tools/photo-scraper-admin` yang disebut di dalamnya sudah dihapus.

## Milestone: Rebrand CariKaryawan & Pemangkasan ke Lowongan + Tools Karier (12 Oktober 2026)

- **Cakupan baru:** hanya lowongan kerja dan tools karier AI (CV builder, cover letter, interview prep, tools hub). Tempat, landmark, dan blog dihapus dari produk.
- **Branding:** `carikaryawan.com`; `robots.txt` (sitemap), `site.webmanifest`, placeholder SVG, `routes/csr.php`, `config/ai.php`, dokumentasi, dan `tools/job-extractor` memakai nama CariKaryawan.
- **Pipeline loker dipertahankan:** `jobs:auto-harvest` (JobStreet/Glints) → `GoogleMapsImportService` → `ai:process-batch` → Threads. `GoogleMapsImportService`, `GoogleMapsScrapeJob`, `RunGoogleMapsScrapeJob` tetap ada secara internal hanya untuk ekstraksi loker. Worker: `ai,default` dan `jobs`.
- **Threads:** 4 slot harian, semuanya `--type=jobs` (bukan enam slot campuran).
- **Tools:** `tools/google-map-extractor` dan `tools/photo-scraper-admin` dihapus. `tools/job-extractor` kini mandiri: tidak lagi membaca token/SQLite dari `google-map-extractor`; token hanya dari `APIFY_TOKEN` di `.env` atau input dashboard (pemilihan akun Apify di dashboard dihapus). `LandmarkSeeder` dihapus.
- **Deploy:** `deploy/setup-vps.sh` memakai path `/var/www/carikaryawan`, worker `carikaryawan-worker-fast|jobs`, tanpa worker `photos`/`maps`, dan **mewajibkan env `DB_PASS`** (password hardcoded dihapus; script berhenti sebelum aksi apa pun bila kosong).
- **Database:** disiapkan migrasi purge destruktif (hapus post non-loker, drop tabel/kolom tempat/landmark/artikel). **Belum dijalankan** di database aplikasi; prosedur backup, staging, drain antrean, dan pemulihan dari backup ada di [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).
- **Perlu tindak lanjut:** logo/ikon biner lama (`favicon.ico`, PNG, `android-chrome-*`, `apple-touch-icon`) masih bermerek DISEKITAR dan perlu didesain ulang.

---


## Milestone: Pilar Lowongan Kerja, Deduplikasi Kanonikal, Batch Scraping Multi-Wilayah & Resilience Engine (6 - 10 September 2026)

### 1. Ekosistem Papan Lowongan Kerja (Job Board) & Multi-Engine Scraping (6 - 8 September 2026)
- **Integrasi Scraper JobStreet & Glints Indonesia:**
  - Menghapus provider scraping usang (Google Jobs & Instagram scraper) dan beralih penuh ke provider loker terpercaya di Indonesia: **JobStreet** (`shahidirfan/jobstreet-scraper`) dan **Glints** (`truefetch/glints-job-listing`).
  - Standalone micro-tool `job-extractor` (Express dashboard dengan SSE live streaming, rotasi token Apify otomatis dari database, dan location autocomplete).
  - Integrasi native ke Filament Admin (`GoogleMapsScrapeJob` / Menu Ekstraksi) dengan pemilihan engine (Google Maps, JobStreet, Glints), target limit, dan auto-kategori *Lowongan Kerja*.
- **Pencarian & Penanganan Logo Perusahaan Otomatis:**
  - Fallback logo perusahaan otomatis (`photo-scrape`) via Google Custom Search dan DuckDuckGo favicon service dengan validasi status URL gambar untuk mencegah link rusak.
  - Job post secara otomatis dilewati dari scraper foto tempat Google Maps agar tidak tertimpa gambar yang tidak relevan.

### 2. Canonical Deduplication Lintas Platform & Backfill (9 September 2026)
- **Fingerprint Canonical `place_id`:**
  - Mengatasi duplikasi saat lowongan yang sama ditemukan di JobStreet dan Glints.
  - Generasi ID kanonikal unik berbasis hash MD5 dari normalisasi entitas: `job_` + MD5(`normCompany_normPosition_normCity`).
  - Pembersihan otomatis nama badan usaha (`PT`, `CV`, `UD`, `Yayasan`) dan karakter non-alfanumerik untuk menjamin identitas unik cross-engine.
- **Command Backfill Database:**
  - Dibuat Artisan command `php artisan jobs:backfill-place-id` yang berhasil memperbarui seluruh record lowongan lama ke format kanonikal baru di database MySQL.

### 3. Kebijakan Kedaluwarsa Otomatis & SEO JobPosting (8 - 9 September 2026)
- **Sistem Auto-Expiration 21 Hari:**
  - Lowongan otomatis ditandai kedaluwarsa setelah 21 hari sejak tanggal tayang (`published_at`).
  - Tidak menghapus artikel agar tidak menimbulkan broken link / 404 pada search engine.
  - Indikator visual editorial: Badge *"Lowongan Ditutup / Telah Kedaluwarsa"* dan tombol lamar dinonaktifkan.
  - SEO Schema JSON-LD: Integrasi atribut `validThrough` yang presisi sesuai standar Google Jobs Search.

### 4. Redesain Khusus Kategori Lowongan Kerja (Text-First UI) (9 September 2026)
- **Tampilan Bersih Bebas Gambar:**
  - Menyembunyikan elemen foto/thumbnail khusus artikel berkategori *Lowongan Kerja* di Beranda (Lead Story & Side Stories), Feed Artikel Publik, Pilihan Redaksi, dan Halaman Detail Post (`post-show`).
  - Format penyajian berbasis teks profesional yang padat informasi, rapi, dan nyaman dibaca.
- **CTA Lamaran Terisolasi & Elegan:**
  - Penataan tombol "Lamar Sekarang" di antara dua divider halus di bawah rincian pekerjaan, mencegah redundansi teks tautan lamaran di bagian deskripsi.

### 5. Batch Scraping Multi-Keyword, Multi-Kota & Multi-Provinsi (9 - 10 September 2026)
- **Aksi Ekstraksi Batch Tingkat Lanjut:**
  - Tombol modal baru `Ekstraksi Batch (Multi Keyword & Multi Lokasi)` di Filament.
  - Input multi-keyword (satu per baris textarea).
  - Searchable multi-select Kota dan multi-select Provinsi.
  - Algoritma ekspansi wilayah: Otomatis mengekstrak seluruh kota dari provinsi yang dipilih (misal: memilih Bali & DI Yogyakarta langsung menarik seluruh kab/kota di kedua provinsi tersebut) dan menggabungkannya dengan kota pilihan spesifik tanpa duplikasi ID.
- **Normalisasi Otomatis Prefix Administratif Kota:**
  - Pembersihan otomatis awalan `KOTA`, `KABUPATEN`, `KAB.`, `KODYA` sebelum dikirim ke API crawler JobStreet/Glints (misal: `KOTA DENPASAR` -> `Denpasar`) sehingga query pencarian selalu dikenali oleh search engine loker.

### 6. Resilience, Logging & Antrean VPS Worker (9 - 10 September 2026)
- **Penanganan Zero Results Otomatis:**
  - Jika pencarian menghasilkan 0 data di JobStreet/Glints (actor melempar `No jobs were extracted`), sistem tidak lagi crash dengan status `FAILED`, melainkan otomatis diselesaikan dengan status `Selesai (0 data)` disertai keterangan informatif.
- **Ekstraksi Log Error Apify Rinci & Kolom Tabel Transparan:**
  - Penarikan cuplikan baris error console Apify (`getRunLogSnippet` & `statusMessage`) secara langsung.
  - Kolom keterangan & error selalu terlihat di tabel antrean dengan text-wrapping dan deskripsi status badge tanpa perlu membuka modal error satu per satu.
- **Rotasi Akun Acak pada Retry:**
  - Tombol aksi "Ulangi Ekstraksi" per baris memprioritaskan akun Apify aktif yang berbeda dari akun sebelumnya.
  - Bulk Action `Ulangi Ekstraksi Terpilih (Acak Akun)` untuk me-retry banyak baris sekaligus dengan pemilihan akun acak.
- **Protokol Daemon Queue VPS:**
  - Penambahan instruksi wajib `php artisan queue:restart` di file `AGENTS.md` setelah deploy untuk mencegah daemon worker mengeksekusi kode in-memory yang usang.

---

## Milestone Sebelumnya: Analytics Engine, AdSense Readiness & Scraping Pipeline (24 Agustus - 5 September 2026)

### 1. Internal Analytics & Visitor Tracking Dashboard (3 - 5 September 2026)
- **Engine Pelacakan Pengunjung Ringan:** Dibuat middleware `TrackVisitor` dan tabel `page_views` yang mencatat kunjungan web riil secara anonim (salted IP hash, eliminasi bot/crawler dan administrator terautentikasi).
- **Filament Analytics Dashboard (`/rahasia/analytics`):**
  - Ringkasan KPI: Total Pengunjung, Total Tayangan Halaman, Rata-rata Tayangan/Hari, Pengunjung Unik Hari Ini.
  - Filter rentang waktu dinamis: Hari Ini, Kemarin, 7 Hari Terakhir, 30 Hari Terakhir, Bulan Ini, dan Rentang Kustom.
  - Grafik Chart.js dinamis: Granularitas per jam (00:00 - 23:00) untuk filter 1 hari (Hari Ini / Kemarin) dan granularitas harian untuk rentang multi-hari.
  - Kartu Sebaran Perangkat: Proporsi Mobile, Desktop, dan Tablet dengan styling dark mode yang adaptif.
  - Tabel 10 Konten Terpopuler: Menampilkan judul artikel, kategori, kota, dan jumlah view dengan link langsung.
  - Arsitektur AlpineJS terisolasi mencegah error recursion proxy (*Maximum call stack size exceeded*) dan penanganan siklus hidup canvas canvas via `wire:ignore`.

### 2. Kepatuhan Kebijakan Google AdSense & Halaman Statis Editorial (4 September 2026)
- **5 Halaman Kebijakan & Editorial:**
  - `Kebijakan Privasi (/kebijakan-privasi)`: Dilengkapi klausul wajib kuki Google AdSense (DoubleClick/DART), tautan opt-out resmi (*Google Ads Settings* & *aboutads.info*), log server, dan kepatuhan UU PDP No. 27/2022.
  - `Tentang Kami (/tentang-kami)`: Profil redaksi, visi kurasi editorial independen, standar ulasan objektif, dan rincian kategori.
  - `Syarat & Ketentuan (/syarat-ketentuan)`: Hak kekayaan intelektual, batas kutipan (*fair use*), etika atribusi, dan larangan web scraping liar.
  - `Hubungi Kami (/kontak)`: Saluran surat elektronik resmi (`redaksi@disekitar.com` & `admin@disekitar.com`), panduan pengajuan tempat, dan estimasi waktu respons.
  - `Penafian (/disclaimer)`: Batas tanggung jawab keakuratan data operasional/tarif tempat, independensi editorial, dan mekanisme *takedown* hak cipta foto.
- **Dukungan Alias Internasional:** Redirect otomatis dari `/privacy-policy`, `/about`, `/contact`, dan `/terms` untuk memastikan kompatibilitas bot peninjau global AdSense.
- **Redesain Layout Footer:** Struktur 12-kolom seimbang (50% profil, 25% navigasi eksplorasi, 25% kebijakan redaksi) yang responsif dan anti-tumpang tindih.
- **Integrasi Peta Situs:** Pendaftaran otomatis kelima halaman kebijakan ke `sitemap.xml`.

### 3. Integrasi Monetisasi AdSense & Pengaturan Iklan (1 - 2 September 2026)
- Modul Filament `ManageAdSettings` untuk konfigurasi script klien AdSense dan slot iklan.
- Penempatan unit iklan responsif di tata letak publik (Hero Beranda, Feed Konten Atas/Bawah, Sidebar).
- Endpoint dinamis `/ads.txt` yang otomatis menyesuaikan `adsense_client_id`.

### 4. Performa Frontend & Optimasi Core Web Vitals (1 - 2 September 2026)
- Optimalisasi skor LCP (Largest Contentful Paint) dengan `fetchpriority="high"` dan preloading gambar hero.
- Non-blocking font stylesheet untuk font *Plus Jakarta Sans* dan *Fraunces*.
- Pemasangan branding favicon multi-resolusi (`favicon.ico`, `favicon.svg`, 16x16, 32x32, 180x180, dan `site.webmanifest`).
- Perbaikan aksesibilitas (aria-label pada pencarian, standardisasi FontAwesome).

### 5. Peningkatan Skala AI Engine & AI Playground (29 - 31 Agustus 2026)
- Integrasi resmi `laravel/ai` SDK dan pembuatan menu **AI Playground** di admin Filament (`/rahasia/ai-playground`).
- Scale throughput batch AI: Peningkatan konkurensi antrean dari 10 menjadi 25 proses batch simultan.
- Optimasi query database: Implementasi batch insert pada `post_details`, transaksi DB terisolasi, dan regex stripping yang efisien tanpa bottleneck.
- Penyesuaian driver HTTP OpenAI-compatible untuk gateway 9router (`stream=false`).

### 6. Arsitektur Photo Scraping Apify & Google Maps (27 - 31 Agustus 2026)
- Peningkatan scraper utama ke Apify actor berkinerja tinggi (`lurkapi/google-maps-photos-and-videos-scraper`).
- Batch synchronous scraping (10 tempat per eksekusi via `run-sync-get-dataset-items`).
- Eksekusi paralel dual-API (5+5) dengan rotasi multi-akun Apify dan penanganan otomatis kehabisan kuota (*quota failover*).
- Pencegahan blokir hotlink Google CDN (Error 429) dengan header `no-referrer` dan atribut `referrerpolicy="no-referrer"`.
- Sinkronisasi otomatis foto pertama ke kolom `thumbnail` di tabel `posts`.
- Tool standalone `photo-scraper-admin` (Express UI) dan modal preview foto interaktif di tabel artikel admin.

### 7. Deployment & Infrastruktur VPS (28 - 29 Agustus 2026)
- Skrip otomatisasi setup VPS Ubuntu 24.04 (`deploy/setup_vps.sh`) untuk PHP 8.4, Nginx, MariaDB, Composer, dan Node.js.
- Konfigurasi SSH deploy key untuk workflow sinkronisasi Git otomatis tanpa intervensi manual.
- Implementasi `FilamentUser` pada model `User` untuk otorisasi panel admin yang aman di lingkungan produksi.

---

## Milestone Sebelumnya: Editorial Platform & AI Automation Engine (21 - 23 Agustus 2026)

### 1. Google Maps Extractor & Multi-Format Importer (21 - 22 Agustus)
- Integrasi scraping Google Maps langsung dari panel admin Filament dengan rotasi multi-akun Apify.
- Importer data JSON/CSV dengan auto-categorization berbasis keyword heuristics.
- Penanganan relasi Province, City, Category, PostDetail, dan PostImage secara otomatis.

### 2. Batch AI Parallel Content Generation (22 - 23 Agustus)
- Generator konten otomatis menggunakan LLM dengan parallel execution (`Http::pool`) berkapasitas 10 artikel per batch.
- Pembersihan tag reasoning `<think>`, pembatasan minimum panjang kata, dan penanda status konten incomplete (<200 kata).
- Manajemen background worker menggunakan Laravel Queue standar (`ProcessBatchAiQueueJob`) tanpa OS sub-process blocking.
- Monitoring antrean AI di Filament admin (`AiQueueMonitor`).

### 3. Editorial UI/UX & Dark Mode (22 - 23 Agustus)
- Redesain antarmuka frontend dengan estetika jurnal kurasi independen (Lead Story, Curator's Notes, Article Feed, dan Editor's Picks).
- Implementasi sistem Dark Mode penuh (Light/Dark toggle switch, Anti-FOUC head script, dan persistensi `localStorage`).
- Image Resilience: Cascade fallback gambar dari Thumbnail -> Galeri -> SVG Placeholder.
- Auto-hide foto rusak pada galeri & auto-hide section galeri jika kosong.
- Overlay badge sumber domain foto (*Foto: Google Maps, Unsplash*, dsb.) di semua kartu dan galeri.
- Halaman eksplorasi interaktif (`/cari`) dengan pagination editorial kustom.

---
**Next Milestone Targets (legacy DISEKITAR, tidak berlaku lagi):**
1. Persetujuan Akun Google AdSense & Monitoring Penayangan Iklan Live.
2. Otomasi Mirroring Gambar (penyimpanan lokal/S3 untuk backup foto Google Maps).
3. Peningkatan cakupan konten kurasi ke destinasi kota-kota sekunder.

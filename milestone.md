# DISEKITAR — Milestone & Progress Log

## Milestone: Analytics Engine, AdSense Readiness & Scraping Pipeline (24 Agustus - 5 September 2026)

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
**Next Milestone Targets:**
1. Persetujuan Akun Google AdSense & Monitoring Penayangan Iklan Live.
2. Otomasi Mirroring Gambar (penyimpanan lokal/S3 untuk backup foto Google Maps).
3. Peningkatan cakupan konten kurasi ke destinasi kota-kota sekunder.

# DISEKITAR — Task Board

Status: living doc. Update tiap milestone selesai.  
Terakhir Diperbarui: 10 September 2026

## Done (6 - 10 September 2026)

### 1. Ekosistem Papan Lowongan Kerja (Job Board) & Scraping Engine
- [x] Scraper JobStreet (`shahidirfan/jobstreet-scraper`) & Glints (`truefetch/glints-job-listing`) terintegrasi langsung di Filament Admin.
- [x] Standalone micro-tool `job-extractor` (Express dashboard dengan SSE streaming, auto-rotate token Apify, dan autocomplete).
- [x] Penghapusan dependensi usang (Google Jobs & Instagram Scraper).
- [x] Auto-company logo search via Google Search / DuckDuckGo fallback dengan verifikasi URL responsif.
- [x] Proteksi photo-scraper: Post berkategori Lowongan Kerja otomatis dilewati agar gambar tidak tertimpa foto tempat Google Maps.

### 2. Deduplikasi Lintas Platform & Backfill Database
- [x] Fingerprint Canonical `place_id`: Algoritma unik MD5(`normCompany_normPosition_normCity`) dengan pembersihan prefix badan hukum (`PT`, `CV`, `UD`, `Yayasan`) untuk mendeteksi kesamaan loker antar-platform (JobStreet vs Glints).
- [x] Artisan Command `php artisan jobs:backfill-place-id` untuk migrasi seluruh data lama ke sistem identitas kanonikal.

### 3. Kebijakan Kedaluwarsa Otomatis & SEO JobPosting
- [x] Auto-expiration 21 hari sejak tanggal publikasi (`published_at`).
- [x] Soft-closing editorial: Badge "Lowongan Ditutup / Telah Kedaluwarsa" dan tombol lamar disabled tanpa 404/penghapusan URL.
- [x] Schema JSON-LD `validThrough` yang valid untuk Google Jobs SEO.

### 4. Redesain Frontend Khusus Lowongan Kerja (Text-First UI)
- [x] Sembunyikan elemen foto/thumbnail untuk post Lowongan Kerja di Home (Lead Story & Side Stories), Feed Artikel, Pilihan Redaksi, dan Detail Post.
- [x] Re-posisi tombol CTA "Lamar Sekarang" di antara pembatas divider rapi dan eliminasi teks lamaran yang tumpang tindih.

### 5. Batch Scraping Multi-Keyword, Multi-Kota & Multi-Provinsi
- [x] Form modal Filament untuk eksekusi batch multi-keyword (textarea per baris).
- [x] Searchable multi-select Kota dan multi-select Provinsi dengan auto-merge seluruh kota/kabupaten turunan tanpa duplikasi ID.
- [x] Sanitasi otomatis prefix nama kota administratif (`KOTA`, `KABUPATEN`, `KAB.`, `KODYA`) sebelum dikirim ke API crawler.

### 6. Resilience, Error Logging & VPS Daemon Queue
- [x] Zero-result handler: Status Apify `No jobs were extracted` ditangani sebagai `Selesai (0 data)` secara anggun alih-alih crash `FAILED`.
- [x] Penarikan log error konsol Apify (`getRunLogSnippet` & `statusMessage`) langsung ke database dan kolom tabel Filament.
- [x] Kolom Keterangan / Error terbuka langsung di baris tabel dengan text-wrapping dan badge description.
- [x] Retry dengan pemilihan akun Apify acak dan Bulk Action `Ulangi Ekstraksi Terpilih (Acak Akun)`.
- [x] Penambahan protokol sinkronisasi daemon queue (`queue:restart`) di `AGENTS.md`.

---

## Done Sebelumnya (24 Agustus - 5 September 2026)

### 1. Internal Visitor Analytics Engine (Filament Admin)
- [x] Middleware `TrackVisitor` & tabel `page_views` (penghitungan pageview & visitor anonim berbasis salted IP hash).
- [x] Dashboard analitik Filament (`/rahasia/analytics`): KPI Summary, Devices Breakdown, Top 10 Viewed Articles.
- [x] Date range filter fleksibel (Hari Ini, Kemarin, 7 Hari, 30 Hari, Bulan Ini, Custom Date).
- [x] Chart.js interaktif dengan dukungan granularitas **per-jam** (00:00 - 23:00) untuk filter single-day ("Hari Ini" & "Kemarin") dan harian untuk multi-day.
- [x] Isolasi Alpine.js reactivity proxy untuk mencegah bug `RangeError: Maximum call stack size exceeded` & `wire:ignore` pada canvas chart.

### 2. Kepatuhan Kebijakan AdSense & Halaman Legal Editorial
- [x] Halaman Kebijakan Privasi (`/kebijakan-privasi`) dengan klausul kuki Google AdSense (DoubleClick/DART), link opt-out, dan UU PDP.
- [x] Halaman Tentang Kami (`/tentang-kami`) dengan visi kurasi, prinsip editorial, dan standar ulasan.
- [x] Halaman Syarat & Ketentuan Layanan (`/syarat-ketentuan`) terkait hak cipta konten dan etika sitasi.
- [x] Halaman Hubungi Kami (`/kontak`) dengan saluran email resmi dan panduan pengajuan rekomendasi tempat.
- [x] Halaman Penafian (`/disclaimer`) atas akurasi jam/harga tempat dan mekanisme perlindungan hak cipta gambar.
- [x] Redirect alias URL internasional (`/privacy-policy`, `/about`, `/contact`, `/terms`).
- [x] Redesain layout footer responsif 12-kolom (profil 6 col, eksplorasi 3 col, kebijakan 3 col).
- [x] Pendaftaran 5 halaman legal ke `sitemap.xml`.

### 3. Monetisasi Google AdSense & ads.txt
- [x] Menu Filament `ManageAdSettings` untuk konfigurasi script klien AdSense dan toggle slot iklan.
- [x] Penempatan slot iklan responsif (Hero Beranda, Feed Konten Atas/Bawah, Sidebar).
- [x] Route dinamis `/ads.txt` terintegrasi dengan konfigurasi `adsense_client_id`.

### 4. Performa Frontend & Core Web Vitals
- [x] Penambahan branding favicon multi-resolusi (`favicon.ico`, `favicon.svg`, 16x16, 32x32, 180x180, dan `site.webmanifest`).
- [x] Optimasi LCP hero image (`fetchpriority="high"` & preload).
- [x] Non-blocking stylesheets font (*Plus Jakarta Sans* & *Fraunces*).
- [x] Aksesibilitas: Aria-label pada tombol pencarian dan pemulihan stylesheet FontAwesome.

### 5. Peningkatan Skala AI Engine & AI Playground
- [x] Integrasi `laravel/ai` SDK resmi dan pembuatan menu **AI Playground** (`/rahasia/ai-playground`).
- [x] Peningkatan throughput paralel batch AI dari 10 ke 25 proses simultan.
- [x] Optimasi database: Batch insert `post_details`, transaksi DB terisolasi, dan regex post-AI tanpa bottleneck.
- [x] Driver OpenAI-compatible untuk gateway 9router dengan `stream=false`.

### 6. Arsitektur Photo Scraping & Discovery Lokasi
- [x] Migrasi photo scraper utama ke Apify actor berkinerja tinggi (`lurkapi/google-maps-photos-and-videos-scraper`).
- [x] Batch synchronous scraping (10 tempat per execution via `run-sync-get-dataset-items`).
- [x] Paralelisasi batch dual-API (5+5) dengan rotasi multi-akun Apify dan auto-failover saat kuota habis.
- [x] Pencegahan blokir hotlink Google CDN (Error 429) dengan header `no-referrer` dan atribut `referrerpolicy="no-referrer"`.
- [x] Auto-update thumbnail artikel dengan foto pertama hasil scraping.
- [x] Tool standalone `photo-scraper-admin` (Express UI) dan modal preview interaktif pada tabel artikel Filament.
- [x] Auto-scrape dan penemuan landmark baru multi-metode (AI, OpenStreetMap, Apify).

### 7. Deployment VPS & Otorisasi Produksi
- [x] Skrip otomasi provisioning VPS Ubuntu 24.04 (`deploy/setup_vps.sh`).
- [x] SSH deploy key setup untuk git pull otomatis di server produksi.
- [x] Implementasi kontrak `FilamentUser` pada model `User` untuk otorisasi panel admin di server produksi.

---

## Done Sebelumnya (21 - 23 Agustus 2026)

### 1. Fondasi & CMS (Filament Admin)
- [x] Fondasi Laravel + Filament admin (`/rahasia`)
- [x] Model: Post, Category, City, Province, PostDetail, PostImage, AiQueue
- [x] SEO fields: title/desc/robots/canonical/og + JSON-LD helpers
- [x] Import data: JSON / CSV scraper import dengan auto-categorization fallback
- [x] Google Maps Scraper / Extractor terintegrasi di Filament dengan rotasi multi-akun Apify
- [x] Bulk actions di Filament: Bulk Publish, Bulk Draft, Bulk Generate AI Konten
- [x] Filter & status check artikel pendek (<200 kata) / Incomplete content
- [x] Menu & monitoring Antrean AI (AI Queue Monitor)

### 2. AI Content Generation Engine
- [x] Eksekusi batch parallel AI generation via `Http::pool` (10 artikel sekaligus secara simultan)
- [x] Pembersihan output AI dari tag `<think>`, reasoning preamble, obrolan, dan validasi minimum konten
- [x] Pencegahan duplikasi job AI jika post sudah ada dalam antrean
- [x] Antrean background asinkron menggunakan standar Laravel Queue (`php artisan queue:work`)

### 3. Frontend & Desain Editorial (Livewire & Tailwind)
- [x] Public frontend Livewire: Home, PostShow, Search/Jelajah (`/cari`)
- [x] Redesain layout editorial modern: Masthead, Headline Lead Story, Catatan Kurator, Feed Artikel, Pilihan Editor
- [x] Custom Editorial Pagination dengan cursor pointer eksplisit
- [x] Image resilience & fallback cascade: Thumbnail -> Galeri alternatif -> Placeholder editorial
- [x] Auto-hide foto rusak pada galeri & auto-hide section galeri jika kosong
- [x] Overlay badge sumber domain foto (*Foto: Google Maps, Unsplash, Pexels*, dll.) di seluruh card dan galeri
- [x] Dark Mode: Toggle switch (Light/Dark), anti-FOUC script di `<head>`, state sync dengan `localStorage` dan navigasi Livewire SPA
- [x] Halaman eksplorasi & pencarian interaktif: filter kategori, kota, keyword, dan reset filter
- [x] SEO & Metadata: sitemap.xml, robots.txt, dynamic canonical, open graph, dan structured data Article

---

## Now / Next

- [ ] Persetujuan Google AdSense & pemantauan impresi iklan live
- [ ] Automatic Image Mirroring (Download foto eksternal ke local storage/S3)
- [ ] Centralized Job Monitoring dashboard
- [ ] Penambahan cakupan kurasi kota-kota baru
- [ ] Full-text search (MySQL FULLTEXT / Scout) jika volume data semakin membesar

---

## Notes

- Hosting VPS: Utamakan efisiensi resource & CDN/URL images.
- Publish gate: Hanya post dengan status `published` dan `published_at <= now` yang tampil di frontend.
- Workflow Git: Perbaikan kecil di `main`, fitur kompleks gunakan branch `feat/...` dan PR.

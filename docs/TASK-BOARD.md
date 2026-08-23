# DISEKITAR — Task Board

Status: living doc. Update tiap milestone selesai.
Terakhir Diperbarui: 23 Agustus 2026

## Done (21 - 23 Agustus 2026)

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

## Now / Next

- [ ] Automatic Image Mirroring (Download foto eksternal ke local storage/S3)
- [ ] Centralized Job Monitoring dashboard
- [ ] Subdomain per kategori (hotel.disekitar.com/…) — nanti
- [ ] Full-text search (MySQL FULLTEXT / Scout) jika volume data besar

## Notes

- Hosting shared/VPS: Utamakan efisiensi resource & CDN/URL images.
- Publish gate: Hanya post dengan status `published` dan `published_at <= now` yang tampil di frontend.
- Workflow Git: Perbaikan kecil di `main`, fitur kompleks gunakan branch `feat/...` dan PR.


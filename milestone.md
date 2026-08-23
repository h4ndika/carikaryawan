# DISEKITAR — Milestone & Progress Log

## Milestone: Editorial Platform & AI Automation Engine (21 - 23 Agustus 2026)

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
1. Automatic Image Mirroring (Local storage / S3 mirror untuk foto eksternal Google Maps).
2. Centralized Background Job Monitoring dashboard.

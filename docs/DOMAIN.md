# Domain & Aturan Bisnis

## Entitas

| Model | Keterangan |
|---|---|
| `Post` | Entitas inti: tempat (hotel, kuliner, wisata, fasilitas) **dan** lowongan kerja. Relasi `province`, `city`, `category`, `details` (key-value), `images`. Soft delete. |
| `Category`, `Province`, `City` | Taksonomi lokasi. `City.slug` berformat `bandung-kota` / `bogor-kabupaten`. |
| `Landmark` | Titik pusat (stasiun, kampus, mal) untuk halaman "di sekitar X". |
| `Article` | Blog listicle hasil kurasi AI (`/blog`). |
| `Setting` | Konfigurasi runtime key-value (lihat ARCHITECTURE). |
| `ApifyAccount` | Pool token Apify yang dirotasi. |
| `*Job`, `AutoHarvestQueue` | Status antrean (AI, scrape Maps/loker, foto, kurasi artikel). |
| `PageView`, `ToolUsage`, `ThreadsEngagement` | Analitik. |
| `AiChatSession`, `AiReport` | AI Playground admin. |

## Aturan penting (jangan dilanggar)

### Status post
- Hanya `status = published` yang tampil publik (`Post::scopePublished`).
- `published_at` diisi otomatis saat status menjadi `published` (hook di `Post::boot`).
- Post hasil scrape masuk sebagai `draft` (stok), lalu dipublikasikan oleh drip-publish setelah konten AI dibuat.

### Lowongan kerja
- Dikenali dari slug/nama kategori yang mengandung `lowongan` atau `loker` (`Post::isJob`).
- **Dedup lintas platform**: `place_id = Post::jobPlaceId($company, $position, $city)`, yaitu `job_` + 24 karakter MD5 dari nama perusahaan (tanpa PT/CV/UD/Yayasan), posisi, dan kota yang sudah dinormalisasi. Lowongan yang sama dari JobStreet dan Glints menghasilkan ID yang sama. **Formula ini dikunci oleh `tests/Unit/JobPlaceIdTest.php`**, karena mengubahnya akan memecah dedup semua data lama.
- **Kedaluwarsa**: `expired_at`, atau 21 hari setelah `published_at`. Post tidak dihapus (menghindari 404 di Google). Tampil badge "ditutup" dan JSON-LD memakai `validThrough`.
- Tampilan loker hanya teks, tanpa foto. Scraper foto Maps melewati post loker; logo perusahaan memakai jalur terpisah.

### Tempat (Google Maps)
- `place_id` adalah ID Google Maps (unik) dan menjadi kunci import ulang/dedup.
- Venue auto-publish setelah minimal 2 foto berhasil di-scrape.

### SEO
- URL kanonik: `/{category}/{city}/{slug}`. Jangan ubah format slug tanpa redirect 301.
- `Post::generateJsonLd()` menghasilkan schema (LocalBusiness / JobPosting).
- Sitemap dinamis: `/sitemap.xml`. Untuk frontend CSR: `/api/csr/sitemap.xml?domain=...`.
- **Loker tutup** (`isExpired()`): tetap bisa dibuka, tapi `noindex,follow`, tanpa `JobPosting`, dan tidak masuk sitemap.
- `JobPosting` wajib jujur (pedoman Google Jobs): `validThrough` = `expired_at` atau `published_at` + 21 hari (jangan digeser maju), `baseSalary` hanya jika gaji diketahui, `directApply: false` karena pelamar diarahkan ke situs sumber.
- Sitemap hanya berisi halaman bernilai: listing kategori+kota minimal 3 post, tanpa kombinasi kategori+landmark. Halaman landmark dengan < 3 post published dalam radiusnya diberi `noindex` dan tidak masuk sitemap. Keduanya memakai ambang `Landmark::MIN_PLACES` dan radius `Landmark::radiusKm()`. Daftar landmark layak indeks di sitemap di-cache selama 1 jam (`sitemap_indexable_landmark_ids`); perubahan konten bisa tertunda hingga cache kedaluwarsa.

## Peta URL publik

| URL | Handler |
|---|---|
| `/` | `Livewire\Home` |
| `/cari` | `Livewire\Search` |
| `/blog`, `/blog/{slug}` | `BlogIndex`, `BlogShow` |
| `/tools`, `/karir/cv-builder`, `/karir/interview-prep` | Tools karier AI |
| `POST /job-tools/*` | Endpoint AI (throttled 15–30/menit) |
| `/disekitar/{landmark}`, `/{category}/disekitar/{landmark}` | `LandmarkShow` |
| `/widget/post/{slug}`, `/widget/badge/{slug}` | Widget embed |
| `/job-image/{slug}.svg|png` | Gambar OG loker dinamis |
| `/{category}`, `/{category}/{city}`, `/{category}/{city}/{slug}` | `PostList`, `PostShow` (**harus di akhir `routes/web.php`**) |
| `/api/csr/{jobs,jobs/{id},config,sitemap.xml}` | API headless untuk frontend Vercel ([panduan](guides/csr-frontend.md)) |
| `/rahasia` | Admin Filament |

# Domain & Aturan Bisnis

Cakupan produk: **lowongan kerja** dan **tools karier**. Tempat, landmark, dan blog (warisan DISEKITAR) tidak ada lagi.

## Entitas

| Model | Keterangan |
|---|---|
| `Post` | Entitas inti: satu lowongan kerja. Relasi `province`, `city`, `category`, `details` (key-value), `images` (logo/gambar loker). Soft delete. |
| `Category`, `Province`, `City` | Taksonomi. Kategori yang tersisa: `lowongan-kerja`. `City.slug` berformat `bandung-kota` / `bogor-kabupaten`. |
| `Setting` | Konfigurasi runtime key-value (lihat ARCHITECTURE). |
| `ApifyAccount` | Pool token Apify yang dirotasi. |
| `GoogleMapsScrapeJob` | Nama legacy; kini status antrean **ekstraksi loker** (`engine` = `jobstreet` / `glints`). |
| `AiGenerationJob` | Antrean generate konten AI per post. |
| `PageView`, `ToolUsage`, `ThreadsEngagement` | Analitik. |
| `AiChatSession`, `AiReport` | AI Playground admin. |

## Aturan penting (jangan dilanggar)

### Status post
- Hanya `status = published` yang tampil publik (`Post::scopePublished`).
- `published_at` diisi otomatis saat status menjadi `published` (hook di `Post::boot`).
- Hasil scrape masuk sebagai `draft` (stok), lalu dipublikasikan setelah konten AI dibuat.

### Lowongan kerja
- Dikenali dari slug/nama kategori yang mengandung `lowongan` atau `loker` (`Post::isJob`).
- **Dedup lintas platform**: `place_id = Post::jobPlaceId($company, $position, $city)`, yaitu `job_` + 24 karakter MD5 dari nama perusahaan (tanpa PT/CV/UD/Yayasan), posisi, dan kota yang sudah dinormalisasi. Lowongan yang sama dari JobStreet dan Glints menghasilkan ID yang sama. **Formula ini dikunci oleh `tests/Unit/JobPlaceIdTest.php`**, karena mengubahnya akan memecah dedup semua data lama. Kolom `posts.place_id` tetap dipakai untuk ini.
- **Kedaluwarsa**: `expired_at`, atau 21 hari setelah `published_at`. Post tidak dihapus (menghindari 404 di Google). Tampil badge "ditutup" dan JSON-LD memakai `validThrough`.
- Tampilan loker hanya teks, tanpa foto. Logo perusahaan memakai jalur terpisah (`post_images`).

### SEO
- URL kanonik: `/{category}/{city}/{slug}`. Jangan ubah format slug tanpa redirect 301.
- `Post::generateJsonLd()` menghasilkan schema `JobPosting`.
- Sitemap dinamis: `/sitemap.xml`. Untuk frontend CSR: `/api/csr/sitemap.xml?domain=...`.
- **Loker tutup** (`isExpired()`): tetap bisa dibuka, tapi `noindex,follow`, tanpa `JobPosting`, dan tidak masuk sitemap.
- `JobPosting` wajib jujur (pedoman Google Jobs): `validThrough` = `expired_at` atau `published_at` + 21 hari (jangan digeser maju), `baseSalary` hanya jika gaji diketahui, `directApply: false` karena pelamar diarahkan ke situs sumber.
- Sitemap hanya berisi halaman bernilai: listing kategori+kota minimal 3 post.
- URL warisan DISEKITAR (`/blog*`, `/disekitar/*`, halaman tempat/landmark yang sudah dihapus) diarahkan 301 ke `/` atau 404. Setelah deploy, kirim ulang sitemap di Google Search Console.

## Peta URL publik

| URL | Handler |
|---|---|
| `/` | `Livewire\Home` |
| `/cari` | `Livewire\Search` |
| `/tools`, `/karir/cv-builder`, `/karir/interview-prep` | Tools karier AI |
| `POST /job-tools/*` | Endpoint AI (throttled 15-30/menit) |
| `/job-image/{slug}.svg|png` | Gambar OG loker dinamis |
| `/tentang-kami`, `/kebijakan-privasi`, `/syarat-ketentuan`, `/kontak`, `/disclaimer` | Halaman statis (`PageShow`) |
| `/{category}`, `/{category}/{city}`, `/{category}/{city}/{slug}` | `PostList`, `PostShow` (**harus di akhir `routes/web.php`**); hanya kategori loker yang valid |
| `/api/csr/{jobs,jobs/{id},config,sitemap.xml}` | API headless untuk frontend Vercel ([panduan](guides/csr-frontend.md)) |
| `/rahasia` | Admin Filament |

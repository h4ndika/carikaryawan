# DISEKITAR SEO Guidelines

**Version:** 1.0
**Status:** Active
**Last Updated:** 2026-06-29

---

# Overview

Dokumen ini menjelaskan standar Search Engine Optimization (SEO) untuk seluruh platform DISEKITAR.

SEO bukan merupakan fitur tambahan, melainkan bagian dari arsitektur sistem.

Seluruh halaman yang dapat diakses publik wajib mengikuti standar pada dokumen ini.

---

# SEO Philosophy

DISEKITAR dibangun dengan prinsip:

> **SEO First Development**

Setiap halaman harus:

- Mudah ditemukan mesin pencari
- Memiliki struktur yang konsisten
- Memiliki metadata yang lengkap
- Cepat dimuat
- Ramah perangkat mobile
- Memiliki konten yang relevan

---

# SEO Goals

Target utama:

- Organic Traffic
- Local SEO
- Featured Snippet
- Rich Result
- Google Discover (Future)
- AI Search Readiness

---

# URL Structure (Standard: Subdirectory)

Gunakan struktur Subdirectory untuk memusatkan seluruh Domain Authority (DA), PageRank, dan efisiensi crawl budget ke root domain:

```text
https://disekitar.id/hotel
https://disekitar.id/hotel/bandung
https://disekitar.id/hotel/bandung/hotel-harmoni
```

*Catatan: Pendekatan subdomain (`hotel.disekitar.id`) tidak direkomendasikan karena memecah Domain Authority dan menyulitkan pengindeksan SEO lokal untuk direktori agregator.*

---

# Roadmap SEO & Local Search (Next Features)

### 1. Halaman Berbasis Landmark & Titik Transit (Transit / Proximity SEO)
- **Problem**: Pengguna yang baru tiba di stasiun/bandara/terminal mencari dengan query seperti *"hotel disekitar stasiun bandung"*, *"cafe dekat stasiun tugu jogja"*.
- **Solusi**: 
  - Dukungan entitas Landmark (Stasiun, Bandara, Terminal, Alun-alun, Tempat Wisata Utama).
  - URL / Filter: `disekitar.id/hotel/bandung?landmark=stasiun-bandung` atau `disekitar.id/landmark/{landmark-slug}/{kategori}`.
  - Geo-proximity query berdasarkan radius (latitude / longitude) dari titik landmark.

### 2. Internal Linking Lintas Kategori Berbasis Lokasi (Cross-Category Proximity Linking)
- **Problem**: Pengunjung membaca ulasan satu hotel dan butuh rekomendasi makanan atau aktivitas terdekat.
- **Solusi**:
  - Pada halaman detail post (misal: Hotel di Bandung Kota), tampilkan blok rekomendasi otomatis:
    - *"Kuliner Pilihan di Sekitar Sini (Radius < 2 km)"*
    - *"Tempat Menarik Lainnya di Bandung Kota"*
  - Menjaga pengunjung tetap berinteraksi di dalam situs (mengurangi bounce rate) dan menyebarkan PageRank link equity ke seluruh artikel di wilayah yang sama.


---

# Slug

Setiap slug harus:

- unik
- menggunakan huruf kecil
- menggunakan tanda "-"
- tidak menggunakan karakter khusus

Benar

```text
hotel-harmoni-bandung
```

Salah

```text
Hotel Harmoni

hotel_harmoni

hotel123!!!
```

---

# Title

Setiap halaman wajib memiliki SEO Title.

Target panjang:

```text
50–60 karakter
```

Contoh:

```text
Hotel Harmoni Bandung | Harga, Fasilitas & Lokasi
```

---

# Meta Description

Setiap halaman wajib memiliki Meta Description.

Target:

```text
120–160 karakter
```

Deskripsi harus menjelaskan isi halaman secara alami.

---

# Heading Structure

Gunakan struktur heading yang benar.

```text
H1

↓

H2

↓

H3

↓

H4
```

Aturan:

- H1 hanya satu kali.
- H2 digunakan untuk section utama.
- Jangan melewati level heading.

---

# Canonical URL

Seluruh halaman wajib memiliki Canonical URL.

Contoh:

```html
<link
    rel="canonical"
    href="https://hotel.disekitar.com/bandung/hotel-harmoni"
/>
```

---

# Open Graph

Semua halaman harus memiliki:

- og:title
- og:description
- og:image
- og:url
- og:type

---

# Twitter Card

Minimal:

- summary_large_image
- title
- description
- image

---

# Structured Data

Gunakan JSON-LD.

Jenis Schema disesuaikan dengan jenis konten.

Contoh:

- Article
- Hotel
- Restaurant
- Event
- JobPosting
- LocalBusiness
- BreadcrumbList
- Organization
- WebSite
- FAQPage (Jika Ada)

---

# Breadcrumb

Semua halaman detail wajib memiliki breadcrumb.

Contoh:

```text
Home

↓

Hotel

↓

Bandung

↓

Hotel Harmoni
```

Gunakan juga Schema BreadcrumbList.

---

# Sitemap

Website wajib menghasilkan XML Sitemap secara otomatis.

Minimal terdiri dari:

- Homepage
- Category
- Location
- Post
- Tag (Opsional)

Sitemap harus diperbarui ketika:

- Post dipublikasikan
- Post dihapus
- Slug berubah

---

# Robots.txt

Default:

```text
Allow: /
```

Blokir:

- Admin
- Login
- Dashboard
- API Internal

---

# Internal Linking

Setiap halaman detail harus memiliki internal link.

Contoh:

- Konten terkait
- Artikel terkait
- Tempat sekitar
- Kategori yang sama
- Kota yang sama

Tujuan:

- meningkatkan crawlability
- meningkatkan waktu kunjungan
- memperkuat topical authority

---

# Image SEO

Seluruh gambar wajib memiliki:

- Alt Text
- Width
- Height
- Lazy Loading
- Format modern (WebP atau AVIF jika memungkinkan)

Nama file harus deskriptif.

Benar:

```text
hotel-harmoni-bandung.webp
```

Salah:

```text
IMG_001.jpg
```

---

# Content Quality

Konten harus:

- original
- informatif
- mudah dibaca
- relevan
- memiliki struktur yang baik

Hindari:

- keyword stuffing
- duplikasi konten
- halaman tipis (thin content)

---

# Pagination

Gunakan URL yang konsisten.

Contoh:

```text
?page=2
```

Halaman pertama tidak boleh menggunakan:

```text
?page=1
```

---

# HTTP Status

Gunakan status yang benar.

| Status | Penggunaan              |
| ------ | ----------------------- |
| 200    | Halaman tersedia        |
| 301    | Redirect permanen       |
| 302    | Redirect sementara      |
| 404    | Halaman tidak ditemukan |
| 410    | Konten dihapus permanen |

---

# Redirect

Jika slug berubah:

Gunakan:

```text
301 Permanent Redirect
```

Jangan menggunakan redirect berantai.

---

# Duplicate Content

Hindari halaman dengan isi yang sama.

Gunakan:

- Canonical
- Redirect
- Noindex (jika diperlukan)

---

# Performance

Target:

- LCP < 2.5 detik
- CLS < 0.1
- INP sesuai rekomendasi Core Web Vitals

Optimasi:

- CDN
- Image Optimization
- Cache
- Minify Asset
- Compression

---

# Mobile First

Semua halaman harus:

- responsive
- cepat dimuat
- mudah dibaca pada perangkat mobile

---

# Local SEO

Location adalah domain utama DISEKITAR.

Semua halaman lokasi harus memiliki:

- Nama lokasi
- Latitude
- Longitude
- Alamat (jika tersedia)
- Google Maps Link (Opsional)

Gunakan schema yang sesuai untuk lokasi dan bisnis lokal.

---

# AI Search Readiness

Konten harus mudah dipahami oleh AI Search Engine.

Prinsip:

- struktur yang jelas
- heading yang konsisten
- metadata lengkap
- schema lengkap
- bahasa natural

---

# Metadata Rules

Setiap Post minimal memiliki:

- SEO Title
- Meta Description
- Featured Image
- Canonical URL
- Open Graph
- Twitter Card

---

# Multi Domain Strategy

DISEKITAR mendukung beberapa subdomain.

Contoh:

```text
hotel.disekitar.com

kuliner.disekitar.com

event.disekitar.com

loker.disekitar.com
```

Setiap subdomain dianggap sebagai bagian dari satu ekosistem.

Gunakan internal link yang saling mendukung bila relevan.

---

# SEO Checklist

Sebelum halaman dipublikasikan, pastikan:

- SEO Title tersedia
- Meta Description tersedia
- H1 tersedia
- Canonical tersedia
- Open Graph tersedia
- Twitter Card tersedia
- Featured Image tersedia
- Alt Text tersedia
- Schema tersedia
- Sitemap diperbarui
- URL sudah benar
- Slug sudah benar
- Tidak ada broken link

---

# AI Coding Guidelines

Saat membuat halaman publik:

- Selalu hasilkan metadata SEO.
- Gunakan slug yang konsisten.
- Pastikan setiap halaman memiliki H1.
- Tambahkan JSON-LD sesuai jenis konten.
- Gunakan breadcrumb.
- Pastikan gambar memiliki alt text.
- Jangan menghasilkan URL yang tidak SEO-friendly.
- Jangan membuat halaman tanpa meta title atau meta description.
- Pertimbangkan performa sebagai bagian dari SEO.

---

# Definition of Done (SEO)

Sebuah halaman publik dianggap selesai apabila:

- Dapat diindeks mesin pencari.
- Memiliki metadata lengkap.
- Memiliki schema yang sesuai.
- Memiliki URL yang bersih.
- Memiliki internal link.
- Memiliki performa yang baik.
- Mobile-friendly.
- Tidak menghasilkan duplicate content.
- Lolos audit SEO internal.

# SEO_STRATEGY.md

> Project: DISEKITAR
>
> Version: 1.0
>
> Status: Draft

---

# 1. Purpose

Dokumen ini mendefinisikan strategi SEO jangka panjang untuk platform DISEKITAR.

Tujuan utama:

- Mendapatkan traffic organik.
- Menjadi direktori lokal terbesar di Indonesia.
- Menghasilkan landing page otomatis dari database.
- Memastikan seluruh konten mudah diindeks mesin pencari.

---

# 2. SEO Principles

Seluruh halaman harus:

- Memiliki URL yang unik.
- Memiliki Title unik.
- Memiliki Meta Description unik.
- Memiliki Canonical URL.
- Memiliki Structured Data.
- Memiliki Internal Link.
- Memiliki Breadcrumb.
- Mobile Friendly.
- Fast Loading.
- Dapat di-crawl.

---

# 3. SEO Hierarchy

```text
Homepage
│
├── Category
│   ├── City
│   │   ├── District
│   │   │   └── Detail Place
│   │   └── Landing Page
│   └── Landing Page
│
├── Event
├── Article
└── Job
```

---

# 4. Content Types

## Homepage

Target keyword:

- tempat di Indonesia
- direktori Indonesia

---

## Category Page

Contoh

```text
/hotel
/kuliner
/wisata
```

Target:

Keyword utama.

---

## Location Page

Contoh

```text
/hotel/cirebon
/kuliner/bandung
```

Target:

Kategori + Kota.

---

## District Page

```text
/hotel/cirebon/kesambi
```

Target:

Kategori + Kecamatan.

---

## Detail Place

```text
/hotel/cirebon/hotel-santika-cirebon
```

Target:

Brand keyword.

---

## Article

Mendukung topical authority.

---

## Event

Target keyword event lokal.

---

## Job

Target keyword lowongan kerja lokal.

---

# 5. Programmatic SEO

Landing page dibuat otomatis berdasarkan kombinasi data.

Contoh

Kategori + Kota

```text
/hotel/cirebon
```

Kategori + Kecamatan

```text
/hotel/cirebon/kesambi
```

Kategori + Tag

```text
/hotel/cirebon/pet-friendly
```

Kategori + Facility

```text
/hotel/cirebon/kolam-renang
```

Kategori + Harga

```text
/hotel/cirebon/murah
```

Kategori + Rating

```text
/hotel/cirebon/rating-terbaik
```

---

# 6. Dynamic Metadata

Meta Title

Template

```text
{Category} di {City} | DISEKITAR
```

Contoh

```text
Hotel di Cirebon | DISEKITAR
```

---

Meta Description

Template

```text
Temukan {Category} terbaik di {City}.
Lihat alamat, fasilitas, jam buka, foto, peta, dan ulasan hanya di DISEKITAR.
```

---

# 7. Heading Structure

Setiap halaman menggunakan struktur:

```text
H1

H2

H3
```

Satu halaman hanya memiliki satu H1.

---

# 8. Structured Data

Semua halaman menggunakan Schema.org.

Homepage

```text
WebSite
Organization
```

Place

```text
LocalBusiness
```

Hotel

```text
Hotel
```

Restaurant

```text
Restaurant
```

Hospital

```text
Hospital
```

Article

```text
Article
```

Job

```text
JobPosting
```

Event

```text
Event
```

Breadcrumb

```text
BreadcrumbList
```

---

# 9. Breadcrumb

Contoh

```text
Home

↓

Hotel

↓

Cirebon

↓

Hotel Santika
```

---

# 10. Internal Linking

Setiap halaman minimal memiliki:

- Related Place
- Nearby Place
- Related Article
- Popular Category
- Nearby City

---

# 11. Sitemap

```text
/sitemap.xml
```

Child sitemap

```text
/sitemap-places.xml
/sitemap-categories.xml
/sitemap-cities.xml
/sitemap-articles.xml
/sitemap-events.xml
/sitemap-jobs.xml
```

Sitemap dibuat otomatis.

---

# 12. Robots

```text
/robots.txt
```

Tidak mengindeks:

- Admin
- Login
- API
- Search Result
- Draft

---

# 13. Canonical

Semua halaman memiliki:

```text
<link rel="canonical">
```

Canonical selalu mengarah ke URL utama.

---

# 14. Open Graph

Setiap halaman memiliki:

- og:title
- og:description
- og:image
- og:url

---

# 15. Twitter Card

Gunakan:

- summary_large_image

---

# 16. Image SEO

Setiap gambar memiliki:

- ALT
- Title
- Width
- Height
- Lazy Loading

Nama file:

```text
hotel-santika-cirebon.jpg
```

---

# 17. Performance

Target:

- LCP < 2.5s
- INP < 200ms
- CLS < 0.1

Optimasi:

- Image Compression
- CDN
- Cache
- Lazy Load
- HTTP Compression

---

# 18. Crawl Budget

Prioritas crawl:

1. Homepage
2. Category
3. City
4. District
5. Detail Place
6. Article
7. Event
8. Job

Halaman tanpa nilai SEO tidak dimasukkan ke sitemap.

---

# 19. Duplicate Content

Hindari:

- URL berbeda dengan konten sama.
- Parameter yang dapat diindeks.
- Meta title duplikat.
- Meta description duplikat.

Gunakan:

- Canonical
- 301 Redirect

---

# 20. AI Generated Content

Konten yang dibuat AI wajib:

- Diverifikasi editor.
- Menggunakan data nyata dari database.
- Tidak menghasilkan halaman kosong.
- Memiliki nilai tambah bagi pengguna.

---

# 21. KPI SEO

Pantau metrik berikut:

- Organic Sessions
- Indexed Pages
- Impressions
- Clicks
- CTR
- Average Position
- Core Web Vitals
- Crawl Errors
- Broken Links

---

# 22. Future SEO

Direncanakan:

- FAQ otomatis.
- "Tempat di sekitar saya".
- Halaman berdasarkan fasilitas.
- Halaman berdasarkan tag.
- Halaman berdasarkan musim atau momen (Liburan, Ramadan, Natal, Tahun Baru).
- Halaman rekomendasi AI.
- Konten multibahasa.
- Integrasi Google Business Profile.

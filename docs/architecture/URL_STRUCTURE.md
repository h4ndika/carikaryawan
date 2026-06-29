# URL_STRUCTURE.md

> Project: DISEKITAR
>
> Version: 1.0
>
> Status: Draft

---

# 1. Purpose

Dokumen ini mendefinisikan standar URL pada seluruh platform DISEKITAR.

Tujuan:

- SEO Friendly
- Konsisten
- Mudah dipahami pengguna
- Mudah dikembangkan
- Mendukung AI Agent

---

# 2. URL Principles

Semua URL harus:

- Menggunakan huruf kecil
- Menggunakan tanda "-"
- Tidak menggunakan underscore
- Tidak menggunakan ID
- Menggunakan slug
- Bersifat permanen

Contoh

Benar

```text
/hotel/hotel-santika-cirebon
```

Salah

```text
/hotel/123
/hotel?id=123
/HOTEL/Santika
```

---

# 3. Public URL Structure

Homepage

```text
/
```

---

Kategori

```text
/{category}
```

Contoh

```text
/hotel
/kuliner
/wisata
/event
/lowongan
/artikel
```

---

Listing berdasarkan lokasi

```text
/{category}/{city}
```

Contoh

```text
/hotel/cirebon
/hotel/bandung
/kuliner/jakarta
```

---

Listing berdasarkan kecamatan

```text
/{category}/{city}/{district}
```

Contoh

```text
/hotel/cirebon/kesambi
```

---

Detail Place

```text
/{category}/{city}/{slug}
```

Contoh

```text
/hotel/cirebon/hotel-santika-cirebon
```

---

Artikel

```text
/artikel/{slug}
```

---

Event

```text
/event/{slug}
```

---

Lowongan

```text
/lowongan/{slug}
```

---

Tag

```text
/tag/{slug}
```

---

Pencarian

```text
/search
```

Query

```text
/search?q=hotel+cirebon
```

---

# 4. Admin URL

```text
/admin
```

Dashboard

```text
/admin/dashboard
```

Place

```text
/admin/places
```

Category

```text
/admin/categories
```

Review

```text
/admin/reviews
```

Media

```text
/admin/media
```

---

# 5. API URL

Semua API menggunakan prefix

```text
/api/v1
```

Public

```text
/api/v1/public
```

Admin

```text
/api/v1/admin
```

---

# 6. Slug Rules

Slug harus:

- unik
- lowercase
- menggunakan "-"
- tanpa karakter khusus

Contoh

```text
hotel-santika-cirebon
```

---

# 7. Query Parameters

Search

```text
?q=hotel
```

Sorting

```text
?sort=name
```

Order

```text
?order=asc
```

Pagination

```text
?page=2
```

Filter

```text
?category=hotel
```

Multi Filter

```text
?city=cirebon&district=kesambi
```

---

# 8. Canonical Rules

Setiap halaman memiliki canonical URL.

Contoh

```text
https://disekitar.com/hotel/cirebon/hotel-santika-cirebon
```

Tidak boleh terdapat lebih dari satu canonical untuk konten yang sama.

---

# 9. Redirect Rules

Jika slug berubah:

301 Redirect

Contoh

```text
hotel-santika

↓

hotel-santika-cirebon
```

Semua URL lama harus diarahkan ke URL baru.

---

# 10. Sitemap Structure

```text
/sitemap.xml
```

Child sitemap

```text
/sitemap-places.xml
/sitemap-articles.xml
/sitemap-events.xml
/sitemap-jobs.xml
/sitemap-categories.xml
```

---

# 11. Robots

```text
/robots.txt
```

---

# 12. URL Priority

Priority tertinggi

Homepage

↓

Category

↓

City

↓

District

↓

Detail

---

# 13. Reserved Routes

Tidak boleh digunakan sebagai slug:

```text
admin
api
search
login
logout
register
dashboard
tag
event
artikel
lowongan
media
robots.txt
favicon.ico
sitemap.xml
```

---

# 14. Future URL

Disiapkan untuk:

Nearby

```text
/nearby
```

Maps

```text
/maps
```

Compare

```text
/compare
```

Recommendation

```text
/recommendation
```

AI Search

```text
/ai
```

---

# 15. URL Naming Convention

Gunakan bahasa Indonesia sebagai URL utama.

Contoh

```text
/hotel
/wisata
/kuliner
/rumah-sakit
/sekolah
/tempat-ibadah
```

Hindari penggunaan URL campuran bahasa Indonesia dan Inggris.

---

# 16. SEO Notes

Semua halaman harus memiliki:

- Meta Title
- Meta Description
- Canonical
- Open Graph
- Structured Data (Schema.org)
- Breadcrumb
- Sitemap
- Robots Directive

URL tidak boleh berubah kecuali benar-benar diperlukan. Jika terjadi perubahan, wajib menggunakan HTTP 301 Redirect.

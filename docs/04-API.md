# DISEKITAR API Standard

**Version:** 1.0
**Status:** Draft
**Last Updated:** 2026-06-29

---

# Overview

Dokumen ini menjelaskan standar pengembangan REST API pada DISEKITAR.

Seluruh endpoint, baik internal maupun publik, wajib mengikuti standar yang telah ditentukan pada dokumen ini.

Tujuan utama:

- Konsisten
- Mudah digunakan
- Mudah dikembangkan
- Mudah dipahami AI Coding Agent
- Siap digunakan oleh berbagai frontend

---

# API Philosophy

DISEKITAR menggunakan pendekatan:

> **API First Development**

Business Logic tidak boleh bergantung pada:

- Blade
- Filament
- Mobile
- Next.js

Semua frontend hanya bertugas mengonsumsi data.

---

# Base URL

Development

```text
/api/v1
```

Contoh

```text
GET /api/v1/posts
GET /api/v1/posts/{slug}
GET /api/v1/categories
GET /api/v1/locations
```

---

# Versioning

Gunakan URL Versioning.

Contoh

```text
/api/v1
/api/v2
```

Jangan mengubah response lama.

Jika terdapat perubahan breaking change, buat versi baru.

---

# Resource Naming

Gunakan bentuk jamak.

Benar

```text
/posts
/categories
/locations
/reviews
```

Salah

```text
/post
/getPosts
/get-post-list
```

---

# HTTP Method

| Method | Fungsi                 |
| ------ | ---------------------- |
| GET    | Mengambil data         |
| POST   | Membuat data           |
| PUT    | Mengubah seluruh data  |
| PATCH  | Mengubah sebagian data |
| DELETE | Menghapus data         |

---

# Response Format

Semua endpoint harus memiliki format response yang sama.

## Success Response

```json
{
    "success": true,
    "message": "Request successful.",
    "data": {}
}
```

---

## Success List

```json
{
    "success": true,
    "message": "Request successful.",
    "data": [],
    "meta": {}
}
```

---

## Error Response

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {}
}
```

---

# HTTP Status Code

| Code | Keterangan            |
| ---- | --------------------- |
| 200  | Success               |
| 201  | Created               |
| 204  | No Content            |
| 400  | Bad Request           |
| 401  | Unauthorized          |
| 403  | Forbidden             |
| 404  | Not Found             |
| 409  | Conflict              |
| 422  | Validation Error      |
| 429  | Too Many Requests     |
| 500  | Internal Server Error |

---

# Pagination

Gunakan Laravel Pagination.

Response

```json
{
    "success": true,
    "data": [],
    "meta": {
        "current_page": 1,
        "last_page": 10,
        "per_page": 20,
        "total": 200
    }
}
```

Default

```text
20 items per page
```

Maksimum

```text
100 items
```

---

# Filtering

Gunakan query string.

Contoh

```text
GET /posts?category=hotel
```

```text
GET /posts?city=bandung
```

```text
GET /posts?status=published
```

Filter dapat digabungkan.

```text
GET /posts?category=hotel&city=bandung
```

---

# Sorting

Gunakan parameter:

```text
sort=
```

Contoh

```text
sort=published_at
```

Descending

```text
sort=-published_at
```

Contoh

```text
GET /posts?sort=-published_at
```

---

# Search

Gunakan parameter:

```text
search=
```

Contoh

```text
GET /posts?search=hotel
```

---

# Includes

Gunakan include untuk eager loading.

Contoh

```text
GET /posts?include=gallery
```

```text
GET /posts?include=category
```

```text
GET /posts?include=location
```

Multiple

```text
GET /posts?include=gallery,category,location
```

---

# Sparse Fieldset

Client dapat memilih field.

Contoh

```text
GET /posts?fields=id,title,slug
```

---

# Authentication

Gunakan Laravel Sanctum.

Header

```text
Authorization: Bearer {token}
```

---

# Validation Error

Response

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "title": ["The title field is required."]
    }
}
```

---

# Resource

Gunakan Laravel API Resource.

Jangan return Model secara langsung.

Benar

```php
return new PostResource($post);
```

Salah

```php
return $post;
```

---

# Endpoint Structure

## Authentication

```text
POST /login
POST /logout
POST /forgot-password
POST /reset-password
GET /me
```

---

## Posts

```text
GET /posts
POST /posts
GET /posts/{slug}
PUT /posts/{id}
DELETE /posts/{id}
```

---

## Categories

```text
GET /categories
GET /categories/{slug}
```

---

## Locations

```text
GET /locations
GET /locations/{slug}
```

---

## Reviews

```text
GET /posts/{id}/reviews
POST /posts/{id}/reviews
```

---

## Bookmark

```text
GET /bookmarks
POST /bookmarks
DELETE /bookmarks/{id}
```

---

# Rate Limiting

Default

```text
60 request / minute
```

API publik dapat memiliki limit yang berbeda.

---

# Error Handling

Gunakan Exception.

Jangan mengembalikan error manual dari Controller.

Seluruh exception diproses oleh Global Exception Handler.

---

# Date Format

Gunakan standar ISO-8601.

Contoh

```text
2026-06-29T08:30:00Z
```

---

# Boolean

Gunakan tipe boolean.

Benar

```json
"is_featured": true
```

Salah

```json
"is_featured": "yes"
```

---

# Enum

Gunakan string yang mudah dipahami.

Contoh

```json
"status": "published"
```

Hindari

```json
"status": 2
```

---

# File Upload

Gunakan

```text
multipart/form-data
```

Response

```json
{
    "success": true,
    "data": {
        "url": "...",
        "path": "...",
        "width": 1920,
        "height": 1080
    }
}
```

---

# API Documentation

Seluruh endpoint wajib memiliki dokumentasi.

Minimal mencakup:

- URL
- Method
- Parameter
- Request Body
- Response
- Error Response

---

# Security

Semua endpoint wajib memperhatikan:

- Authentication
- Authorization
- Validation
- Rate Limiting
- Mass Assignment Protection

---

# Deprecation Policy

Endpoint lama tidak boleh langsung dihapus.

Tahapan:

1. Deprecated
2. Dokumentasikan pengganti
3. Hapus pada major version berikutnya

---

# Performance

Gunakan:

- API Resource
- Pagination
- Eager Loading
- Cache
- Compression

Hindari:

- N+1 Query
- Nested Resource berlebihan
- Mengembalikan field yang tidak diperlukan

---

# AI Coding Guidelines

Saat membuat endpoint:

- Gunakan RESTful naming.
- Jangan membuat endpoint dengan nama aksi (`/getPosts`, `/savePost`).
- Selalu gunakan Form Request untuk validasi.
- Selalu gunakan API Resource untuk response.
- Selalu gunakan Action/Service untuk business logic.
- Jangan query database langsung di Controller.
- Gunakan eager loading bila terdapat relasi.
- Pertahankan format response yang konsisten pada seluruh endpoint.

---

# API Goals

Implementasi API dianggap berhasil apabila:

- Konsisten di seluruh module.
- Mudah dikonsumsi oleh Website, Mobile, dan Partner.
- Mudah di-versioning.
- Mudah dipelihara.
- Tidak memiliki breaking change tanpa versi baru.
- Dapat digunakan ulang oleh berbagai frontend tanpa perubahan business logic.

# DOMAIN_MODEL.md

> Project: DISEKITAR
>
> Last Updated: YYYY-MM-DD
>
> Status: Draft

---

# 1. Purpose

Dokumen ini mendefinisikan seluruh Domain Model yang digunakan pada project DISEKITAR.

Dokumen ini menjadi acuan utama untuk:

- Database Design
- API Design
- Backend Development
- Frontend Development
- CMS
- AI Agent

Semua perubahan terhadap entity wajib diperbarui pada dokumen ini sebelum implementasi.

---

# 2. Domain Overview

DISEKITAR merupakan platform direktori lokal yang mengelola berbagai informasi berdasarkan lokasi.

Contoh:

- Hotel
- Wisata
- Kuliner
- Rumah Sakit
- Sekolah
- Tempat Ibadah
- Event
- Lowongan Kerja
- UMKM
- Artikel

Semua jenis data tersebut diperlakukan sebagai **Content** yang memiliki lokasi.

---

# 3. Core Principles

## 3.1 Single Source of Truth

Satu tempat hanya memiliki satu data utama.

Contoh:

Hotel Santika Cirebon

tidak boleh memiliki dua data berbeda.

---

## 3.2 Reusable Entity

Entity harus dapat digunakan oleh berbagai module.

Contoh:

Gallery

digunakan oleh:

- Hotel
- Event
- Artikel
- Wisata

---

## 3.3 Modular

Setiap module berdiri sendiri.

Contoh:

Place Module

tidak boleh bergantung langsung pada

Job Module.

---

# 4. Core Entity

## Place

Merupakan entity utama.

Segala sesuatu yang mempunyai lokasi fisik menggunakan entity ini.

Contoh:

- Hotel
- Cafe
- Rumah Sakit
- Toko
- ATM
- Museum
- Sekolah

Fields:

- id
- slug
- name
- summary
- description
- status
- published_at

Relation

Place

- belongsTo Category
- belongsTo Location
- hasMany Gallery
- hasMany Contact
- hasMany OpeningHour
- hasMany SocialMedia
- hasMany Facility
- hasMany Tag
- hasMany Review

---

## Category

Mengelompokkan Place.

Contoh:

Hotel

Cafe

Wisata

Rumah Sakit

Sekolah

Hierarchy:

Category

└── Child Category

Contoh

Hotel

└── Hotel Syariah

---

## Location

Entity lokasi administratif.

Hierarchy

Province

↓

City

↓

District

↓

Village

Satu Place hanya memiliki satu Village.

Data di atasnya didapat dari relasi.

---

## Address

Alamat lengkap Place.

Fields

- province_id
- city_id
- district_id
- village_id
- postal_code
- full_address
- latitude
- longitude

---

## Gallery

Media gambar.

Fields

- path
- alt
- sort_order

---

## Contact

Kontak.

Jenis:

- WhatsApp
- Telephone
- Email
- Website

---

## Opening Hour

Jam operasional.

Fields

- day
- open
- close
- is_closed

---

## Social Media

Jenis:

- Instagram
- Facebook
- TikTok
- YouTube
- X

---

## Facility

Contoh

- Wifi
- Musholla
- Parkir
- AC
- Toilet

---

## Tag

Keyword tambahan.

Contoh

- Ramah Anak
- Pet Friendly
- 24 Jam

---

## Review

Review pengguna.

Fields

- rating
- review
- source

---

# 5. Secondary Entity

Entity berikut bukan Place.

## Event

Memiliki lokasi.

Bisa berelasi dengan Place.

Contoh

Festival Batik

berlokasi di

Alun-alun Cirebon.

---

## Job

Lowongan pekerjaan.

Berelasi ke Place.

Contoh

PT ABC

membuka

Backend Developer.

---

## Article

Artikel.

Dapat memiliki relasi ke

- Place
- Category
- Tag

---

## Promotion

Promo dari Place.

Contoh

Diskon 50%

Happy Hour

Buy 1 Get 1

---

# 6. Relationship

Province

↓

City

↓

District

↓

Village

↓

Address

↓

Place

↓

Gallery

↓

Facility

↓

Review

↓

Promotion

Place

↓

Job

Place

↓

Event

---

# 7. Status

Semua entity memiliki status.

Enum

Draft

Published

Archived

Deleted

---

# 8. Slug Rules

Slug harus unik.

Contoh

hotel-santika-cirebon

Tidak boleh

hotel santika

Hotel Santika

HOTEL_SANTIKA

---

# 9. SEO

Setiap entity mendukung SEO.

Fields

- meta_title
- meta_description
- canonical_url
- robots
- og_image

---

# 10. Audit

Semua entity memiliki

- created_by
- updated_by
- deleted_by
- created_at
- updated_at
- deleted_at

---

# 11. Future Modules

Direncanakan:

- Marketplace
- Booking
- Tiket
- AI Recommendation
- Nearby Recommendation
- Review Verification
- Notification
- Analytics

---

# 12. Naming Convention

Entity

Singular

Contoh

Place

Category

Gallery

Model

Singular

Table

Plural

Contoh

places

categories

opening_hours

API Endpoint

Plural

/api/places

/api/categories

---

# 13. Out of Scope

Belum termasuk:

- Payment
- Booking Engine
- Chat
- Membership Premium
- Advertisement

Akan dibuat pada dokumen terpisah.

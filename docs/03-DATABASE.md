# DISEKITAR Database Design

**Version:** 1.0
**Status:** Draft
**Last Updated:** 2026-06-29

---

# Overview

Dokumen ini menjelaskan desain database DISEKITAR.

Tujuan utama desain database adalah:

- Mudah dikembangkan.
- Mendukung jutaan data.
- SEO Friendly.
- API Friendly.
- Tidak bergantung pada satu jenis konten.
- Menghindari duplikasi struktur tabel.

---

# Database Philosophy

DISEKITAR menggunakan pendekatan:

> **Core Content Engine**

Semua konten diperlakukan sebagai **Post**.

Hotel, Wisata, Event, Lowongan Kerja, Properti, Kuliner, dan kategori lainnya merupakan pengembangan (extension) dari Post.

Dengan pendekatan ini seluruh sistem memiliki satu pusat data yang konsisten.

---

# High Level ERD

```text
User
│
├── Posts
│      │
│      ├── Category
│      ├── Location
│      ├── Gallery
│      ├── Tags
│      ├── SEO
│      ├── Metadata
│      └── Detail Module
│
├── Review
├── Bookmark
└── Notification
```

---

# Core Tables

## users

Menyimpan seluruh pengguna sistem.

Digunakan oleh:

- Admin
- Editor
- Business Owner
- Visitor (Future)

---

## posts

Merupakan tabel terpenting.

Semua konten berasal dari tabel ini.

Contoh data:

- Hotel
- Wisata
- Kuliner
- Event
- Job
- Properti
- UMKM
- Artikel

Field utama:

```text
id

author_id

category_id

location_id

title

slug

excerpt

content

status

published_at

created_at

updated_at
```

---

# Categories

```text
categories

id

parent_id

name

slug

icon

description

status
```

Nested category.

Contoh:

```text
Hotel

Hotel Bintang 5

Hotel Budget

Villa

Resort
```

---

# Locations

Location merupakan entity utama.

```text
locations

id

parent_id

type

name

slug

latitude

longitude

postal_code
```

Hierarchy

```text
Province

↓

City

↓

District

↓

Village
```

Seluruh module menggunakan tabel Location yang sama.

---

# Galleries

```text
galleries

id

disk

path

url

alt

caption

width

height

mime_type

size
```

---

# Post Gallery

Relasi many-to-many.

```text
post_gallery

post_id

gallery_id

sort_order
```

---

# Tags

```text
tags

id

name

slug
```

---

# Post Tags

```text
post_tag

post_id

tag_id
```

---

# SEO

SEO dipisahkan agar mudah diperluas.

```text
seo

id

post_id

meta_title

meta_description

canonical_url

robots

schema_json
```

---

# Metadata

Metadata bersifat fleksibel.

```text
post_meta

id

post_id

key

value
```

Contoh

```text
price

opening_hours

phone

website

wifi

parking
```

Metadata hanya digunakan untuk data sederhana.

Jangan menyimpan data relational di tabel ini.

---

# Detail Modules

Apabila sebuah kategori membutuhkan field khusus, gunakan extension table.

Contoh Hotel.

```text
hotel_details

post_id

star

check_in

check_out

room_count

pet_allowed
```

---

Contoh Event.

```text
event_details

post_id

start_date

end_date

organizer

ticket_url
```

---

Contoh Job.

```text
job_details

post_id

salary_min

salary_max

company

employment_type

deadline
```

---

Dengan pendekatan ini:

```text
posts

↓

hotel_details
```

lebih baik dibanding:

```text
hotels
```

karena tidak menduplikasi struktur dasar.

---

# Reviews

```text
reviews

id

post_id

user_id

rating

comment

created_at
```

---

# Bookmark

```text
bookmarks

id

user_id

post_id
```

---

# Favorites

Future.

User dapat membuat collection.

```text
collections

id

user_id

name
```

---

# Search Index

Tahap awal:

Search langsung ke tabel Post.

Tahap besar:

Gunakan Search Engine.

Database tidak perlu diubah.

---

# Soft Delete

Gunakan Soft Delete pada:

- posts
- galleries
- categories

Tidak digunakan pada tabel pivot.

---

# Audit

Future.

```text
activity_logs

id

user_id

action

table

record_id

old_value

new_value

created_at
```

---

# Naming Convention

Gunakan:

snake_case

Contoh:

```text
published_at

created_by

updated_by
```

Hindari:

```text
PublishDate

PostName
```

---

# Primary Key

Seluruh tabel menggunakan:

```text
id
```

Auto Increment.

UUID dapat dipertimbangkan di masa depan untuk API publik.

---

# Foreign Key

Semua relasi wajib menggunakan foreign key.

Gunakan cascade hanya jika memang aman.

---

# Indexing Strategy

Minimal index:

- slug
- status
- category_id
- location_id
- published_at
- created_at

Tambahkan composite index bila diperlukan.

Contoh:

```text
status

published_at
```

---

# Large Scale Strategy

Database harus siap menangani:

- jutaan post
- jutaan gallery
- jutaan review

Strategi:

- indexing
- eager loading
- pagination
- caching
- search engine

---

# What Should NOT Be Stored

Jangan menyimpan:

- HTML hasil render
- Cache
- Session
- Temporary File
- Generated Thumbnail

Semua itu berada di layer lain.

---

# AI Coding Guidelines

Saat membuat migration:

- Selalu gunakan foreign key.
- Tambahkan index pada field pencarian.
- Gunakan nama tabel jamak.
- Gunakan nama kolom yang konsisten.
- Hindari nullable jika tidak diperlukan.
- Jangan menyimpan informasi yang sama di dua tabel.
- Gunakan extension table jika sebuah kategori membutuhkan field tambahan.
- Jangan membuat tabel baru jika data masih dapat direpresentasikan oleh struktur yang sudah ada.

---

# Database Goals

Desain database dianggap berhasil apabila:

- Mudah dipahami.
- Tidak terjadi duplikasi struktur.
- Mudah menambah kategori baru.
- Tetap cepat meskipun jumlah data sangat besar.
- Mendukung API, Website, Mobile App, dan AI tanpa perubahan besar pada skema database.

# DISEKITAR Architecture

**Version:** 1.0
**Status:** Active
**Last Updated:** 2026-06-29

---

# Overview

Dokumen ini menjelaskan arsitektur teknis DISEKITAR.

Seluruh developer dan AI Coding Agent wajib mengikuti arsitektur ini agar project tetap konsisten, mudah dipelihara, dan dapat berkembang dalam jangka panjang.

---

# Architecture Principles

DISEKITAR dibangun berdasarkan prinsip berikut:

- Modular
- API First
- Domain Driven Structure (Lightweight)
- SOLID
- KISS
- DRY
- Convention over Configuration

Setiap keputusan teknis harus mempertimbangkan:

- Maintainability
- Scalability
- Readability
- Performance

---

# High Level Architecture

```text
                Browser / Mobile App
                         │
                Laravel Route
                         │
                    Controller
                         │
                  Form Request
                         │
                Action / Service
                         │
          ┌──────────────┴──────────────┐
          │                             │
      Eloquent Model              External Service
          │                             │
          └──────────────┬──────────────┘
                         │
                     Database
```

Controller hanya bertugas menerima request dan mengembalikan response.

Business logic tidak boleh berada di Controller.

---

# Project Philosophy

Project dibagi berdasarkan **Domain**, bukan berdasarkan jenis file.

Contoh:

```text
Hotel
Wisata
Kuliner
Job
Event
User
Location
SEO
Gallery
```

Setiap domain bertanggung jawab atas business logic-nya sendiri.

---

# Directory Structure

```text
app/

├── Actions/
├── Console/
├── Enums/
├── Events/
├── Exceptions/
├── Helpers/
├── Http/
│
├── Models/
│
├── Domains/
│   ├── Post/
│   ├── Category/
│   ├── Gallery/
│   ├── Location/
│   ├── Hotel/
│   ├── Tourism/
│   ├── Event/
│   ├── Job/
│   └── Shared/
│
├── Notifications/
├── Policies/
├── Providers/
└── Services/
```

> Domain baru dapat ditambahkan tanpa mengubah domain lain.

---

# Domain Structure

Setiap domain mengikuti struktur yang sama.

Contoh:

```text
Domains/

Post/

├── Actions/
├── DTO/
├── Exceptions/
├── Policies/
├── Services/
├── Validators/
└── README.md
```

Tidak semua folder wajib ada.

Gunakan hanya bila diperlukan.

---

# Request Flow

```text
HTTP Request

↓

Route

↓

Controller

↓

Form Request

↓

Action

↓

Service (Optional)

↓

Model

↓

Database

↓

API Resource / View

↓

HTTP Response
```

---

# Responsibilities

## Controller

Controller bertugas:

- menerima request
- memanggil Action
- mengembalikan response

Controller **tidak boleh**:

- query kompleks
- validasi manual
- business logic
- manipulasi data besar

Target maksimal ±100 baris per controller.

---

## Form Request

Seluruh validasi wajib menggunakan Form Request.

Jangan melakukan validasi di Controller.

---

## Action

Action menangani satu use case.

Contoh:

```text
CreatePostAction

UpdatePostAction

DeletePostAction

PublishPostAction
```

Satu Action = satu tanggung jawab.

---

## Service

Service digunakan bila terdapat business logic yang dipakai lebih dari satu Action.

Contoh:

```text
SeoService

ImageService

SlugService

LocationService

NotificationService
```

---

## Model

Model hanya bertanggung jawab terhadap:

- Relationship
- Scope
- Accessor
- Mutator
- Casting

Hindari business logic kompleks di Model.

---

# Repository Pattern

Repository **tidak digunakan secara default**.

Gunakan Repository hanya apabila:

- menggunakan lebih dari satu data source
- integrasi API eksternal
- caching kompleks
- query sangat kompleks dan dipakai di banyak tempat

Jangan membuat Repository hanya karena mengikuti pola tertentu.

---

# DTO

DTO digunakan bila:

- data berpindah antar layer
- parameter sangat banyak
- business process kompleks

DTO bukan kewajiban.

---

# API First

Seluruh business logic harus dapat digunakan oleh:

- Website
- REST API
- Mobile App
- CLI
- Queue
- Scheduler

Jangan menulis business logic yang hanya bisa digunakan oleh Blade.

---

# Filament Architecture

Filament hanya bertugas sebagai Admin Interface.

Filament Resource tidak boleh menjadi tempat business logic.

Contoh:

```text
Filament Resource

↓

Action

↓

Service

↓

Model
```

---

# Database Layer

Seluruh akses database dilakukan melalui Eloquent.

Gunakan:

- Relationship
- Scope
- Eager Loading
- Query Builder

Hindari Raw SQL kecuali benar-benar diperlukan.

---

# Event Driven

Gunakan Event bila proses dapat dipisahkan.

Contoh:

```text
Post Published

↓

Send Notification

↓

Update Sitemap

↓

Clear Cache

↓

Generate AI Summary
```

Jangan memanggil seluruh proses langsung dari Controller.

---

# Queue

Semua pekerjaan berat harus menggunakan Queue.

Contoh:

- Resize Image
- AI Processing
- Email
- Notification
- Import
- Export

---

# Cache Strategy

Cache digunakan pada:

- Homepage
- Popular Content
- Category
- Sitemap
- Settings

Gunakan cache dengan masa berlaku yang jelas dan lakukan invalidasi saat data berubah.

---

# Image Architecture

Seluruh proses gambar dipusatkan pada Image Service.

Image dapat berasal dari:

- Upload
- URL
- CDN

Storage harus dapat diganti tanpa mengubah business logic.

---

# Search Architecture

Tahap awal:

- Database LIKE

Tahap berikutnya:

- Full Text Search

Tahap besar:

- Meilisearch
- Algolia

Implementasi search harus menggunakan abstraction agar mudah diganti.

---

# Error Handling

Gunakan Exception.

Jangan mengembalikan:

```php
return false;
```

atau

```php
return null;
```

untuk kondisi error yang seharusnya menjadi exception.

---

# Dependency Rules

Allowed

```text
Controller

↓

Action

↓

Service

↓

Model
```

Forbidden

```text
Model

↓

Controller
```

atau

```text
Controller

↓

Controller
```

---

# Scalability Strategy

Saat jumlah konten bertambah hingga jutaan data, arsitektur tetap harus mampu berkembang dengan perubahan minimal.

Pertimbangan jangka panjang:

- Queue
- Cache
- CDN
- Search Engine
- Read Replica Database
- Object Storage

---

# AI Coding Guidelines

AI Coding Agent wajib mengikuti aturan berikut:

- Jangan memindahkan business logic ke Controller.
- Jangan membuat Fat Model.
- Gunakan Action untuk setiap use case.
- Gunakan Service hanya jika logic digunakan kembali.
- Hindari over-engineering.
- Jangan membuat Repository tanpa alasan yang jelas.
- Selalu gunakan Eager Loading jika diperlukan.
- Jangan melakukan query di Blade.
- Ikuti struktur folder yang telah ditentukan.
- Semua kode harus mudah dibaca oleh developer lain.

---

# Architecture Goals

Arsitektur dianggap berhasil apabila:

- Mudah dipahami developer baru.
- Mudah dipahami AI Coding Agent.
- Mudah diuji.
- Mudah dikembangkan.
- Tidak bergantung pada satu frontend.
- Tetap sederhana meskipun fitur bertambah banyak.

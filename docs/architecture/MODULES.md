# MODULES.md

> Project: DISEKITAR
>
> Version: 1.0
>
> Status: Draft

---

# 1. Purpose

Dokumen ini mendefinisikan seluruh module yang terdapat pada project DISEKITAR.

Tujuan:

- Menjadi panduan implementasi backend.
- Menjadi referensi AI Agent.
- Menentukan dependency antar module.
- Menghindari duplikasi logic.

---

# 2. Architecture

Project menggunakan pendekatan:

```text
Modular Monolith
```

Setiap module memiliki:

- Model
- Migration
- Controller
- Service
- Repository (opsional)
- Policy
- Request Validation
- API Resource
- Routes
- Tests

Setiap module berdiri sendiri dan hanya berinteraksi melalui service atau contract.

---

# 3. Module Dependency

```text
Core
│
├── Authentication
├── Authorization
├── User
│
├── Master Data
│   ├── Province
│   ├── City
│   ├── District
│   ├── Village
│   ├── Category
│   ├── Tag
│   └── Facility
│
├── Place
│   ├── Gallery
│   ├── Contact
│   ├── Opening Hour
│   ├── Social Media
│   ├── Review
│   └── Promotion
│
├── Article
├── Event
├── Job
│
├── Search
├── SEO
├── Media
│
└── System
```

Rule:

Module hanya boleh bergantung pada module yang berada di level yang sama atau lebih rendah.

---

# 4. Core Modules

## Authentication

Tanggung Jawab

- Login
- Logout
- Refresh Token
- Current User

Database

- users
- personal_access_tokens

API

```text
POST /auth/login
POST /auth/logout
POST /auth/refresh
GET  /auth/me
```

---

## Authorization

Mengelola:

- Role
- Permission
- Policy

Database

- roles
- permissions
- role_has_permissions
- model_has_roles

---

## User

Mengelola akun pengguna.

Relasi

User

↓

Role

↓

Permission

---

# 5. Master Modules

## Province

Master provinsi.

---

## City

Master kota.

---

## District

Master kecamatan.

---

## Village

Master desa.

---

## Category

Kategori utama.

Hierarchy.

---

## Tag

Keyword.

---

## Facility

Master fasilitas.

---

# 6. Business Modules

## Place

Aggregate Root.

Tanggung Jawab

- CRUD Place
- Publish
- Archive
- Soft Delete

Dependency

- Category
- Location
- Gallery
- Contact
- Facility
- Review

---

## Gallery

Upload gambar.

---

## Contact

Nomor telepon.

WhatsApp.

Email.

Website.

---

## Opening Hour

Jam operasional.

---

## Social Media

Instagram.

Facebook.

TikTok.

YouTube.

---

## Review

Review pengguna.

---

## Promotion

Promo.

---

# 7. Content Modules

## Article

Blog.

Panduan.

News.

---

## Event

Agenda.

Festival.

Pameran.

---

## Job

Lowongan kerja.

---

# 8. SEO Modules

## SEO

Menghasilkan:

- Meta Title
- Meta Description
- Canonical
- Sitemap
- Robots

---

## Search

Pencarian.

Autocomplete.

Suggestion.

Filter.

---

# 9. Media Module

Mengelola:

- Upload
- Resize
- Compression
- Thumbnail

Storage

- Local
- S3 (Future)

---

# 10. System Modules

## Audit Log

Mencatat:

- Login
- Update
- Delete
- Publish

---

## Notification

Email.

Webhook.

Push Notification.

---

## Queue

Background Job.

---

## Cache

Redis.

---

## Scheduler

Cron Job.

---

# 11. Module Ownership

| Module         | Owner    |
| -------------- | -------- |
| Authentication | Core     |
| Authorization  | Core     |
| User           | Core     |
| Province       | Master   |
| City           | Master   |
| District       | Master   |
| Village        | Master   |
| Category       | Master   |
| Tag            | Master   |
| Facility       | Master   |
| Place          | Business |
| Gallery        | Business |
| Contact        | Business |
| Opening Hour   | Business |
| Social Media   | Business |
| Review         | Business |
| Promotion      | Business |
| Article        | Content  |
| Event          | Content  |
| Job            | Content  |
| SEO            | System   |
| Search         | System   |
| Media          | System   |
| Audit          | System   |

---

# 12. Folder Structure

```text
app/
└── Modules/
    ├── Authentication/
    ├── Authorization/
    ├── User/
    ├── Place/
    ├── Category/
    ├── Gallery/
    ├── Contact/
    ├── Facility/
    ├── Review/
    ├── Article/
    ├── Event/
    ├── Job/
    ├── Search/
    ├── SEO/
    ├── Media/
    └── System/
```

Setiap module minimal memiliki:

```text
Place/
├── Controllers/
├── Models/
├── Services/
├── Requests/
├── Resources/
├── Policies/
├── Routes/
├── Tests/
└── Providers/
```

---

# 13. Communication Rules

Module tidak boleh mengakses database module lain secara langsung jika terdapat business logic.

Gunakan:

- Service
- Contract
- Event
- Observer

Hindari dependency melingkar (circular dependency).

---

# 14. Event Driven

Module dapat berkomunikasi menggunakan event.

Contoh:

```text
PlaceCreated
PlaceUpdated
PlacePublished
ArticlePublished
ReviewCreated
JobPublished
```

Listener:

- SEO
- Notification
- Audit
- Search Index

---

# 15. Future Modules

Disiapkan untuk:

- Marketplace
- Booking
- Ticketing
- AI
- Recommendation
- Maps
- Analytics
- Advertisement
- Subscription
- Payment Gateway
- CRM Integration
- Data Import
- Data Sync

---

# 16. Development Rules

Semua module wajib memiliki:

- Migration
- Seeder (jika diperlukan)
- Model
- API Resource
- Request Validation
- Policy
- Service
- Unit Test
- Feature Test
- Documentation

Tidak diperbolehkan menambahkan module baru tanpa memperbarui dokumen ini.

---

# 17. Module Lifecycle

Setiap module mengikuti siklus berikut:

```text
Planning
    ↓
Architecture
    ↓
Database
    ↓
API
    ↓
Business Logic
    ↓
Testing
    ↓
Documentation
    ↓
Deployment
```

Perubahan pada satu module harus mempertimbangkan dampaknya terhadap module lain sebelum diimplementasikan.

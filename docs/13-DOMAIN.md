# DISEKITAR Domain Guide

**Version:** 1.0
**Status:** Active
**Last Updated:** 2026-06-29

---

# Overview

Dokumen ini mendefinisikan pembagian Domain pada platform DISEKITAR.

Domain adalah area bisnis yang memiliki tanggung jawab, data, aturan, dan API sendiri.

Tujuan utama pembagian Domain:

- Memisahkan tanggung jawab.
- Mengurangi coupling.
- Memudahkan pengembangan.
- Memudahkan scaling.
- Mempermudah AI Coding Agent memahami konteks bisnis.

---

# Domain Philosophy

DISEKITAR bukan sekadar CMS.

DISEKITAR adalah kumpulan Domain yang berjalan di atas satu Platform.

Setiap Domain memiliki:

- Business Rules
- Data
- API
- UI
- SEO
- Service
- Validation

Namun seluruh Domain berbagi fondasi yang sama.

---

# Platform Structure

```text
DISEKITAR Platform
│
├── Shared Platform
│
├── CMS
├── User
├── Location
├── Search
├── Media
├── SEO
│
├── Hotel
├── Restaurant
├── Tourism
├── Event
├── Job
├── Property
├── Marketplace
│
└── AI
```

---

# Core Domains

Core Domain adalah fondasi platform.

## CMS

Bertanggung jawab atas:

- Post
- Category
- Content
- Publishing
- Draft
- Revision (Future)

---

## User

Bertanggung jawab atas:

- Authentication
- Authorization
- Profile
- Role
- Permission

---

## Location

Merupakan Domain paling penting.

Bertanggung jawab atas:

- Province
- City
- District
- Coordinate
- Postal Code
- Nearby Logic (Future)

Hampir seluruh Domain bergantung pada Location.

---

## Search

Bertanggung jawab atas:

- Keyword Search
- Filtering
- Sorting
- Search Suggestion
- Search Index (Future)

---

## Media

Bertanggung jawab atas:

- Upload
- Gallery
- Image Optimization
- Thumbnail
- CDN

---

## SEO

Bertanggung jawab atas:

- Meta Title
- Meta Description
- Canonical
- Open Graph
- JSON-LD
- Sitemap
- Robots
- Redirect

Seluruh Domain publik menggunakan Domain SEO.

---

# Business Domains

Business Domain berisi konten yang ditampilkan kepada pengguna.

---

## Hotel

Data:

- Hotel
- Room (Future)
- Facility
- Gallery
- Price (Future)

---

## Restaurant

Data:

- Restaurant
- Menu (Future)
- Cuisine
- Opening Hours

---

## Tourism

Data:

- Destination
- Attraction
- Ticket
- Facility

---

## Event

Data:

- Event
- Organizer
- Schedule
- Registration URL

---

## Job

Data:

- Company
- Vacancy
- Salary
- Employment Type

---

## Property

Data:

- House
- Apartment
- Boarding House
- Land

---

## Marketplace

Data:

- Product
- Store
- Service
- Promotion

---

# Shared Domain

Shared Domain berisi komponen yang digunakan oleh seluruh Domain.

Contoh:

- Address
- Contact
- Gallery
- Slug
- SEO Metadata
- Schedule
- Opening Hours

Jangan menduplikasi struktur yang sama pada setiap Domain.

---

# Domain Dependency

Hubungan antar Domain:

```text
Location
│
├── Hotel
├── Restaurant
├── Tourism
├── Event
├── Job
└── Property

SEO
│
├── Semua Domain Publik

Media
│
├── Semua Domain

User
│
├── Bookmark
├── Review
├── Dashboard
└── Business

CMS
│
└── Seluruh konten berbasis Post
```

Domain tidak boleh saling bergantung secara melingkar (circular dependency).

---

# Ownership

Setiap data hanya memiliki satu Domain Owner.

Contoh:

| Data         | Domain   |
| ------------ | -------- |
| Hotel        | Hotel    |
| Event        | Event    |
| User         | User     |
| Gallery      | Media    |
| SEO Metadata | SEO      |
| Coordinate   | Location |

Domain lain harus menggunakan API atau Service dari Domain Owner.

---

# Reuse Rules

Jika dua Domain membutuhkan fitur yang sama:

- pindahkan ke Shared Domain,
- jangan melakukan copy-paste.

---

# Domain Communication

Komunikasi antar Domain dilakukan melalui:

- Service
- Action
- Event (Jika diperlukan)
- API (Untuk layanan eksternal)

Hindari akses langsung ke implementasi internal Domain lain.

---

# Domain Isolation

Setiap Domain harus:

- mudah diuji,
- mudah dipindahkan,
- mudah dikembangkan.

Perubahan pada satu Domain tidak boleh merusak Domain lain.

---

# Future Domain

Domain yang direncanakan:

- Review
- Bookmark
- Business
- Notification
- Analytics
- Recommendation
- AI Assistant

---

# Domain Evolution

Urutan pengembangan Domain:

```text
Platform

↓

CMS

↓

Location

↓

SEO

↓

Search

↓

Hotel

↓

Restaurant

↓

Tourism

↓

Event

↓

Job

↓

Marketplace

↓

AI
```

Domain baru harus dibangun di atas fondasi yang sudah stabil.

---

# AI Coding Guidelines

Saat menerima tugas:

1. Identifikasi Domain yang terlibat.
2. Gunakan Service atau Action dari Domain tersebut.
3. Hindari mencampur business logic lintas Domain.
4. Gunakan Shared Domain untuk kebutuhan bersama.
5. Jangan menduplikasi data atau logika.

Jika suatu perubahan menyentuh lebih dari satu Domain, dokumentasikan hubungan dan dampaknya.

---

# Domain Checklist

Sebelum membuat Domain baru:

- Apakah benar-benar diperlukan?
- Apakah bisa menjadi bagian dari Domain yang sudah ada?
- Apakah memiliki Business Rules sendiri?
- Apakah memiliki data sendiri?
- Apakah memiliki API sendiri?
- Apakah akan digunakan ulang?

Jika sebagian besar jawabannya "tidak", kemungkinan Domain baru belum diperlukan.

---

# Definition of Done

Sebuah Domain dianggap matang apabila:

- Memiliki tanggung jawab yang jelas.
- Tidak memiliki circular dependency.
- Memiliki API yang konsisten.
- Menggunakan Shared Domain bila memungkinkan.
- Mudah diuji.
- Mudah dipelihara.
- Mendukung pertumbuhan platform dalam jangka panjang.

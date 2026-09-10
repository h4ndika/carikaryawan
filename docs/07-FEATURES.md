# DISEKITAR Features Specification

**Version:** 1.0
**Status:** Active Development
**Last Updated:** 2026-06-29

---

# Overview

Dokumen ini mendefinisikan seluruh fitur yang tersedia dan yang direncanakan pada platform DISEKITAR.

Dokumen ini menjadi acuan utama dalam pengembangan produk agar seluruh fitur memiliki tujuan yang jelas, konsisten, dan sejalan dengan visi platform.

---

# Feature Principles

Seluruh fitur harus memenuhi prinsip berikut:

- Memberikan manfaat nyata bagi pengguna.
- Mendukung penemuan informasi lokal.
- Mudah digunakan.
- Mudah dikembangkan.
- Tidak menambah kompleksitas yang tidak diperlukan.

---

# Feature Status

| Status         | Keterangan             |
| -------------- | ---------------------- |
| 🟢 Available   | Sudah tersedia         |
| 🟡 Development | Sedang dikembangkan    |
| 🔵 Planned     | Sudah direncanakan     |
| ⚪ Future      | Rencana jangka panjang |

---

# Core Platform

Fitur yang menjadi fondasi seluruh sistem.

## User Management

Status: 🔵 Planned

### Features

- Login
- Logout
- Register
- Email Verification
- Password Reset
- Profile Management
- Session Management

---

## Role & Permission

Status: 🔵 Planned

### Features

- Role
- Permission
- Policy
- Multi Role (Future)

---

## Settings

Status: 🔵 Planned

### Features

- General Settings
- SEO Settings
- Mail Settings
- Storage Settings
- Social Media
- Analytics
- Sitemap

---

# Core Content

Merupakan inti dari DISEKITAR.

Semua jenis informasi berasal dari modul ini.

---

## Post

Status: 🔵 Planned

### Features

- CRUD
- Draft
- Published
- Scheduled Publish
- Featured Post
- Sticky Post
- Duplicate Post
- Revision History (Future)
- Preview
- Soft Delete

---

## Lowongan Kerja (Job Board Ecosystem)

Status: 🟢 Available

Modul penayangan dan agregasi informasi lowongan kerja berbasis lokasi.

### Features

- **Multi-Engine Scraping:** Integrasi otomatis ekstraksi data loker terverifikasi dari JobStreet dan Glints Indonesia.
- **Canonical Deduplication:** Fingerprint unik `place_id` lintas platform (`job_` + MD5 hash) untuk mencegah duplikasi listing dari penyedia berbeda.
- **Batch Scraping Multi-Wilayah:** Input multi-keyword, multi-select kota, dan multi-select provinsi dengan otomatisasi penggabungan kabupaten/kota dan pembersihan nama administratif.
- **Auto-Expiration & SEO Compliance:** Masa tayang 21 hari dengan visual closed badge, penonaktifan tombol lamar, dan Schema JSON-LD `validThrough` tanpa penghapusan URL.
- **Text-First Layout:** Penyajian khusus lowongan kerja bebas gambar untuk tampilan profesional yang padat informasi dan tombol CTA lamaran terisolasi.
- **Resilience Engine:** Penanganan 0 lowongan otomatis, auto-rotate token akun Apify saat retry, serta ekstraksi log error konsol langsung ke panel admin.

---

## Category

Status: 🔵 Planned

### Features

- Nested Category
- Icon
- Description
- SEO Metadata
- Ordering

---

## Location

Status: 🔵 Planned

Location merupakan domain utama.

### Features

- Province
- City
- District
- Village (Optional)
- Latitude
- Longitude
- Postal Code

---

## Gallery

Status: 🔵 Planned

### Features

- Multiple Images
- Drag & Drop Upload
- Sort Order
- Alt Text
- Caption
- WebP Conversion
- Image Compression

---

## Tags

Status: ⚪ Future

### Features

- Tag Management
- Related Content
- Trending Tags

---

# Discovery Features

Fitur yang membantu pengguna menemukan informasi.

---

## Search

Status: 🔵 Planned

### Features

- Keyword Search
- Location Filter
- Category Filter
- Sorting
- Search Suggestion
- Recent Search

Future

- Full Text Search
- AI Search

---

## Nearby

Status: ⚪ Future

### Features

- Nearby Hotel
- Nearby Restaurant
- Nearby Event
- Nearby Tourism
- Radius Search

---

## Interactive Map

Status: ⚪ Future

### Features

- Map View
- Marker
- Cluster
- Route
- Nearby Place

---

## Recommendation

Status: ⚪ Future

### Features

- Similar Content
- Nearby Recommendation
- Popular Recommendation
- Personalized Recommendation

---

# Community Features

---

## Review

Status: ⚪ Future

### Features

- Rating
- Comment
- Photo Review
- Like Review

---

## Bookmark

Status: ⚪ Future

### Features

- Save Favorite
- Favorite Collection
- Quick Bookmark

---

## User Profile

Status: ⚪ Future

### Features

- Activity
- Reviews
- Saved Places
- Published Content (Future)

---

# Business Platform

---

## Business Listing

Status: ⚪ Future

### Features

- Business Profile
- Business Verification
- Claim Listing
- Business Dashboard

---

## Premium Listing

Status: ⚪ Future

### Features

- Featured Listing
- Priority Placement
- Highlight Badge

---

## Advertisement

Status: ⚪ Future

### Features

- Banner Ads
- Sponsored Listing
- Sponsored Content

---

# Job Platform

Status: ⚪ Future

### Features

- Job Listing
- Company Profile
- Apply Link
- Bookmark Job
- Job Category
- Employment Type
- Salary Information

---

# Event Platform

Status: ⚪ Future

### Features

- Event Calendar
- Registration Link
- Organizer
- Ticket URL
- Reminder

---

# Property Platform

Status: ⚪ Future

### Features

- House
- Apartment
- Boarding House
- Land
- Commercial Property

---

# Tourism Platform

Status: ⚪ Future

### Features

- Destination
- Tourist Attraction
- Tour Package
- Travel Guide

---

# Marketplace Platform

Status: ⚪ Future

### Features

- Product Listing
- Local Store
- Service Listing
- Checkout Integration

---

# AI Features

---

## AI Content

Status: ⚪ Future

### Features

- Content Draft
- Meta Description Generator
- SEO Title Generator
- Slug Suggestion

---

## AI Search

Status: ⚪ Future

### Features

- Natural Language Search
- Intent Detection
- Related Result

---

## AI Recommendation

Status: ⚪ Future

### Features

- Personalized Content
- Similar Place
- Nearby Suggestion

---

# SEO Features

Status: 🔵 Planned

### Features

- Meta Title
- Meta Description
- Open Graph
- Twitter Card
- Canonical URL
- Breadcrumb
- XML Sitemap
- JSON-LD
- Robots.txt
- Redirect Management

---

# Media Features

Status: 🔵 Planned

### Features

- Image Optimization
- Responsive Image
- CDN Ready
- Lazy Loading
- Image Metadata

---

# Notification

Status: ⚪ Future

### Features

- Email Notification
- Push Notification
- WhatsApp Notification
- In-App Notification

---

# API Features

Status: 🔵 Planned

### Features

- REST API
- API Authentication
- Versioning
- Pagination
- Filtering
- Sorting
- Rate Limiting

---

# Administration

---

## Dashboard

Status: 🔵 Planned

### Features

- Statistics
- Traffic Summary
- Content Summary
- Recent Activity
- Quick Action

---

## Audit Log

Status: ⚪ Future

### Features

- Login History
- Content History
- User Activity
- System Activity

---

## Scheduler

Status: ⚪ Future

### Features

- Publish Scheduler
- Cache Scheduler
- Sitemap Generator
- SEO Validation

---

# Integrations

Status: ⚪ Future

### Supported Services

- Google Maps
- OpenStreetMap
- Cloudflare R2
- AWS S3
- Firebase
- Google Analytics
- Google Search Console
- WhatsApp Business
- Midtrans
- DOKU
- Duitku
- Xendit

---

# Performance Features

Status: 🔵 Planned

### Features

- Cache
- Queue
- Lazy Loading
- CDN Support
- Image Optimization
- Database Optimization

---

# Security Features

Status: 🔵 Planned

### Features

- CSRF Protection
- XSS Protection
- SQL Injection Protection
- Role-Based Access Control
- Audit Trail
- Rate Limiting

---

# Feature Dependencies

Beberapa fitur bergantung pada fitur lain.

| Feature           | Depends On       |
| ----------------- | ---------------- |
| Review            | User, Post       |
| Bookmark          | User, Post       |
| Nearby            | Location         |
| Recommendation    | Post, Location   |
| Business Listing  | User             |
| Premium Listing   | Business Listing |
| AI Recommendation | Search, Location |
| AI Search         | Search Engine    |

---

# Minimum Viable Product (MVP)

Fitur minimum yang harus tersedia sebelum peluncuran pertama:

- Authentication
- User Management
- Post Management
- Category Management
- Location Management
- Gallery
- SEO Engine
- Search
- Public Website
- REST API
- Admin Dashboard

---

# Long-Term Vision

DISEKITAR akan berkembang dari:

```
Website Informasi

↓

Local Discovery Platform

↓

Business Platform

↓

Location Intelligence Platform

↓

Digital Ecosystem
```

---

# AI Coding Guidelines

Saat mengembangkan fitur:

- Jangan membuat fitur di luar dokumen ini tanpa persetujuan.
- Gunakan kembali komponen yang sudah ada.
- Perhatikan dependency antar fitur.
- Hindari implementasi yang terlalu kompleks.
- Pastikan setiap fitur mendukung API First dan SEO First.
- Dokumentasikan perubahan jika menambahkan fitur baru.

---

# Definition of Done

Sebuah fitur dianggap selesai apabila:

- Berfungsi sesuai kebutuhan.
- Memiliki validasi.
- Memiliki authorization bila diperlukan.
- Mengikuti Coding Standard.
- Mengikuti Architecture Guide.
- Mendukung REST API.
- Mendukung SEO (untuk halaman publik).
- Telah diuji.
- Didokumentasikan.

# DISEKITAR Deployment & Operations Guide

**Version:** 1.0
**Status:** Active
**Last Updated:** 2026-06-29

---

# Overview

Dokumen ini menjelaskan standar deployment, operasi, monitoring, dan pemeliharaan platform DISEKITAR.

Tujuan utama:

- Deployment yang konsisten.
- Downtime seminimal mungkin.
- Mudah di-scale.
- Mudah dipulihkan jika terjadi kegagalan.
- Aman untuk production.

---

# Deployment Philosophy

DISEKITAR menggunakan prinsip:

> **Automated, Repeatable, and Safe Deployment**

Deployment harus:

- dapat diulang,
- terdokumentasi,
- minim intervensi manual,
- mudah di-rollback.

---

# Cloud-Native Ready Philosophy

DISEKITAR dirancang dengan prinsip:

> **Build Small, Scale Big**

Meskipun deployment awal dapat menggunakan satu VPS, seluruh arsitektur harus siap berkembang menjadi platform berskala besar tanpa perubahan signifikan pada kode aplikasi.

Developer dan AI Coding Agent **tidak boleh membuat asumsi bahwa aplikasi hanya akan berjalan pada satu server**.

Seluruh implementasi harus mempertimbangkan kemungkinan horizontal scaling di masa depan.

---

## Design Principles

Seluruh komponen aplikasi harus memenuhi prinsip berikut:

- Stateless Application
- Externalized Configuration
- Shared-Nothing Architecture
- Horizontal Scaling Ready
- Infrastructure Agnostic

---

## Stateless Application

Application Server tidak boleh menyimpan data penting secara lokal.

Contoh yang harus dihindari:

- Session berbasis file
- Cache berbasis file
- Upload yang hanya tersimpan di local disk
- Temporary data yang diperlukan server lain

Application harus dapat dipindahkan ke server lain tanpa kehilangan fungsi.

---

## Storage Strategy

Pisahkan seluruh penyimpanan berdasarkan tanggung jawab.

| Data           | Target                       |
| -------------- | ---------------------------- |
| Database       | MySQL                        |
| Session        | Redis                        |
| Cache          | Redis                        |
| Queue          | Redis                        |
| Uploaded Media | Cloudflare R2 / Amazon S3    |
| Log            | Server / Centralized Logging |

---

## Local Storage Policy

Local storage hanya digunakan untuk:

- cache sementara
- temporary upload
- build asset

Data penting tidak boleh hanya berada pada local disk.

---

## Session Strategy

Gunakan Redis sebagai session driver.

Hindari:

```env
SESSION_DRIVER=file
```

Gunakan:

```env
SESSION_DRIVER=redis
```

---

## Cache Strategy

Gunakan Redis.

Hindari cache berbasis file pada production.

```env
CACHE_STORE=redis
```

---

## Queue Strategy

Gunakan Redis Queue.

Semua proses berat harus dipindahkan ke Queue.

Contoh:

- Email
- Image Processing
- AI Generation
- Import
- Export
- Notification

---

## File Storage Strategy

Seluruh media harus menggunakan abstraction Laravel Storage.

Jangan menggunakan:

```php
move_uploaded_file(...)
```

atau path absolut.

Gunakan:

```php
Storage::disk(...)
```

Dengan demikian perpindahan dari Local → R2 → S3 tidak memerlukan perubahan business logic.

---

## CDN Ready

Asset publik harus dapat dipindahkan ke CDN.

Contoh:

- gambar
- thumbnail
- javascript
- css

Gunakan URL yang berasal dari konfigurasi.

---

## Database Strategy

Tahap awal:

```
Laravel
↓

MySQL
```

Tahap berikutnya:

```
Laravel

↓

Primary Database

↓

Read Replica
```

Kode aplikasi tidak boleh bergantung pada satu koneksi database saja.

---

## Reverse Proxy Ready

Aplikasi harus dapat berjalan di belakang:

- Nginx
- Cloudflare
- Load Balancer

Seluruh URL harus menggunakan konfigurasi Laravel, bukan hardcode.

---

## Twelve-Factor App

Sebisa mungkin mengikuti prinsip Twelve-Factor App.

Minimal:

- Config melalui environment
- Stateless
- Dependency melalui Composer
- Log ke stdout atau storage terpisah
- Build dan Release dipisahkan

---

## Horizontal Scaling

Aplikasi harus tetap berjalan ketika terdapat lebih dari satu Application Server.

Contoh:

```
           Load Balancer

          /             \

Laravel App 1     Laravel App 2

        \           /

          Redis

            |

          MySQL
```

Tidak boleh ada fitur yang hanya berfungsi pada satu server.

---

## AI Coding Guidelines

Saat membuat fitur baru:

- Jangan menyimpan file penting di local storage.
- Gunakan Laravel Storage.
- Gunakan Redis untuk cache dan session.
- Jangan membuat business logic yang bergantung pada satu server.
- Gunakan environment variable untuk seluruh konfigurasi.
- Pastikan aplikasi tetap berjalan pada multi-server environment.

# Environment

Minimal terdapat tiga environment.

## Development

Digunakan untuk pengembangan lokal.

Karakteristik:

- Debug aktif.
- Fake data diperbolehkan.
- Mail menggunakan mail catcher.
- Logging verbose.

---

## Staging

Digunakan untuk QA dan pengujian.

Karakteristik:

- Mirip production.
- Data dapat berupa salinan anonim dari production.
- Digunakan untuk validasi sebelum rilis.

---

## Production

Digunakan oleh pengguna akhir.

Karakteristik:

- Debug nonaktif.
- Optimasi aktif.
- Monitoring aktif.
- Backup aktif.

---

# Infrastructure

Target arsitektur deployment.

```text
                 Internet
                     │
               Cloudflare CDN
                     │
              Reverse Proxy (Nginx)
                     │
          ┌──────────┴──────────┐
          │                     │
      Laravel App          Queue Worker
          │                     │
          └──────────┬──────────┘
                     │
                  Redis
                     │
                 MySQL Server
                     │
              Object Storage
          (Cloudflare R2 / S3)
```

Arsitektur dapat dimulai dari satu server dan berkembang secara bertahap.

---

# Application Server

Standar awal:

- Linux
- PHP 8.3+
- Nginx
- MySQL
- Redis
- Supervisor
- Cron

---

# Storage

Pisahkan storage menjadi:

- Database
- Media
- Cache
- Log

Target jangka panjang:

Media menggunakan Object Storage (Cloudflare R2 atau Amazon S3).

---

# Configuration

Semua konfigurasi menggunakan:

- `.env`
- `config/*`

Jangan pernah:

- menyimpan credential di source code,
- melakukan hardcode URL production,
- melakukan hardcode API Key.

---

# Deployment Flow

Urutan deployment:

```text
Push Repository

↓

CI Pipeline

↓

Run Tests

↓

Build Assets

↓

Deploy

↓

Run Migration

↓

Clear & Rebuild Cache

↓

Restart Queue

↓

Health Check

↓

Deployment Complete
```

Deployment harus dihentikan jika salah satu tahap gagal.

---

# Release Strategy

Gunakan strategi:

- Small Release
- Frequent Release

Hindari deployment yang menggabungkan terlalu banyak perubahan sekaligus.

---

# Database Migration

Aturan:

- Migration harus dapat dijalankan berkali-kali tanpa konflik.
- Hindari migration yang menyebabkan downtime panjang.
- Gunakan migration bertahap untuk perubahan besar.

---

# Rollback Strategy

Setiap deployment harus memiliki prosedur rollback.

Rollback minimal mencakup:

- Source Code
- Database Migration (jika memungkinkan)
- Configuration
- Asset

Rollback harus terdokumentasi dan diuji.

---

# Queue

Semua Queue Worker harus:

- otomatis berjalan,
- otomatis restart jika gagal,
- dimonitor.

Queue tidak boleh bergantung pada proses web.

---

# Scheduler

Gunakan Laravel Scheduler.

Contoh tugas:

- Sitemap Generation
- Cache Refresh
- SEO Validation
- Publish Scheduled Content
- Cleanup Temporary File

---

# Cache

Cache harus dapat dibersihkan tanpa memengaruhi data utama.

Gunakan:

- Redis
- Laravel Cache

Hindari cache yang sulit diinvalidasi.

---

# Logging

Pisahkan log berdasarkan jenis.

Contoh:

- Application
- Queue
- Scheduler
- Security
- API

Retensi log harus disesuaikan dengan kebutuhan.

---

# Monitoring

Minimal memantau:

- Uptime
- Error Rate
- Response Time
- Queue
- Disk Usage
- Memory
- CPU
- Database Connection

---

# Health Check

Sediakan endpoint health check.

Contoh:

```text
/health
```

Minimal memeriksa:

- Database
- Cache
- Queue
- Storage

---

# Backup Strategy

Backup meliputi:

- Database
- Uploaded Media
- Configuration

Frekuensi:

- Database: Harian
- Media: Berkala
- Konfigurasi: Setiap perubahan

Backup harus diuji proses pemulihannya.

---

# Disaster Recovery

Dokumentasikan langkah pemulihan apabila terjadi:

- Server gagal
- Database rusak
- Storage gagal
- Deployment gagal

Target utama:

- Meminimalkan kehilangan data.
- Mempercepat pemulihan layanan.

---

# Security

Server production harus:

- HTTPS
- Firewall
- SSH Key Authentication
- Fail2Ban (Opsional)
- Security Update Berkala

Jangan membuka port yang tidak digunakan.

---

# Performance

Sebelum production:

- Optimize Autoload
- Cache Config
- Cache Route
- Cache View
- Build Frontend Assets

Gunakan:

```bash
php artisan optimize
```

setelah deployment bila sesuai dengan proses rilis.

---

# Scaling Strategy

Tahap 1

```text
Single Server
```

Tahap 2

```text
Web + Database
```

Tahap 3

```text
Multiple Web Server
```

Tahap 4

```text
Load Balancer
```

Tahap 5

```text
CDN + Object Storage
```

Tahap 6

```text
Read Replica Database
```

Arsitektur harus mampu berkembang tanpa perubahan besar pada kode aplikasi.

---

# Domain Strategy

Contoh:

```text
disekitar.com

hotel.disekitar.com

kuliner.disekitar.com

event.disekitar.com

loker.disekitar.com

api.disekitar.com

admin.disekitar.com
```

Semua subdomain berbagi platform yang sama.

---

# CI/CD

Pipeline minimal mencakup:

- Install Dependency
- Static Analysis (Opsional)
- Test
- Build
- Deploy
- Health Check

Deployment manual hanya dilakukan dalam kondisi darurat.

---

# Secrets Management

Semua secret:

- API Key
- Database Password
- SMTP Credential
- Storage Key

harus disimpan di environment, bukan di repository.

---

# Maintenance Mode

Gunakan maintenance mode hanya bila diperlukan.

Usahakan deployment dilakukan tanpa downtime.

---

# AI Coding Guidelines

Saat membuat deployment script:

- Jangan menyimpan credential.
- Gunakan environment variable.
- Pastikan proses dapat diulang.
- Tambahkan health check setelah deployment.
- Hindari langkah manual yang tidak terdokumentasi.
- Pastikan deployment dapat di-rollback.

---

# Deployment Checklist

Sebelum release:

- Test berhasil.
- Migration telah diperiksa.
- Backup tersedia.
- Environment benar.
- Queue aktif.
- Cache sesuai.
- Monitoring aktif.
- Health check berhasil.

---

# Definition of Done

Deployment dianggap berhasil apabila:

- Aplikasi dapat diakses.
- Tidak ada error kritis.
- Queue berjalan.
- Scheduler aktif.
- Monitoring normal.
- Backup tersedia.
- Health check berhasil.
- Performa sesuai target.
- Seluruh layanan utama berfungsi dengan baik.

# Production Readiness Checklist

Sebelum melakukan deployment ke Production, pastikan:

## Infrastructure

- [ ] HTTPS aktif
- [ ] SSL valid
- [ ] Firewall aktif
- [ ] Backup berhasil
- [ ] Monitoring aktif

## Application

- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] Queue berjalan
- [ ] Scheduler aktif
- [ ] Cache telah dioptimalkan
- [ ] Config cache dibuat
- [ ] Route cache dibuat
- [ ] View cache dibuat

## Database

- [ ] Backup database
- [ ] Migration telah diuji di Staging
- [ ] Tidak ada migration yang berpotensi lock table terlalu lama

## SEO

- [ ] Sitemap diperbarui
- [ ] Robots.txt sesuai
- [ ] Canonical valid

## Performance

- [ ] Asset telah dibuild
- [ ] Gambar teroptimasi
- [ ] Cache warming selesai

## Security

- [ ] Secret tidak berada di repository
- [ ] API Key menggunakan Environment Variable
- [ ] Tidak ada endpoint debug yang aktif

## Monitoring

- [ ] Health Check berhasil
- [ ] Error Log bersih
- [ ] Queue tidak menumpuk

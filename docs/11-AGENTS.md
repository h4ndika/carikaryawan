# DISEKITAR AI Agent Guide

**Version:** 1.0
**Status:** Active
**Last Updated:** 2026-06-29

---

# Purpose

Dokumen ini berisi aturan kerja untuk seluruh AI Coding Agent yang digunakan pada proyek DISEKITAR.

Contoh AI Agent:

- Antigravity
- Cursor
- Claude Code
- GitHub Copilot
- Windsurf
- ChatGPT Coding Agent
- AI Agent lainnya

Seluruh AI wajib mengikuti dokumen ini sebelum membuat perubahan pada project.

---

# Mission

Misi AI adalah:

> Membantu mengembangkan DISEKITAR dengan menghasilkan kode yang sederhana, konsisten, aman, mudah dipelihara, dan sesuai arsitektur proyek.

AI bukan hanya bertugas membuat kode yang berjalan, tetapi juga menjaga kualitas jangka panjang.

---

# Read Documentation First

Sebelum membuat perubahan apa pun, AI harus memahami dokumentasi proyek.

Urutan prioritas:

```text
00-VISION.md

↓

01-ROADMAP.md

↓

02-ARCHITECTURE.md

↓

03-DATABASE.md

↓

04-API.md

↓

05-CODING-STANDARD.md

↓

06-SEO.md

↓

07-FEATURES.md

↓

08-UI-UX.md

↓

09-DEPLOYMENT.md

↓

10-CONTRIBUTING.md
```

Jika terdapat konflik, gunakan dokumen dengan prioritas lebih tinggi.

---

# Core Principles

AI wajib mematuhi prinsip berikut:

- API First
- SEO First
- Mobile First
- Cloud-Native Ready
- Documentation Driven Development
- Simplicity First
- Reusability First

---

# Before Writing Code

AI harus memahami:

- tujuan bisnis,
- tujuan fitur,
- dampak terhadap sistem,
- dependency,
- dokumentasi terkait.

Jangan langsung membuat kode.

---

# Decision Priority

Jika terdapat beberapa solusi yang valid, gunakan urutan berikut:

1. Solusi paling sederhana.
2. Solusi paling mudah dipelihara.
3. Solusi paling konsisten.
4. Solusi paling reusable.
5. Solusi dengan dependency paling sedikit.

Jangan memilih solusi hanya karena lebih kompleks atau lebih modern.

---

# Architecture Rules

AI tidak boleh:

- mengubah arsitektur tanpa alasan kuat,
- membuat pola baru yang bertentangan dengan dokumentasi,
- membuat duplicate business logic.

Jika perubahan arsitektur diperlukan, jelaskan alasannya terlebih dahulu.

---

# Code Generation Rules

Saat menghasilkan kode:

- Gunakan Laravel Convention.
- Gunakan PHP 8.3+.
- Gunakan Strict Types.
- Gunakan Dependency Injection.
- Gunakan Action untuk setiap use case.
- Gunakan Service untuk logika reusable.
- Gunakan API Resource.
- Gunakan Form Request.
- Gunakan Eloquent sebagai default.

---

# Database Rules

AI tidak boleh:

- membuat tabel baru tanpa kebutuhan yang jelas,
- menduplikasi struktur data,
- menghilangkan foreign key tanpa alasan.

Gunakan extension table bila kategori membutuhkan atribut khusus.

---

# API Rules

Semua endpoint harus:

- RESTful,
- konsisten,
- menggunakan API Resource,
- mengikuti format response yang telah ditentukan.

Jangan membuat endpoint seperti:

```text
/getPosts

/savePost

/updateHotel
```

---

# UI Rules

Gunakan:

- Design System,
- Base Component,
- Card Driven Interface.

Jangan membuat komponen baru jika sudah ada komponen yang setara.

---

# SEO Rules

Halaman publik wajib memiliki:

- SEO Title
- Meta Description
- Canonical
- Open Graph
- JSON-LD
- Breadcrumb

SEO merupakan bagian dari Definition of Done.

---

# Performance Rules

Selalu perhatikan:

- eager loading,
- pagination,
- indexing,
- cache,
- lazy loading.

Hindari:

- query di dalam loop,
- N+1 Query,
- query yang tidak diperlukan.

---

# Security Rules

Selalu gunakan:

- Validation
- Authorization
- Policy
- Escaping Output
- Rate Limiting

Jangan mempercayai input pengguna.

---

# Documentation Rules

Jika perubahan memengaruhi:

- Database
- API
- Architecture
- Deployment
- Feature

AI harus memperbarui dokumentasi terkait.

Kode dan dokumentasi harus selalu sinkron.

---

# Refactoring Rules

Refactoring diperbolehkan apabila:

- meningkatkan keterbacaan,
- mengurangi duplikasi,
- meningkatkan maintainability.

Jangan mengubah perilaku sistem tanpa kebutuhan.

---

# Package Rules

Sebelum menambahkan dependency:

Pastikan:

- benar-benar diperlukan,
- aktif dikembangkan,
- memiliki dokumentasi baik,
- memiliki lisensi yang sesuai.

Prioritaskan fitur bawaan Laravel.

---

# Error Handling

Gunakan:

- Exception
- Validation
- Logging

Jangan:

- menyembunyikan error,
- mengembalikan null tanpa alasan,
- menggunakan silent failure.

---

# Testing

Business logic penting harus dapat diuji.

Prioritaskan:

- Feature Test
- Unit Test

Jika membuat fitur besar, sertakan rekomendasi skenario pengujian.

---

# Documentation Update

Jika AI:

- membuat module baru,
- mengubah API,
- mengubah database,
- mengubah deployment,

maka dokumentasi harus ikut diperbarui.

---

# When AI Is Unsure

Jika kebutuhan tidak jelas:

AI harus:

- mengajukan pertanyaan,
- menjelaskan asumsi,
- tidak menebak kebutuhan bisnis.

Lebih baik meminta klarifikasi daripada menghasilkan implementasi yang salah.

---

# What AI Must NOT Do

AI tidak boleh:

- membuat over-engineering,
- membuat helper global tanpa alasan,
- membuat business logic di Controller,
- membuat query di Blade,
- menggunakan raw SQL tanpa alasan,
- mengubah struktur project tanpa persetujuan,
- menghapus dokumentasi.

---

# Preferred Workflow

Urutan kerja AI:

```text
Read Documentation

↓

Understand Context

↓

Analyze Existing Code

↓

Design Solution

↓

Implement

↓

Test

↓

Update Documentation

↓

Summarize Changes
```

---

# Response Style

Saat memberikan hasil pekerjaan, AI sebaiknya menjelaskan:

- apa yang diubah,
- mengapa diubah,
- dampaknya,
- langkah berikutnya (jika ada).

Hindari penjelasan yang terlalu panjang bila perubahan sederhana.

---

# Escalation

AI harus meminta konfirmasi sebelum:

- mengubah arsitektur,
- menghapus data,
- menghapus module,
- mengganti dependency utama,
- membuat breaking change,
- mengubah struktur database secara signifikan.

---

# Definition of Success

Sebuah tugas dianggap berhasil apabila:

- memenuhi kebutuhan bisnis,
- mengikuti dokumentasi proyek,
- menghasilkan kode yang bersih,
- mudah dipelihara,
- tidak menambah technical debt,
- siap dikembangkan di masa depan.

---

# AI Agent Oath

Sebagai AI Coding Agent DISEKITAR, saya akan:

- Mengutamakan kesederhanaan.
- Mengutamakan konsistensi.
- Mengikuti dokumentasi proyek.
- Tidak membuat perubahan besar tanpa alasan yang jelas.
- Menjaga kualitas kode dan arsitektur.
- Mengutamakan maintainability dibanding kompleksitas.
- Membantu membangun platform yang dapat berkembang dalam jangka panjang.

# DISEKITAR Contributing Guide

**Version:** 1.0
**Status:** Active
**Last Updated:** 2026-06-29

---

# Overview

Selamat datang di DISEKITAR.

Dokumen ini menjelaskan standar kontribusi terhadap project, baik oleh developer manusia maupun AI Coding Agent.

Tujuan utama:

- Menjaga kualitas kode.
- Menjaga konsistensi arsitektur.
- Mengurangi technical debt.
- Memastikan setiap perubahan terdokumentasi.

---

# Core Principles

Sebelum membuat perubahan apa pun, pahami prinsip berikut:

- API First
- SEO First
- Mobile First
- Cloud-Native Ready
- Simplicity First
- Reusability First

Jangan membuat solusi yang bertentangan dengan prinsip di atas.

---

# Before You Start

Sebelum mulai mengerjakan fitur:

- Baca `00-VISION.md`
- Baca `01-ROADMAP.md`
- Baca `02-ARCHITECTURE.md`
- Baca `03-DATABASE.md`
- Baca `04-API.md`
- Baca `05-CODING-STANDARD.md`

Pastikan memahami konteks sebelum menulis kode.

---

# Development Workflow

Urutan kerja yang direkomendasikan:

```text
Issue

↓

Discussion

↓

Design

↓

Implementation

↓

Testing

↓

Documentation

↓

Pull Request

↓

Code Review

↓

Merge
```

Jangan langsung menulis kode tanpa memahami kebutuhan bisnis.

---

# Branch Strategy

Gunakan branch yang jelas.

Contoh:

```text
main
develop

feature/post-management
feature/gallery-upload

fix/slug-duplicate

hotfix/api-timeout

refactor/location-service
```

---

# Commit Standard

Gunakan Conventional Commit.

Contoh:

```text
feat: add nearby search

fix: resolve slug duplication

docs: update deployment guide

refactor: simplify image service

test: add post feature test

chore: update dependencies
```

Satu commit sebaiknya hanya memiliki satu tujuan.

---

# Pull Request

Setiap Pull Request harus menjelaskan:

- Apa yang diubah.
- Mengapa perubahan diperlukan.
- Dampak terhadap fitur lain.
- Cara melakukan pengujian.

Jika mengubah UI, sertakan tangkapan layar.

---

# Code Review

Reviewer harus memeriksa:

- Arsitektur
- Kualitas kode
- Performa
- Keamanan
- Konsistensi
- Dokumentasi

Review bukan hanya mencari bug, tetapi juga meningkatkan kualitas solusi.

---

# Documentation

Jika perubahan memengaruhi:

- Arsitektur
- Database
- API
- Deployment
- Feature

maka dokumentasi terkait harus diperbarui.

Kode dan dokumentasi tidak boleh berbeda.

---

# Testing

Sebelum mengajukan Pull Request:

- Jalankan test yang relevan.
- Pastikan fitur berjalan sesuai kebutuhan.
- Periksa kemungkinan dampak terhadap fitur lain.

Jangan menggabungkan kode yang belum diuji.

---

# Refactoring

Refactoring diperbolehkan apabila:

- meningkatkan keterbacaan,
- mengurangi duplikasi,
- meningkatkan maintainability.

Jangan mengubah perilaku sistem tanpa kebutuhan bisnis yang jelas.

---

# Dependency Management

Sebelum menambahkan package baru:

Pastikan:

- benar-benar diperlukan,
- masih aktif dikembangkan,
- memiliki dokumentasi yang baik,
- memiliki lisensi yang sesuai.

Prioritaskan fitur bawaan Laravel dibanding package tambahan.

---

# Backward Compatibility

Hindari breaking change.

Jika perubahan tidak dapat dihindari:

- dokumentasikan,
- sediakan migration path,
- gunakan versioning bila diperlukan.

---

# Performance

Saat membuat fitur baru:

- hindari N+1 Query,
- gunakan eager loading,
- gunakan pagination,
- gunakan cache bila diperlukan.

Optimalkan hanya setelah mengukur.

---

# Security

Selalu perhatikan:

- Authentication
- Authorization
- Validation
- Escaping Output
- Rate Limiting

Jangan mempercayai input pengguna.

---

# Database Changes

Setiap perubahan database harus:

- menggunakan migration,
- memiliki foreign key bila sesuai,
- memiliki index bila diperlukan,
- tidak merusak data yang ada.

Migration harus dapat dijalankan pada environment baru.

---

# API Changes

Jika mengubah API:

- perbarui dokumentasi,
- pertahankan konsistensi response,
- hindari breaking change.

---

# UI Changes

Saat mengubah UI:

- gunakan Design System,
- pertahankan konsistensi,
- uji pada mobile dan desktop.

---

# Feature Request

Sebelum menambahkan fitur baru, tanyakan:

- Apakah sesuai dengan visi DISEKITAR?
- Apakah benar-benar dibutuhkan?
- Apakah dapat digunakan ulang?
- Apakah menambah kompleksitas?

Jika jawabannya meragukan, diskusikan terlebih dahulu.

---

# Bug Report

Laporan bug minimal berisi:

- Ringkasan masalah
- Langkah reproduksi
- Hasil yang diharapkan
- Hasil yang terjadi
- Screenshot (jika ada)
- Environment

---

# Issue Labels

Gunakan label yang konsisten.

Contoh:

- feature
- bug
- enhancement
- documentation
- performance
- security
- refactor
- ui
- api
- database

---

# AI Coding Agent Guidelines

AI Coding Agent wajib:

- Membaca dokumentasi sebelum membuat kode.
- Tidak membuat struktur baru tanpa alasan.
- Mengikuti Coding Standard.
- Menggunakan komponen yang sudah ada.
- Menjelaskan keputusan arsitektur jika membuat perubahan besar.
- Tidak menambahkan dependency tanpa alasan yang jelas.
- Memperbarui dokumentasi jika perubahan memengaruhi arsitektur atau perilaku sistem.

Jika terdapat konflik antar dokumen, gunakan prioritas berikut:

1. `00-VISION.md`
2. `02-ARCHITECTURE.md`
3. `03-DATABASE.md`
4. `04-API.md`
5. `05-CODING-STANDARD.md`
6. Dokumen lainnya

---

# Decision Making

Jika terdapat beberapa solusi yang valid:

Pilih solusi yang:

- lebih sederhana,
- lebih mudah dipahami,
- lebih mudah dipelihara,
- lebih mudah diuji,
- lebih mudah dikembangkan di masa depan.

Jangan memilih solusi hanya karena lebih "canggih".

---

# Documentation First

Perubahan besar sebaiknya diawali dengan pembaruan dokumentasi.

Urutan yang disarankan:

```text
Update Documentation

↓

Diskusi

↓

Implementasi

↓

Testing

↓

Merge
```

Dengan cara ini seluruh tim memiliki pemahaman yang sama sebelum kode ditulis.

---

# Quality Checklist

Sebelum melakukan merge, pastikan:

- [ ] Mengikuti Coding Standard.
- [ ] Mengikuti Architecture Guide.
- [ ] Tidak ada duplikasi kode.
- [ ] Tidak ada N+1 Query.
- [ ] Validasi telah diterapkan.
- [ ] Authorization telah diterapkan jika diperlukan.
- [ ] API tetap konsisten.
- [ ] Dokumentasi telah diperbarui jika diperlukan.
- [ ] Tidak menambah technical debt secara signifikan.

---

# Definition of Done

Sebuah kontribusi dianggap selesai apabila:

- Masalah yang dituju telah terselesaikan.
- Kode mengikuti seluruh standar proyek.
- Dokumentasi telah diperbarui.
- Tidak merusak fitur lain.
- Mudah dipahami developer berikutnya.
- Siap dipelihara dalam jangka panjang.

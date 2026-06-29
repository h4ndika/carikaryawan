# DISEKITAR AI Code Review Prompt

Version: 1.0

---

# Purpose

Dokumen ini digunakan ketika AI melakukan **Code Review** terhadap implementasi yang telah selesai.

Tujuan utama:

- Menemukan bug.
- Menemukan code smell.
- Menemukan technical debt.
- Memastikan implementasi sesuai standar DISEKITAR.
- Memberikan rekomendasi sebelum kode di-merge.

AI harus bertindak sebagai **Senior Software Engineer** yang melakukan Pull Request Review.

---

# Your Role

Anda adalah Senior Software Engineer dan Code Reviewer.

Tugas Anda adalah:

- Mengaudit implementasi.
- Memberikan masukan.
- Menemukan potensi masalah.
- Memastikan kualitas kode.

Jangan langsung mengubah kode.

Lakukan analisis terlebih dahulu.

---

# Review Workflow

Ikuti langkah berikut.

```text
Read Task
      │
      ▼
Read Related Documents
      │
      ▼
Review Code
      │
      ▼
Find Issues
      │
      ▼
Categorize Issues
      │
      ▼
Recommend Improvements
      │
      ▼
Wait for Approval
      │
      ▼
Fix (Jika Diminta)
```

---

# Step 1 — Read Task

Buka:

```text
docs/tasks/items/<TASK_ID>.md
```

Pahami:

- Description
- Scope
- Out of Scope
- Acceptance Criteria

Pastikan review hanya dilakukan terhadap ruang lingkup task tersebut.

---

# Step 2 — Read Related Documents

Baca dokumentasi yang berkaitan.

Minimal:

- Coding Standard
- Architecture
- API
- Database
- Domain Guide

Gunakan dokumen tersebut sebagai acuan review.

---

# Step 3 — Review Checklist

Periksa seluruh aspek berikut.

## Architecture

- Apakah sesuai dengan Architecture Guide?
- Apakah dependency benar?
- Apakah terdapat circular dependency?
- Apakah tanggung jawab setiap class jelas?

---

## Coding Standard

Periksa:

- Naming Convention
- Formatting
- Readability
- Consistency
- Comments

---

## Laravel Best Practices

Periksa:

- Dependency Injection
- Service Layer
- Action Layer
- Resource Class
- Form Request
- Middleware
- Route Organization

---

## Database

Periksa:

- Migration
- Foreign Key
- Index
- Transaction
- Constraint

---

## API

Periksa:

- RESTful Convention
- Status Code
- Validation
- Error Response
- Resource Format

---

## Performance

Periksa:

- N+1 Query
- Eager Loading
- Pagination
- Cache Opportunity
- Query Optimization

---

## Security

Periksa:

- Authorization
- Authentication
- Validation
- Mass Assignment
- SQL Injection
- XSS
- CSRF

---

## Maintainability

Periksa:

- SOLID
- DRY
- KISS
- Reusability
- Testability

---

## Documentation

Periksa apakah:

- Dokumentasi diperbarui.
- API berubah.
- Database berubah.
- Feature berubah.
- Deployment berubah.

---

# Step 4 — Severity Classification

Kelompokkan temuan berdasarkan tingkat keparahan.

## Critical

Masalah yang harus diperbaiki sebelum merge.

Contoh:

- Security Issue
- Data Loss
- Fatal Error

---

## High

Masalah penting.

Contoh:

- Bug
- Performance Buruk
- Query Tidak Optimal

---

## Medium

Masalah yang dapat diperbaiki.

Contoh:

- Refactoring
- Readability

---

## Low

Saran.

Contoh:

- Rename Variable
- Improve Comment
- Minor Cleanup

---

# Step 5 — Review Report

Gunakan format berikut.

## Summary

Ringkasan hasil review.

---

## Positive Findings

Hal-hal yang sudah baik.

Contoh:

- Struktur Service sudah konsisten.
- API mengikuti standar.
- Validasi sudah menggunakan Form Request.

---

## Findings

Daftar seluruh temuan.

Setiap temuan harus memiliki:

- Severity
- Lokasi
- Penjelasan
- Rekomendasi

Contoh:

Severity:

High

File:

PostController.php

Issue:

Business Logic berada di Controller.

Recommendation:

Pindahkan ke PostService.

---

# Step 6 — Technical Debt

Jika menemukan technical debt:

Jelaskan:

- Penyebab.
- Dampak.
- Solusi.

Tambahkan ke:

```text
docs/tasks/TECH-DEBT.md
```

jika telah disetujui oleh Human.

---

# Step 7 — Bug Report

Jika menemukan bug:

Jelaskan:

- Cara reproduksi.
- Dampak.
- Solusi.

Tambahkan ke:

```text
docs/tasks/BUGS.md
```

jika telah disetujui oleh Human.

---

# Step 8 — Refactoring Suggestions

Jika memungkinkan:

Berikan daftar refactoring.

Jangan melakukan refactoring tanpa persetujuan.

---

# AI Must NOT

AI tidak boleh:

- Langsung mengubah kode.
- Mengubah requirement.
- Mengubah architecture.
- Mengubah roadmap.
- Menghapus kode tanpa alasan yang jelas.

---

# Output Format

AI harus menghasilkan laporan review dengan format berikut.

## Overall Result

- Pass
- Pass with Recommendation
- Revision Required
- Failed

---

## Score

Berikan penilaian:

| Category        | Score |
| --------------- | ----: |
| Architecture    |   /10 |
| Code Quality    |   /10 |
| Performance     |   /10 |
| Security        |   /10 |
| Documentation   |   /10 |
| Maintainability |   /10 |

---

## Findings

Kelompokkan berdasarkan:

- Critical
- High
- Medium
- Low

---

## Recommendations

Berikan rekomendasi yang paling penting terlebih dahulu.

---

## Merge Recommendation

Pilih salah satu:

- Approve
- Approve with Minor Revision
- Needs Revision
- Reject

---

# Success Criteria

Review dianggap selesai apabila:

- Seluruh aspek telah diperiksa.
- Temuan telah diklasifikasikan.
- Tidak ada masalah Critical yang terlewat.
- Laporan review mudah dipahami.
- AI tidak melakukan perubahan kode tanpa instruksi Human.

Review harus membantu meningkatkan kualitas project, bukan sekadar mencari kesalahan.

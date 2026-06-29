# DISEKITAR AI Refactoring Prompt

Version: 1.0

---

# Purpose

Dokumen ini digunakan ketika AI melakukan **refactoring** terhadap kode yang sudah ada.

Tujuan utama refactoring adalah:

- Meningkatkan kualitas kode.
- Mengurangi technical debt.
- Meningkatkan maintainability.
- Meningkatkan readability.
- Meningkatkan performance (jika memungkinkan).

**Refactoring bukan implementasi fitur baru.**

Refactoring **tidak boleh mengubah business behavior** kecuali telah disetujui oleh Human.

---

# Your Role

Anda adalah Senior Software Engineer.

Tugas Anda:

- Membersihkan kode.
- Menyederhanakan implementasi.
- Mengurangi kompleksitas.
- Menghilangkan duplikasi.
- Meningkatkan maintainability.

Anda bukan Product Owner.

Jangan mengubah requirement bisnis.

---

# Refactoring Principles

Selalu pegang prinsip berikut:

- Preserve Behavior
- Small Incremental Changes
- Readability First
- Simplicity Over Cleverness
- Backward Compatibility

Jika terdapat perubahan perilaku sistem, hentikan proses dan minta persetujuan Human.

---

# Refactoring Workflow

Ikuti langkah berikut.

```text
Read Task
      │
      ▼
Read Related Documents
      │
      ▼
Analyze Existing Code
      │
      ▼
Identify Refactoring Opportunities
      │
      ▼
Create Refactoring Plan
      │
      ▼
Wait for Approval (Jika Diperlukan)
      │
      ▼
Refactor
      │
      ▼
Run Validation
      │
      ▼
Update Documentation
```

---

# Step 1 — Read Documentation

Baca dokumentasi yang relevan.

Minimal:

- 02-ARCHITECTURE.md
- 05-CODING-STANDARD.md
- 11-AGENTS.md
- 13-DOMAIN.md

Jika refactoring berkaitan dengan API atau Database, baca dokumen terkait.

---

# Step 2 — Analyze Existing Code

Sebelum mengubah kode, lakukan analisis.

Cari:

- Code Smell
- Duplicate Code
- Dead Code
- Long Method
- Large Class
- Tight Coupling
- Low Cohesion
- Complex Condition
- Magic Number
- Hardcoded Value
- Repeated Validation
- Repeated Query

Jangan mengubah kode sebelum analisis selesai.

---

# Step 3 — Refactoring Goals

Tentukan tujuan refactoring.

Contoh:

- Memecah class yang terlalu besar.
- Memindahkan business logic ke Service.
- Mengurangi duplikasi.
- Mengoptimalkan query.
- Memperjelas nama class.
- Memperjelas nama method.
- Menambahkan type declaration.
- Mengurangi kompleksitas fungsi.

---

# Step 4 — Refactoring Rules

Selama refactoring:

Gunakan:

- SOLID
- DRY
- KISS
- Single Responsibility
- Dependency Injection
- Laravel Convention

Hindari:

- Menulis ulang seluruh modul.
- Mengubah requirement.
- Mengubah API publik.
- Mengubah struktur database tanpa persetujuan.
- Mengubah response API.
- Menambah fitur baru.

---

# Step 5 — Allowed Refactoring

AI diperbolehkan:

- Rename Variable
- Rename Method
- Rename Class
- Extract Method
- Extract Class
- Extract Service
- Extract Action
- Inline Variable
- Remove Dead Code
- Simplify Condition
- Optimize Query
- Improve Error Handling
- Improve Logging
- Improve Comments (jika diperlukan)

---

# Step 6 — Forbidden Refactoring

AI tidak boleh:

- Mengubah business flow.
- Mengubah response API.
- Mengubah endpoint.
- Menghapus fitur.
- Menambah fitur.
- Menghapus migration.
- Mengubah database schema.
- Mengubah requirement bisnis.
- Melakukan breaking change.

---

# Step 7 — Validation Checklist

Pastikan:

- Behavior tetap sama.
- Acceptance Criteria tetap terpenuhi.
- Tidak ada regression.
- Coding Standard tetap dipatuhi.
- Arsitektur tetap konsisten.
- Domain Rules tetap dipatuhi.

---

# Step 8 — Technical Debt

Jika menemukan technical debt yang tidak dapat diselesaikan saat ini:

Dokumentasikan pada:

```text
docs/tasks/TECH-DEBT.md
```

Sertakan:

- Penyebab
- Dampak
- Solusi yang disarankan

---

# Step 9 — Documentation Update

Jika refactoring mengubah:

- Struktur class
- Arsitektur
- API internal

Perbarui dokumentasi yang relevan.

Jika tidak ada perubahan dokumentasi, tuliskan alasan pada laporan refactoring.

---

# Refactoring Report

Setelah selesai, buat laporan.

## Summary

Ringkasan perubahan.

---

## Refactoring Performed

Daftar perubahan.

Contoh:

- Extract PostService.
- Rename StorePostAction.
- Remove duplicate validation.
- Optimize eager loading.

---

## Benefits

Jelaskan manfaat.

Contoh:

- Kode lebih mudah dipelihara.
- Kompleksitas berkurang.
- Query lebih efisien.
- Struktur lebih konsisten.

---

## Risks

Jelaskan risiko yang masih ada.

Jika tidak ada:

```
None
```

---

## Technical Debt Remaining

Jelaskan technical debt yang belum terselesaikan.

Jika tidak ada:

```
None
```

---

## Files Modified

Daftar seluruh file yang diubah.

---

## Documentation Updated

Tuliskan dokumentasi yang diperbarui.

Jika tidak ada:

```
No documentation changes required.
```

---

# AI Must NOT

AI tidak boleh:

- Mengubah requirement.
- Mengubah business logic.
- Mengubah prioritas task.
- Menambah fitur.
- Menghapus fitur.
- Mengubah roadmap.
- Mengubah vision project.

---

# Success Criteria

Refactoring dianggap berhasil apabila:

- Tidak ada perubahan business behavior.
- Kode menjadi lebih sederhana.
- Technical debt berkurang.
- Readability meningkat.
- Maintainability meningkat.
- Performance meningkat (jika memungkinkan).
- Dokumentasi tetap sinkron.
- Tidak terjadi regression.

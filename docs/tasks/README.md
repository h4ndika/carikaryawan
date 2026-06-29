# DISEKITAR Task Management Guide

**Version:** 1.0
**Status:** Active
**Last Updated:** 2026-06-29

---

# Overview

Folder `docs/tasks` digunakan untuk mengelola seluruh pekerjaan pada proyek DISEKITAR.

Dokumen ini menjadi panduan bagi developer maupun AI Coding Agent dalam membuat, memperbarui, dan menyelesaikan task.

Tujuan utama sistem ini adalah:

- Satu sumber informasi untuk setiap task.
- Mudah dibaca oleh manusia.
- Mudah diproses oleh AI Coding Agent.
- Memiliki riwayat perubahan yang jelas melalui Git.
- Menghindari duplikasi informasi.

---

# Folder Structure

```text
docs/
└── tasks/
    ├── README.md
    ├── TASK_TEMPLATE.md
    ├── BACKLOG.md
    ├── CURRENT.md
    ├── NEXT.md
    ├── BUGS.md
    ├── TECH-DEBT.md
    ├── DONE.md
    └── items/
        ├── CORE-001.md
        ├── CMS-001.md
        ├── CMS-002.md
        ├── API-001.md
        └── ...
```

---

# Task Lifecycle

Setiap task memiliki siklus hidup berikut.

```text
IDEA

↓

BACKLOG

↓

NEXT

↓

CURRENT

↓

CODE REVIEW

↓

TESTING

↓

DONE
```

Jika ditemukan bug:

```text
BUG

↓

CURRENT

↓

DONE
```

Jika ditemukan technical debt:

```text
TECH-DEBT

↓

NEXT

↓

CURRENT

↓

DONE
```

---

# Single Source of Truth

Setiap task hanya memiliki **satu file**.

Contoh:

```
tasks/items/CMS-001.md
```

Seluruh informasi mengenai task tersebut harus berada di dalam file tersebut.

Jangan membuat salinan informasi pada file lain.

---

# Index Files

File berikut hanya berisi daftar task.

- BACKLOG.md
- NEXT.md
- CURRENT.md
- BUGS.md
- TECH-DEBT.md
- DONE.md

File tersebut **bukan tempat menyimpan detail task**.

Contoh:

## BACKLOG.md

```text
CMS-001
CMS-002
API-001
LOC-001
```

---

# Status

Status ditentukan di dalam file task.

Contoh:

```yaml
Status: Backlog
```

Status yang tersedia:

- Idea
- Backlog
- Next
- Current
- Code Review
- Testing
- Done
- Cancelled

---

# Task ID

Gunakan ID berdasarkan domain.

Contoh:

```
CORE-001
CMS-001
CMS-002
API-001
SEO-001
LOC-001
MEDIA-001
AUTH-001
USER-001
HOTEL-001
EVENT-001
AI-001
```

ID tidak boleh diubah setelah dibuat.

---

# Moving Task

Task **tidak dipindahkan ke folder lain**.

Yang berubah hanyalah:

- Status
- Progress
- History

Contoh:

```yaml
Status: Current
```

kemudian

```yaml
Status: Done
```

Hal ini menjaga history Git tetap sederhana dan mudah dilacak.

---

# Progress

Gunakan persentase.

Contoh:

```
0%
25%
50%
75%
100%
```

---

# History

Setiap perubahan penting dicatat.

Contoh:

```
2026-06-29
Task dibuat.

2026-07-02
Status menjadi Current.

2026-07-05
Feature selesai.

2026-07-06
Masuk tahap Testing.

2026-07-07
Status menjadi Done.
```

---

# AI Coding Agent Rules

AI wajib mengikuti aturan berikut:

1. Baca `CURRENT.md` terlebih dahulu.
2. Jika kosong, baca `NEXT.md`.
3. Jangan mengambil task dari `BACKLOG.md` tanpa persetujuan.
4. Perbarui status setelah pekerjaan selesai.
5. Tambahkan riwayat perubahan.
6. Perbarui dokumentasi jika diperlukan.

---

# Benefits

Dengan sistem ini:

- Setiap task memiliki satu file.
- Tidak ada duplikasi informasi.
- Riwayat Git lebih bersih.
- AI dapat fokus pada satu task.
- Developer lebih mudah mencari informasi.
- Skalabel untuk ratusan bahkan ribuan task.

---

# Definition of Done

Sistem task dianggap digunakan dengan benar apabila:

- Setiap task memiliki satu file.
- Status selalu diperbarui.
- History selalu dicatat.
- Dokumentasi terkait ikut diperbarui.
- Index (`BACKLOG`, `CURRENT`, `DONE`, dll.) selalu sinkron dengan status task.

# DISEKITAR Task Role Matrix

**Version:** 1.0
**Status:** Active
**Last Updated:** 2026-06-29

---

# Overview

Dokumen ini mendefinisikan hak akses dan tanggung jawab antara **Human (Project Owner / Developer)** dan **AI Coding Agent** dalam mengelola dokumentasi task DISEKITAR.

Tujuan utama:

- Menjaga konsistensi roadmap.
- Mencegah AI mengubah prioritas proyek tanpa persetujuan.
- Memberikan kebebasan kepada AI untuk memperbarui progres pekerjaan.
- Menentukan batas tanggung jawab yang jelas.

---

# Principles

AI Coding Agent berperan sebagai **Software Engineer**, bukan **Product Owner**.

Artinya:

- AI mengimplementasikan solusi.
- AI memberikan saran.
- AI memperbarui progres pekerjaan.
- AI **tidak menentukan arah bisnis**.

Keputusan bisnis tetap berada di tangan Human.

---

# Responsibility Matrix

| File             | Human |       AI        | Keterangan                                                                     |
| ---------------- | :---: | :-------------: | ------------------------------------------------------------------------------ |
| README.md        |  ✅   |  👀 Read Only   | Menjelaskan sistem task.                                                       |
| TASK_TEMPLATE.md |  ✅   |  👀 Read Only   | Template resmi task.                                                           |
| BACKLOG.md       |  ✅   |  👀 Read Only   | Daftar pekerjaan yang belum diprioritaskan.                                    |
| NEXT.md          |  ✅   |  👀 Read Only   | Prioritas berikutnya.                                                          |
| CURRENT.md       |  ✅   |    ✅ Update    | AI dapat memperbarui status task yang sedang dikerjakan.                       |
| BUGS.md          |  ✅   |    ✅ Update    | AI dapat menambahkan bug yang ditemukan selama pengembangan.                   |
| TECH-DEBT.md     |  ✅   |    ✅ Update    | AI dapat mencatat technical debt yang ditemukan.                               |
| IDEAS.md         |  ✅   | 💡 Suggest Only | AI boleh mengusulkan ide, tetapi tidak boleh menambahkannya tanpa persetujuan. |
| DONE.md          |  ✅   |    ✅ Update    | AI dapat mencatat task yang telah selesai.                                     |
| items/\*.md      |  ✅   |    ✅ Update    | AI dapat memperbarui task yang sedang dikerjakan.                              |

---

# Permission Legend

| Simbol          | Arti                                                                          |
| --------------- | ----------------------------------------------------------------------------- |
| ✅              | Dapat membuat, mengubah, dan memperbarui.                                     |
| 👀 Read Only    | Hanya boleh membaca. Tidak boleh mengubah tanpa persetujuan.                  |
| 💡 Suggest Only | Boleh memberikan usulan, tetapi tidak boleh mengubah dokumen secara langsung. |

---

# Human Responsibilities

Human bertanggung jawab untuk:

- Menentukan visi proyek.
- Menentukan roadmap.
- Menentukan milestone.
- Menentukan prioritas task.
- Menyetujui perubahan besar.
- Menyetujui fitur baru.
- Menyetujui perubahan arsitektur.

---

# AI Responsibilities

AI Coding Agent bertanggung jawab untuk:

- Mengimplementasikan task.
- Memperbarui status task.
- Memperbarui progress.
- Menambahkan history pekerjaan.
- Memperbarui dokumentasi yang relevan.
- Melaporkan bug.
- Melaporkan technical debt.
- Memberikan rekomendasi perbaikan.

---

# AI Must NOT

AI tidak boleh melakukan hal-hal berikut tanpa persetujuan Human:

- Mengubah Vision.
- Mengubah Roadmap.
- Mengubah Architecture.
- Mengubah prioritas task.
- Menambah task baru ke Backlog.
- Menghapus task.
- Menghapus dokumentasi.
- Mengubah milestone.
- Membatalkan task.
- Mengubah struktur project secara signifikan.

---

# AI May Suggest

AI diperbolehkan memberikan rekomendasi mengenai:

- Fitur baru.
- Refactoring.
- Optimasi performa.
- Peningkatan keamanan.
- Perbaikan UI/UX.
- Perubahan struktur database.
- Dependency baru.
- Ide produk.

Namun seluruh rekomendasi harus menunggu persetujuan Human sebelum diterapkan.

---

# Workflow

AI harus mengikuti alur berikut:

1. Membaca `docs/tasks/README.md`.
2. Membaca `CURRENT.md`.
3. Membuka detail task pada `items/<TASK_ID>.md`.
4. Mengimplementasikan task.
5. Memperbarui:
    - Progress
    - Status
    - History
    - Completion Summary

6. Memperbarui `DONE.md` jika task selesai.
7. Memperbarui dokumentasi lain jika diperlukan.

---

# Updating Task Status

AI diperbolehkan mengubah status task berikut:

```text
Backlog
      │
      └── (Human Only)
             │
             ▼
Next
      │
      └── (Human Only)
             │
             ▼
Current
      │
      └── (AI + Human)
             │
             ▼
Code Review
      │
      └── (AI + Human)
             │
             ▼
Testing
      │
      └── (AI + Human)
             │
             ▼
Done
```

AI tidak boleh memindahkan task dari **Backlog** ke **Next** tanpa persetujuan.

---

# Escalation Rules

AI wajib meminta persetujuan Human apabila:

- Menemukan perubahan arsitektur besar.
- Membutuhkan migration database yang kompleks.
- Akan menghapus data.
- Akan menghapus modul.
- Akan melakukan breaking change.
- Akan mengubah dependency utama.
- Akan membuat task baru.

---

# Documentation Rules

Jika implementasi memengaruhi:

- Database
- API
- Architecture
- Deployment
- SEO
- Features

AI wajib memperbarui dokumentasi yang relevan.

---

# Communication Rules

Jika kebutuhan tidak jelas:

AI harus:

- Mengajukan pertanyaan.
- Menjelaskan asumsi yang digunakan.
- Tidak menebak kebutuhan bisnis.
- Tidak membuat keputusan produk secara sepihak.

---

# Decision Authority

Keputusan akhir selalu berada pada Human.

AI berfungsi sebagai:

- Software Engineer
- Technical Advisor
- Documentation Assistant
- Code Reviewer (jika diminta)

AI bukan Product Owner maupun Project Manager.

---

# Goal

Kolaborasi Human dan AI harus menghasilkan:

- Dokumentasi yang selalu sinkron.
- Kode yang konsisten.
- Roadmap yang terjaga.
- Pengembangan yang terstruktur.
- Technical debt yang terkendali.
- Platform yang mudah dikembangkan dalam jangka panjang.

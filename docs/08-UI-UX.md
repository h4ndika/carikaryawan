# DISEKITAR UI/UX Guidelines

**Version:** 1.0
**Status:** Active
**Last Updated:** 2026-06-29

---

# Overview

Dokumen ini menjelaskan standar User Interface (UI) dan User Experience (UX) untuk seluruh platform DISEKITAR.

Seluruh halaman publik, dashboard admin, dashboard bisnis, dan aplikasi mobile wajib mengikuti panduan ini agar pengalaman pengguna tetap konsisten.

---

# Design Philosophy

DISEKITAR dibangun berdasarkan empat prinsip utama.

> **Simple • Familiar • Fast • Local First**

Setiap keputusan desain harus membantu pengguna menemukan informasi dengan lebih cepat.

---

# Design Goals

Target utama desain:

- Mudah dipahami pengguna baru.
- Cepat digunakan.
- Konsisten di seluruh halaman.
- Ramah perangkat mobile.
- Mendukung aksesibilitas.
- Mendukung SEO.

---

# User Personas

Platform melayani beberapa jenis pengguna.

## Visitor

Tujuan:

- Mencari informasi.
- Menemukan tempat.
- Membandingkan pilihan.
- Membaca ulasan.

---

## Business Owner

Tujuan:

- Mengelola listing.
- Memperbarui informasi.
- Melihat performa listing.

---

## Content Editor

Tujuan:

- Membuat konten.
- Mengelola media.
- Memastikan kualitas data.

---

## Administrator

Tujuan:

- Mengelola platform.
- Mengelola pengguna.
- Memantau sistem.

---

# UX Principles

Seluruh alur harus mengikuti prinsip berikut.

## Clarity

Pengguna selalu tahu:

- sedang berada di mana,
- apa yang dapat dilakukan,
- dan apa yang akan terjadi setelah suatu tindakan.

---

## Consistency

Komponen yang sama harus memiliki perilaku yang sama.

Contoh:

- Tombol utama selalu memiliki gaya yang sama.
- Form selalu menggunakan pola yang sama.
- Card memiliki struktur yang konsisten.

---

## Simplicity

Hilangkan elemen yang tidak memberikan nilai.

Jangan menambahkan fitur hanya karena terlihat menarik.

---

## Speed

Setiap interaksi harus terasa cepat.

Gunakan:

- skeleton loading,
- lazy loading,
- optimistic update (jika sesuai),
- pagination.

---

## Accessibility

Website harus dapat digunakan oleh sebanyak mungkin pengguna.

Minimal memperhatikan:

- kontras warna,
- ukuran teks,
- navigasi keyboard,
- alt text pada gambar,
- label form.

---

# Navigation

Navigasi harus sederhana.

Struktur utama:

```text
Home

↓

Category

↓

Location

↓

Detail
```

Breadcrumb wajib tersedia pada halaman detail.

---

# Homepage

Homepage harus menjawab pertanyaan:

> "Apa yang ingin saya cari di sekitar saya?"

Komponen utama:

- Search Bar
- Featured Category
- Popular Destination
- Latest Content
- Trending
- Nearby (Future)

---

# Search Experience

Search merupakan fitur utama.

Target:

- mudah ditemukan,
- cepat,
- relevan.

Fitur:

- autocomplete (Future),
- recent search,
- filter,
- sorting,
- lokasi.

---

# Listing Page

Contoh:

- Hotel
- Kuliner
- Event
- Wisata

Komponen:

- Filter
- Sorting
- Result Count
- Pagination
- Card View

---

# Detail Page

Halaman detail harus menjadi pusat informasi.

Minimal berisi:

- Judul
- Featured Image
- Lokasi
- Deskripsi
- Gallery
- Informasi penting
- Peta (Future)
- Konten terkait
- Tempat di sekitar

---

# Card Design

Seluruh card harus memiliki struktur yang konsisten.

Urutan informasi:

1. Gambar
2. Judul
3. Lokasi
4. Ringkasan
5. Informasi tambahan
6. Aksi (jika ada)

---

# Forms

Form harus:

- sederhana,
- mudah dipahami,
- memiliki validasi yang jelas.

Gunakan:

- placeholder seperlunya,
- helper text,
- validasi real-time jika bermanfaat,
- pesan error yang spesifik.

---

# Buttons

Gunakan hirarki yang jelas.

Jenis tombol:

- Primary
- Secondary
- Outline
- Text
- Danger

Hindari penggunaan terlalu banyak warna aksi.

---

# Colors

Gunakan warna secara konsisten.

Peran warna:

- Primary
- Secondary
- Success
- Warning
- Danger
- Neutral

Warna tidak boleh menjadi satu-satunya indikator suatu status.

---

# Typography

Prioritaskan keterbacaan.

Gunakan hirarki:

- H1
- H2
- H3
- Body
- Caption

Hindari terlalu banyak variasi ukuran font.

---

# Icons

Gunakan icon hanya jika membantu pemahaman.

Jangan menggunakan icon sebagai dekorasi semata.

Seluruh icon harus berasal dari satu library yang konsisten.

---

# Images

Semua gambar harus:

- proporsional,
- memiliki rasio konsisten,
- memiliki placeholder,
- memiliki alt text.

---

# Empty State

Setiap halaman harus memiliki empty state.

Contoh:

- Belum ada data.
- Tidak ditemukan hasil pencarian.
- Belum ada bookmark.

Berikan tindakan yang dapat dilakukan pengguna.

---

# Error State

Gunakan pesan yang jelas.

Hindari:

> Error 500

Gunakan:

> Terjadi kesalahan. Silakan coba beberapa saat lagi.

---

# Loading State

Gunakan:

- Skeleton Screen
- Progress Indicator
- Lazy Loading

Hindari spinner panjang tanpa informasi.

---

# Notifications

Jenis notifikasi:

- Success
- Info
- Warning
- Error

Pesan harus:

- singkat,
- jelas,
- menjelaskan tindakan berikutnya jika diperlukan.

---

# Mobile First

Semua halaman harus dirancang untuk mobile terlebih dahulu.

Prioritas:

- navigasi mudah dijangkau,
- tombol cukup besar,
- teks mudah dibaca,
- form mudah diisi.

---

# Responsive Design

Minimal mendukung:

- Mobile
- Tablet
- Laptop
- Desktop

Konten harus beradaptasi tanpa kehilangan informasi penting.

---

# Performance UX

Target:

- halaman terasa responsif,
- perpindahan antar halaman cepat,
- gambar dimuat secara bertahap,
- interaksi tidak terhambat.

---

# Admin UX

Dashboard admin harus mengutamakan efisiensi.

Prinsip:

- sedikit klik,
- mudah dipelajari,
- fokus pada produktivitas.

Filament menjadi dasar antarmuka admin.

---

# Business Dashboard

Dashboard bisnis harus menampilkan informasi penting terlebih dahulu.

Contoh:

- Statistik listing
- Jumlah kunjungan
- Review terbaru
- Aksi cepat

---

# Future Mobile App

Aplikasi mobile mengikuti prinsip yang sama.

Jangan membuat pengalaman yang sangat berbeda dengan website.

---

# Design System

Seluruh komponen UI harus berasal dari Design System yang sama.

Komponen inti:

- Button
- Card
- Badge
- Alert
- Input
- Select
- Modal
- Drawer
- Table
- Pagination
- Breadcrumb
- Tabs
- Accordion
- Tooltip

Setiap komponen memiliki perilaku yang konsisten.

---

# Motion & Animation

Gunakan animasi secara seperlunya.

Animasi bertujuan:

- memberikan umpan balik,
- memperjelas transisi,
- meningkatkan kenyamanan.

Hindari animasi yang berlebihan.

---

# AI Coding Guidelines

Saat membuat halaman:

- Gunakan komponen yang sudah tersedia.
- Jangan membuat variasi UI baru jika komponen serupa sudah ada.
- Ikuti Design System.
- Utamakan mobile-first.
- Pastikan setiap halaman memiliki loading, empty, dan error state.
- Jaga konsistensi spacing, tipografi, dan warna.

---

# UI/UX Checklist

Sebelum halaman dianggap selesai:

- Navigasi jelas.
- Responsive.
- Mobile-friendly.
- Memiliki loading state.
- Memiliki empty state.
- Memiliki error state.
- Form tervalidasi dengan baik.
- Konsisten dengan Design System.
- Aksesibel.
- Tidak membingungkan pengguna.

---

# Definition of Done

Sebuah halaman dianggap selesai apabila:

- Mudah dipahami pengguna baru.
- Konsisten dengan halaman lain.
- Berfungsi baik di berbagai ukuran layar.
- Mendukung aksesibilitas dasar.
- Memiliki performa yang baik.
- Mengikuti Design System.
- Mendukung tujuan utama DISEKITAR: membantu pengguna menemukan informasi lokal dengan cepat dan nyaman.

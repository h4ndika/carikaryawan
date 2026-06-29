# DISEKITAR Coding Standard

**Version:** 1.0
**Status:** Active
**Last Updated:** 2026-06-29

---

# Purpose

Dokumen ini berisi standar penulisan kode pada project DISEKITAR.

Seluruh developer dan AI Coding Agent wajib mengikuti aturan pada dokumen ini.

Tujuan utama:

- Konsisten
- Mudah dibaca
- Mudah dipelihara
- Mudah diuji
- Mudah dikembangkan

---

# General Principles

Selalu ikuti prinsip berikut:

- KISS (Keep It Simple)
- DRY (Don't Repeat Yourself)
- SOLID
- Convention over Configuration
- Readability First
- Explicit is Better than Implicit

Apabila terdapat dua solusi yang sama baiknya, pilih solusi yang:

- lebih sederhana
- lebih mudah dipahami
- lebih mudah dipelihara

---

# PHP Standard

Gunakan:

- PHP 8.3+
- PSR-12
- Strict Type

Seluruh file PHP harus diawali dengan:

```php
<?php

declare(strict_types=1);
```

---

# Naming Convention

## Class

Gunakan PascalCase.

Benar

```text
PostService
CreatePostAction
LocationResource
```

Salah

```text
postservice
post_Service
```

---

## Method

Gunakan camelCase.

```php
publishPost()

findNearby()

generateSlug()
```

---

## Variable

Gunakan camelCase.

```php
$post

$category

$featuredImage
```

---

## Constant

Gunakan UPPER_SNAKE_CASE.

```php
MAX_UPLOAD_SIZE
DEFAULT_PAGE_SIZE
```

---

## Database

Gunakan snake_case.

```text
published_at

created_by

meta_description
```

---

# File Organization

Satu file hanya memiliki satu tanggung jawab.

Hindari file yang terlalu besar.

Target:

- Controller < 100 baris
- Action < 150 baris
- Service < 300 baris

Jika melebihi, pertimbangkan untuk dipecah.

---

# Controller Rules

Controller hanya boleh:

- menerima request
- memanggil Action
- mengembalikan response

Controller tidak boleh:

- query kompleks
- validasi manual
- business logic
- manipulasi file
- kirim email

---

# Form Request

Semua validasi menggunakan Form Request.

Jangan menggunakan:

```php
$request->validate(...)
```

di dalam Controller.

---

# Action Rules

Gunakan Action untuk setiap use case.

Contoh:

```text
CreatePostAction
UpdatePostAction
PublishPostAction
DeletePostAction
```

Satu Action = satu tujuan.

Jangan membuat Action yang menangani banyak proses berbeda.

---

# Service Rules

Gunakan Service jika logic digunakan lebih dari satu Action.

Contoh:

```text
ImageService

SeoService

LocationService

SlugService
```

Jangan membuat Service yang hanya berisi satu method sederhana.

---

# Model Rules

Model hanya berisi:

- Relationship
- Scope
- Accessor
- Mutator
- Cast
- Attribute

Hindari business logic besar.

---

# Eloquent

Gunakan Eloquent sebagai default.

Gunakan:

- Relationship
- Scope
- Eager Loading

Hindari:

- Raw SQL
- DB::table() tanpa alasan
- Query di View

---

# Mass Assignment

Selalu gunakan:

```php
$fillable
```

atau

```php
$guarded
```

Jangan membiarkan Model tanpa perlindungan.

---

# Relationship

Gunakan Relationship.

Benar

```php
$post->category
```

Salah

```php
Category::find($post->category_id)
```

---

# Query

Gunakan Scope bila query dipakai berulang.

Contoh:

```php
Post::published()

Post::featured()

Post::popular()
```

---

# Validation

Seluruh validasi dilakukan menggunakan:

Form Request

Rule Object (jika kompleks)

Jangan melakukan validasi di Service.

---

# Exception

Gunakan Exception.

Jangan mengembalikan:

```php
false
```

atau

```php
null
```

untuk error.

---

# Enum

Gunakan PHP Enum.

Contoh:

```php
PostStatus::Published

PostStatus::Draft
```

Jangan menggunakan:

```php
$status = 1;
```

---

# DTO

Gunakan DTO apabila:

- parameter sangat banyak
- data berpindah antar layer
- proses kompleks

Jangan membuat DTO untuk kasus sederhana.

---

# Trait

Trait hanya digunakan apabila benar-benar reusable.

Contoh:

```text
HasSeo

HasSlug

HasGallery
```

Jangan menggunakan Trait hanya untuk menghindari copy-paste kecil.

---

# Helper

Jangan membuat Global Helper tanpa alasan kuat.

Prioritaskan:

- Service
- Action
- Class Utility

---

# Dependency Injection

Gunakan Constructor Injection.

Benar

```php
public function __construct(
    private ImageService $imageService
) {}
```

Jangan menggunakan Service Locator.

---

# API Response

Gunakan Laravel API Resource.

Jangan mengembalikan Model langsung.

---

# Database Transaction

Gunakan transaction apabila melibatkan lebih dari satu perubahan data.

Contoh:

- membuat post
- upload gallery
- membuat SEO

Harus berada dalam satu transaction.

---

# Queue

Gunakan Queue untuk:

- Email
- Resize Image
- AI
- Import
- Export
- Notification

Jangan menjalankan proses berat pada HTTP Request.

---

# Logging

Gunakan Log hanya untuk:

- debugging
- audit
- critical error

Jangan meninggalkan debug log.

---

# Configuration

Jangan menggunakan:

```php
env(...)
```

di dalam application code.

Gunakan:

```php
config(...)
```

---

# Magic Number

Hindari Magic Number.

Salah

```php
if ($status == 2)
```

Benar

```php
if ($status === PostStatus::Published)
```

---

# Comments

Kode yang baik tidak membutuhkan banyak komentar.

Gunakan komentar hanya untuk:

- business rule
- algoritma kompleks
- keputusan arsitektur

Jangan memberi komentar yang menjelaskan hal yang sudah jelas.

---

# Testing

Setiap business logic penting harus dapat diuji.

Prioritaskan:

- Feature Test
- Unit Test

---

# Filament Standard

Filament hanya sebagai Admin UI.

Business Logic tetap berada pada:

- Action
- Service

Jangan menaruh logic panjang di:

- Resource
- Page
- Relation Manager

---

# Blade Standard

Blade hanya untuk tampilan.

Jangan:

- Query Database
- Business Logic
- Perhitungan kompleks

Gunakan ViewModel atau data dari Controller.

---

# Performance Rules

Selalu perhatikan:

- eager loading
- pagination
- cache
- indexing

Hindari:

- N+1 Query
- nested loop berlebihan
- query di dalam loop

---

# Security

Selalu gunakan:

- Form Request
- Authorization
- Policy
- CSRF Protection
- Escaping Output

Jangan mempercayai input pengguna.

---

# Git Commit

Gunakan Conventional Commit.

Contoh:

```text
feat: add gallery upload

fix: resolve slug duplication

refactor: simplify image service

docs: update architecture

test: add post feature test
```

---

# AI Coding Rules

AI Coding Agent wajib:

- Mengikuti seluruh dokumen pada folder `docs/`.
- Tidak membuat struktur baru tanpa alasan.
- Tidak menambahkan dependency yang tidak diperlukan.
- Tidak menduplikasi kode.
- Tidak membuat helper global tanpa persetujuan.
- Mengutamakan Eloquent dibanding Raw SQL.
- Menggunakan Action untuk setiap use case.
- Menggunakan Service hanya bila logika digunakan kembali.
- Menghasilkan kode yang mudah dibaca, bukan hanya berfungsi.

Jika terdapat beberapa solusi, pilih yang paling sederhana namun tetap dapat berkembang.

---

# Definition of Done

Kode dianggap selesai apabila:

- Mengikuti PSR-12.
- Lolos static analysis (jika digunakan).
- Tidak memiliki N+1 Query.
- Menggunakan Form Request.
- Menggunakan Action/Service sesuai kebutuhan.
- Menggunakan API Resource.
- Memiliki validasi.
- Memiliki authorization bila diperlukan.
- Tidak menimbulkan warning atau error.
- Mudah dipahami developer lain.

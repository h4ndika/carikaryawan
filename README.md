# CariKaryawan

Portal lowongan kerja Indonesia di [carikaryawan.com](https://carikaryawan.com) plus tools karier berbasis AI (CV builder, cover letter, interview prep). Trafik utama dari Google (SEO), monetisasi lewat iklan dan afiliasi.

Lowongan diisi otomatis: scraping Apify (JobStreet, Glints) → generate konten AI → publish → broadcast ke Threads. Ekstraksi manual/batch tersedia di admin.

> Proyek ini diturunkan dari DISEKITAR (direktori tempat + landmark + blog). Fitur tempat/landmark/blog sudah dihapus dari kode; lihat [milestone.md](milestone.md) untuk riwayat dan [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) untuk purge database.

## Stack

Laravel 13 · PHP 8.3 · Livewire · Filament 5 (admin di `/rahasia`) · MySQL · Redis · OpenAI · Apify · Pest

## Menjalankan lokal

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
# atur DB_*, OPENAI_*, APIFY_TOKEN di .env
php artisan migrate
composer run dev          # server + queue + vite
```

Buat user admin: `php artisan make:filament-user`, lalu buka `/rahasia`.

## Tes

```bash
php artisan test
```

Membutuhkan ekstensi `pdo_sqlite` (lihat [docs/TESTING.md](docs/TESTING.md)).

## Dokumentasi

- [Arsitektur & pipeline otomatis](docs/ARCHITECTURE.md)
- [Domain & aturan bisnis](docs/DOMAIN.md)
- [Deployment & operasional](docs/DEPLOYMENT.md)
- [Testing](docs/TESTING.md)
- [Riwayat milestone](milestone.md)

## Struktur singkat

```text
app/Livewire          halaman publik (lowongan, tools karier)
app/Filament/Rahasia  panel admin
app/Services          logika bisnis & integrasi (OpenAI, Apify, Threads)
app/Console/Commands  pipeline terjadwal (lihat routes/console.php)
tools/job-extractor   tool Node.js standalone untuk ekstraksi loker
deploy/setup-vps.sh   provisioning VPS (wajib env DB_PASS)
```

# DISEKITAR

Platform informasi lokal Indonesia di [disekitar.com](https://disekitar.com): *"apa saja yang ada di sekitarmu"*. Berisi tempat (hotel, kuliner, wisata, fasilitas), lowongan kerja, landmark, blog kurasi, dan tools karier berbasis AI. Trafik utama berasal dari Google (SEO), monetisasi lewat iklan dan afiliasi.

Sebagian besar konten diisi otomatis: scraping Apify (Google Maps, JobStreet, Glints) → generate konten AI → publish bertahap → foto → broadcast ke Threads.

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
app/Livewire          halaman publik
app/Filament/Rahasia  panel admin
app/Services          logika bisnis & integrasi (OpenAI, Apify, Threads)
app/Console/Commands  pipeline terjadwal (lihat routes/console.php)
tools/                tool Node.js standalone (extractor & photo admin)
deploy/setup-vps.sh   provisioning VPS
```

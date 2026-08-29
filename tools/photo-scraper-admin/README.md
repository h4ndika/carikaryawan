# Photo Scraper Admin

Standalone Node.js admin panel untuk scraping foto tempat di database Disekitar.

## Features

- ✅ Login menggunakan table `users` dari database utama
- ✅ Daftar posts dengan pagination
- ✅ Scrape foto menggunakan logic yang sama dengan `google-map-extractor`
- ✅ Multi-tier scraping: DuckDuckGo → Bing → Apify fallback
- ✅ Preview foto dengan lightbox
- ✅ Auto-update thumbnail post

## Tech Stack

- **Backend**: Express.js
- **Database**: MySQL2 (shared dengan Laravel)
- **View Engine**: EJS
- **Session**: express-session
- **Authentication**: bcryptjs
- **Photo Scraper**: Native fetch + Apify Client

## Installation

1. Install dependencies:
```bash
cd tools/photo-scraper-admin
npm install
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Edit `.env` jika perlu (default sudah sesuai dengan Laravel)

## Running

Development mode (with auto-reload):
```bash
npm run dev
```

Production mode:
```bash
npm start
```

Server akan berjalan di: **http://localhost:3001**

## Login

Gunakan kredensial user yang ada di table `users` (sama dengan login Filament).

## How It Works

1. **Login**: Menggunakan table `users` dari database utama dengan bcrypt password verification
2. **Posts List**: Menampilkan daftar posts dengan informasi kategori, lokasi, dan jumlah foto
3. **Scrape Photos**: 
   - Membuat query: `[Kategori] [Judul] [Kota] [Provinsi] Indonesia`
   - Scraping dari DuckDuckGo (priority 1)
   - Fallback ke Bing (priority 2)
   - Fallback ke Apify Actor (priority 3, jika ada place_id)
   - Menghapus foto lama dan insert foto baru
   - Auto-update thumbnail jika kosong
4. **View Photos**: Preview semua foto dengan lightbox

## Scraping Logic

Photo scraping menggunakan **exact same logic** dengan `google-map-extractor/src/utils/photoScraper.js`:
- Simple regex pattern untuk Bing
- Validation untuk URL (favicon, logo, icon filtering)
- Multi-source dengan fallback otomatis

## Database Schema

Menggunakan table yang sama dengan Laravel:
- `users` - Authentication
- `posts` - Daftar tempat
- `post_images` - Foto tempat
- `categories` - Kategori tempat
- `cities` - Kota
- `provinces` - Provinsi

## Port Configuration

Default port: `3001` (dapat diubah di `.env`)

Pastikan port tidak konflik dengan:
- Laravel (port 80/8000)
- Google Map Extractor (jika ada)

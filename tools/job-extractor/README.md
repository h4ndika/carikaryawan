# DiSekitar Job Extractor (Google Jobs & Instagram)

Modul ekstraktor lowongan kerja lokal untuk ekosistem DiSekitar. Mendukung 2 mesin (engine) dengan format data seragam:
1. **Google Jobs** (`spidersource/google-jobs-scraper`) — *Default*
2. **Instagram Loker** (`apify/instagram-post-scraper`)

---

## 🚀 Cara Instalasi & Setup

1. Masuk ke direktori:
   ```bash
   cd tools/job-extractor
   npm install
   ```

2. Konfigurasi Token:
   Salin `.env.example` ke `.env` dan masukkan `APIFY_TOKEN`:
   ```bash
   cp .env.example .env
   ```
   *(Modul ini juga otomatis membaca token fallback dari `tools/google-map-extractor/.env` jika ada).*

---

## 🌐 Cara Menjalankan Web Dashboard

Mirip dengan `tools/google-map-extractor`, modul ini memiliki antarmuka Web Dashboard interaktif lengkap dengan live streaming log (SSE), penampil kartu lowongan, dan pengelola berkas output.

```bash
cd tools/job-extractor
npm run dashboard
```
Buka browser di: **`http://localhost:3002`**

Fitur Dashboard:
- 🚀 **Scraper Console**: Pilih engine (Google Jobs / Instagram), ketik keyword, atur limit, dan pantau log ekstraksi real-time.
- 📋 **Hasil Lowongan (Viewer)**: Preview kartu lowongan lengkap dengan posisi, nama perusahaan, estimasi gaji, lokasi/kota, deskripsi kualifikasi, modal detail, dan tombol langsung lamar kerja.
- 🗂️ **File Output Manager**: Daftar file JSON di `output/` beserta jumlah lowongan, ukuran berkas, aksi download, dan hapus.
- ⚙️ **Pengaturan**: Setel token Apify langsung dari antarmuka web.

---

## 💻 Cara Penggunaan CLI

### 1. Ekstrak via Google Jobs (Default)
```bash
# Contoh 1: Loker Barista di Bandung
node src/index.js scrape -q "lowongan barista bandung" -m 20

# Contoh 2: Loker Staf Kafe di Jogja
node src/index.js scrape -q "lowongan staf kafe jogja" -m 15

# Contoh 3: Loker Hotel di Surabaya
node src/index.js scrape -q "lowongan kerja hotel surabaya" -m 20
```

### 2. Ekstrak via Instagram Loker
```bash
# Mengambil post terbaru dari akun Instagram loker daerah (masukkan username tanpa @)
node src/index.js scrape -e instagram -q "lokerbandung" -m 15
```

---

## 📂 Output Data & Impor ke DiSekitar

Hasil ekstraksi otomatis tersimpan dalam format `.json` siap pakai di folder `tools/job-extractor/output/`.

File JSON ini dapat diimpor langsung ke database DiSekitar melalui:
- **Panel Admin Filament**: Masuk ke menu **Posts** ➔ Klik tombol **"Import Google Maps / Loker"** ➔ Upload file JSON.
- **CLI Artisan**: `php artisan maps:import --file=tools/job-extractor/output/nama_file.json --default-category=6` (Kategori: Lowongan Kerja).

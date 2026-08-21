# Google Maps Data Extractor v2 (Node.js & Apify Edition)

Proyek Node.js mandiri (CLI & script) untuk ekstraksi data bisnis Google Maps skala besar menggunakan Apify Actor `compass/crawler-google-places` (`nwua9Gu5YrADL7ZDj`) dilengkapi dengan modul **Apify Account Automation Creator**.

---

## 🌟 Fitur Utama v2

1. **Scraping Skala Besar via Apify Actor**:
   - Memakai Actor `compass/crawler-google-places` resmi.
   - Mengambil data komprehensif: Nama, Kategori, Rating, Jumlah Review, Nomor Telepon, Website, Alamat Lengkap, Koordinat GPS (Latitude/Longitude), Jam Buka, Deskripsi, Fasilitas, Google Maps URL, Foto, Place ID, dan CID.
   - Tidak terpengaruh perubahan / obfuscation DOM Google Maps karena diproses langsung oleh cloud cluster Apify.

2. **Ekspor Multi-Format (4 Format)**:
   - **Excel (.xlsx)**: Styling profesional (header biru Google `#1A73E8`, teks tebal putih, borders cell, auto-width).
   - **CSV**: RFC 4180 standard dengan escape karakter aman.
   - **JSON**: Pretty-printed format untuk integrasi API/database.
   - **XML**: Struktur `<businesses><business>...</business></businesses>` dengan entity escaping.
   - Opsi `all`: Sekali jalan langsung membuat ke-4 format sekaligus.

3. **Apify Account Automation Creator**:
   - Otomasi pembuatan akun Apify baru menggunakan temporary email (`mail.tm` API).
   - Otomasi verifikasi email dan ekstraksi **Personal API Token** via Puppeteer Stealth.
   - Otomatis memperbarui file `.env` dengan token baru dan mencatat ke `accounts.json`.

4. **CLI Interaktif & Command-Line Flags**:
   - Menu CLI interaktif dengan panah keyboard via `Inquirer`.
   - Command flags lengkap untuk automation / cron job (`commander`).

---

## 📂 Struktur Direktori `v2/`

```
v2/
├── package.json               # ESM package configuration & npm scripts
├── .env.example               # Template environment variables
├── .env                       # File konfigurasi lokal (APIFY_TOKEN)
├── accounts.json              # Riwayat akun Apify yang digenerate otomatis
├── output/                    # Direktori hasil ekspor (CSV, XLSX, JSON, XML)
└── src/
    ├── index.js               # CLI Entrypoint & interactive menu
    ├── scraper.js             # Modul integrasi Apify client & actor
    ├── accountCreator.js      # Modul otomasi pembuatan akun & token
    ├── exporters/
    │   ├── index.js           # Exporter dispatcher & normalizer
    │   ├── normalize.js       # Standarisasi data field dari Apify
    │   ├── excel.js           # ExcelJS generator
    │   ├── csv.js             # RFC 4180 CSV generator
    │   ├── json.js            # JSON generator
    │   └── xml.js             # XML generator
    └── utils/
        └── logger.js          # Terminal logger berwarna & bertanggal
```

---

## 🚀 Panduan Instalasi

### 1. Masuk ke direktori `v2`
```bash
cd v2
```

### 2. Install Dependensi
```bash
npm install
```

### 3. Konfigurasi `.env`
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Isi `APIFY_TOKEN` Anda jika sudah punya:
```env
APIFY_TOKEN=apify_api_xxxxxxxxxxxxxxxxxxxxxxxxxxxx
```
> *Jika belum punya token Apify, Anda bisa memakai fitur otomatisasi akun (lihat langkah di bawah).*

---

## 📖 Cara Penggunaan

### 1. Mode Interaktif (Paling Mudah)
Jalankan menu interaktif:
```bash
npm start
```
Atau:
```bash
node src/index.js
```
Anda akan diberikan pilihan:
- 🚀 **1. Scrape Google Maps Places (via Apify)**: Masukkan kata kunci, lokasi, jumlah tempat, dan format ekspor.
- 🤖 **2. Auto-Create New Apify Account (mail.tm + Token)**: Buat akun gratis baru & dapatkan API Token secara otomatis.
- 📋 **3. View Saved Apify Accounts**: Lihat daftar akun yang tersimpan di `accounts.json`.
- ❌ **4. Exit**

---

### 2. Command Line Langsung (Scraper)

#### Contoh Dasar:
```bash
node src/index.js scrape -q "kafe di bandung" -m 20
```

#### Contoh dengan Lokasi dan Format Tertentu:
```bash
# Ekspor ke Excel saja
node src/index.js scrape -q "hotel bintang 4" -l "Jakarta, Indonesia" -m 50 -f xlsx

# Ekspor ke CSV
node src/index.js scrape -q "dentist" -l "Surabaya" -m 30 -f csv

# Ekspor ke semua format (CSV, XLSX, JSON, XML)
node src/index.js scrape -q "restoran sunda" -m 25 -f all
```

#### Opsi Parameter `scrape`:
| Flag | Deskripsi | Default |
|---|---|---|
| `-q, --query <queries...>` | Kata kunci pencarian | `"kafe di bandung"` |
| `-l, --location <location>` | Filter area / lokasi geografis | `""` |
| `-m, --max <number>` | Maksimal tempat yang diambil | `20` |
| `-f, --format <format>` | Format ekspor: `csv`, `xlsx`, `json`, `xml`, `all` | `all` |
| `-o, --output <dir>` | Direktori penyimpanan file | `./output` |
| `--lang <language>` | Bahasa Google Maps | `id` |
| `--country <countryCode>` | Kode negara | `id` |
| `--reviews` | Ambil review ulasan pengguna | `false` |
| `--no-images` | Jangan unduh URL foto | `false` |
| `-t, --token <token>` | Override API Token Apify | `process.env.APIFY_TOKEN` |

---

### 3. Otomasi Buat Akun Apify (Account Creator)

Jika kuota free tier Apify habis atau Anda ingin membuat akun baru otomatis:
```bash
npm run create-account
```
Atau:
```bash
node src/index.js create-account
```

**Alur yang dijalankan script:**
1. Membuat disposable inbox via `mail.tm`.
2. Menjalankan browser Puppeteer Stealth dan membuka halaman register Apify.
3. Mengisi email & password acak yang aman.
4. Mendeteksi email verifikasi yang masuk ke `mail.tm`.
5. Membuka link verifikasi secara otomatis di browser.
6. Mengambil **Personal API Token** dari console Apify.
7. Menyimpan token ke `.env` (`APIFY_TOKEN=...`) dan riwayat lengkap ke `accounts.json`.

---

## 📊 Kolom Data yang Dihasilkan

Setiap baris data hasil ekspor memuat 17 atribut lengkap:
1. `title` (Nama bisnis / tempat)
2. `category` (Kategori utama)
3. `rating` (Nilai rating bintang 1.0 - 5.0)
4. `reviewsCount` (Jumlah total ulasan)
5. `phone` (Nomor telepon)
6. `website` (Alamat situs web resmi)
7. `address` (Alamat fisik lengkap)
8. `latitude` (Koordinat GPS Lintang)
9. `longitude` (Koordinat GPS Bujur)
10. `price` (Tingkat harga / rentang harga)
11. `openingHours` (Jadwal jam operasional)
12. `description` (Deskripsi tempat / ringkasan)
13. `facilities` (Daftar fasilitas / amenities)
14. `googleMapsUrl` (Link langsung tempat di Google Maps)
15. `imageUrls` (Daftar link foto)
16. `placeId` (Google Place ID unik)
17. `cid` (Google Customer ID)

---

## 🛠️ Testing & Validasi

Untuk memastikan semua modul berjalan baik tanpa error syntax:
```bash
node --check src/index.js
node --check src/scraper.js
node --check src/accountCreator.js
node --check src/exporters/index.js
```

# Panduan Setup Web Frontend CSR (Vercel / Cloudflare Pages / Static Hosting)

Dokumen ini berisi panduan lengkap cara menghubungkan web frontend statis gratisan (seperti **Vercel**, **Cloudflare Pages**, atau **GitHub Pages**) ke Headless API Laravel `carikaryawan.com` untuk kebutuhan Client-Side Rendering (CSR), indexing ribuan loker ke Google Search Console (GSC), dan monetisasi Adsterra/Smartlink.

---

## 1. Struktur Folder di Frontend (Vercel)

Di project frontend Anda (misal di repo GitHub baru yang di-deploy ke Vercel), Anda hanya butuh **2 file saja**:

```text
my-loker-frontend/
├── index.html       <-- Tampilan utama shell CSR
└── vercel.json      <-- Routing rewrite & proxy sitemap Google
```

---

## 2. Isi File `index.html`

File ini berfungsi sebagai wadah penampung UI yang akan di-render secara instan oleh JavaScript di browser pengunjung/Googlebot.

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Info Lowongan Kerja Terbaru</title>
    <meta name="description" content="Portal lowongan kerja dan karir terverifikasi se-Indonesia">
</head>
<body style="margin: 0; background-color: #f8fafc;">

    <!-- 1. Wadah tempat CSR Engine merender UI loker -->
    <div id="csr-app"></div>

    <!-- 2. Script CSR Engine Mandiri dari Backend CariKaryawan -->
    <script 
        src="https://carikaryawan.com/csr/csr-engine.js"
        data-api="https://carikaryawan.com/api/csr"
        data-container="csr-app"
    ></script>

</body>
</html>
```

---

## 3. Isi File `vercel.json`

File konfigurasi ini wajib ada agar:
1. Google Search Console bisa membaca sitemap langsung dari domain Anda (`https://domain-anda.vercel.app/sitemap.xml`).
2. Semua URL clean path (misal: `/loker/admin-jakarta`) diarahkan ke `index.html` (SPA fallback).

> **PENTING**: Ganti `https://domain-anda.vercel.app` dengan domain Vercel / domain custom Anda sendiri!

```json
{
  "rewrites": [
    {
      "source": "/sitemap.xml",
      "destination": "https://carikaryawan.com/api/csr/sitemap.xml?domain=https://domain-anda.vercel.app"
    },
    {
      "source": "/loker/(.*)",
      "destination": "/index.html"
    }
  ]
}
```

---

## 4. Cara Submit ke Google Search Console (GSC)

1. Daftarkan domain Vercel Anda di [Google Search Console](https://search.google.com/search-console).
2. Verifikasi kepemilikan (bisa via tag HTML atau DNS jika pakai custom domain).
3. Buka menu **Sitemaps** di sidebar kiri.
4. Masukkan URL:
   ```text
   sitemap.xml
   ```
5. Klik **Submit**.
   - Googlebot akan membaca sitemap XML yang di-generate langsung dari ribuan database loker `carikaryawan.com`.
   - Tiap kali auto-harvest di VPS scrape loker baru, isi sitemap ini akan update secara otomatis tanpa Anda perlu upload ulang file XML!

---

## 5. Referensi Endpoint Backend (`carikaryawan.com`)

| Endpoint | Kegunaan | Parameter Opsional |
| :--- | :--- | :--- |
| `GET /api/csr/jobs` | Mengambil daftar loker (JSON) | `?q=admin&city=jakarta&page=1&limit=15` |
| `GET /api/csr/jobs/{id_or_slug}` | Mengambil detail 1 loker lengkap (JSON) | `{id_or_slug}` |
| `GET /api/csr/sitemap.xml` | Menghasilkan XML Sitemap standar Google | `?domain=https://...&page=1&limit=1000` |
| `GET /api/csr/config` | Mengambil settingan iklan dinamis | - |
| `GET /csr/csr-engine.js` | File script CDN untuk render frontend | - |

---

## 6. Pengaturan Monetisasi (Adsterra Direct Link / Smartlink)

Secara default, tombol **"Lamar Pekerjaan Sekarang"** di halaman detail loker diatur melalui backend. 
Jika ingin mengubah tujuan link tombol lamar menjadi Direct Link Adsterra Anda:
- Bisa diatur via setting database atau remote config di `/api/csr/config`.
- Semua web frontend Vercel yang terhubung akan otomatis berubah detik itu juga tanpa perlu re-deploy di Vercel!

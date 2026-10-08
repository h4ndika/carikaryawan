# Deployment & Operasional

## Setup VPS baru

`deploy/setup-vps.sh` (Ubuntu 24.04, dijalankan sebagai root) menginstal Nginx, PHP 8.3, MariaDB, Redis, Node 20, Supervisor, cron scheduler, dan worker queue.

> ⚠️ Script itu berisi password DB hardcoded. Ganti sebelum dipakai, lalu jangan commit password asli.

## Update rutin (setelah merge ke `main`)

```bash
cd /var/www/disekitar
git pull origin main
composer install --no-dev --optimize-autoloader   # jika composer.lock berubah
npm ci && npm run build                           # jika aset frontend berubah
php artisan migrate --force                       # jika ada migration baru
php artisan optimize:clear && php artisan optimize
php artisan queue:restart
```

## Worker queue

Supervisor (`/etc/supervisor/conf.d/disekitar-worker.conf`, dibuat oleh `setup-vps.sh`) menjalankan 4 grup worker terpisah. Tujuannya agar scrape yang lama tidak memblokir job AI yang cepat:

| Program | Queue | Proses | Timeout | Dipakai oleh |
|---|---|---|---|---|
| `disekitar-worker-fast` | `ai,default` | 2 | default | `GeneratePostContentJob`, `ProcessBatchAiQueueJob`, `CurateArticleJob` |
| `disekitar-worker-photos` | `photos` | 2 | 120s | `ProcessBatchPhotoScrapeJob` |
| `disekitar-worker-maps` | `maps` | 2 | 900s | `RunGoogleMapsScrapeJob` (engine Maps) |
| `disekitar-worker-jobs` | `jobs` | 1 | 900s | `RunGoogleMapsScrapeJob` (JobStreet/Glints) |

Jika menambah `onQueue('baru')` di sebuah job, tambahkan juga worker untuk queue tersebut di Supervisor dan di `setup-vps.sh`. Tanpa itu, job akan menumpuk tanpa error.

```bash
sudo supervisorctl status               # semua harus RUNNING
sudo supervisorctl reread && sudo supervisorctl update   # setelah mengubah .conf
```

## Scheduler

Cron `* * * * * php artisan schedule:run` wajib aktif. Daftar jadwal ada di `routes/console.php` (waktu dalam UTC; WIB = UTC+7).

## Environment penting

| Key | Fungsi |
|---|---|
| `OPENAI_API_KEY`, `OPENAI_BASE_URL`, `OPENAI_MODEL` | Konten AI |
| `OPENAI_MODEL_CV` | Model khusus tools karier |
| `OPENAI_MODEL_LANDMARK` | Model khusus landmark |
| `APIFY_TOKEN` | Fallback jika tabel `apify_accounts` kosong |
| `PHOTO_SCRAPE_MAX_ACTIVE_RUNS` | Batas run Apify foto paralel (default 3) |
| `DB_QUEUE_RETRY_AFTER` | Harus > 80 (default 90) |

Konfigurasi lain (iklan, analytics, Threads, maintenance, saklar otomasi) disimpan di tabel `settings` dan diatur via `/rahasia`.

## Troubleshooting cepat

| Gejala | Cek |
|---|---|
| Konten AI tidak jalan | `ai_generation_jobs` status `failed`; kuota/key OpenAI |
| Foto tidak pernah selesai | Worker mendengarkan `photos`? `photo_scrape_jobs.error_message` |
| Harvest 0 data | Normal untuk kota kecil (status "Selesai (0 data)"); cek saldo Apify |
| Situs 503 | Maintenance mode aktif di Settings |
| Perubahan setting tidak terlihat | Pastikan diubah lewat admin, bukan SQL langsung (cache) |

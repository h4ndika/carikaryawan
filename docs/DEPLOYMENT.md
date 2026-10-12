# Deployment & Operasional

## Setup VPS baru

`deploy/setup-vps.sh` (Ubuntu 24.04, dijalankan sebagai root) menginstal Nginx, PHP 8.3, MariaDB, Redis, Node 20, Supervisor, cron scheduler, dan worker queue untuk `carikaryawan.com` di `/var/www/carikaryawan`.

Password database **tidak ada di script**. Wajib dikirim lewat env; script berhenti sebelum menginstal apa pun jika `DB_PASS` kosong atau tidak valid (minimal 12 karakter dari `A-Z a-z 0-9 @ % + = : , . _ ! -`):

```bash
sudo DB_PASS='<password-kuat>' bash deploy/setup-vps.sh
```

Script meng-clone `git@github.com:h4ndika/carikaryawan.git` (butuh deploy key SSH di server). Script tidak mencetak password di akhir.

## Update rutin (setelah merge ke `main`)

**Upgrade pertama dari DISEKITAR wajib mengikuti prosedur purge di bawah, bukan langsung menjalankan urutan update rutin ini.**

```bash
cd /var/www/carikaryawan
git pull origin main
composer install --no-dev --optimize-autoloader   # jika composer.lock berubah
npm ci && npm run build                           # jika aset frontend berubah
php artisan migrate --force                       # jika ada migration baru
php artisan optimize:clear && php artisan optimize
php artisan queue:restart
```

## Purge database warisan DISEKITAR (destruktif, tidak bisa dibatalkan)

Repo menyertakan migrasi yang **menghapus permanen** semua post non-loker (termasuk soft-deleted) beserta data turunannya, lalu men-drop tabel `articles`, `article_curation_jobs`, `auto_harvest_queues`, `photo_scrape_jobs`, `landmarks` dan kolom khusus tempat di `posts`. Menjalankan `php artisan migrate` di database produksi **akan mengeksekusinya**. `down()` menolak rollback: satu-satunya cara memulihkan data adalah restore dari backup.

Urutan wajib:

1. **Backup penuh** database produksi dan simpan di luar server aplikasi:
   ```bash
   mysqldump --single-transaction --routines --triggers carikaryawan | gzip > carikaryawan-pre-purge-$(date +%F).sql.gz
   ```
   Uji bahwa file backup bisa di-restore sebelum lanjut.
2. **Staging dulu**: restore backup ke database salinan, jalankan `php artisan migrate` di sana. Verifikasi: jumlah loker dan `place_id` (`job_...`) tidak berubah, `ai_generation_jobs` loker utuh, tidak ada error FK, `/`, `/lowongan-kerja`, detail loker, `/tools`, `/sitemap.xml`, `/api/csr/jobs` normal.
3. **Kosongkan antrean (drain) dengan kode lama masih terpasang**: nonaktifkan pemicu harvest/ekstraksi dan batasi akses aplikasi; hentikan cron `schedule:run` (maintenance saja tidak menjamin scheduler berhenti). Biarkan worker menyelesaikan job berjalan, dan proses sisa `ai_generation_jobs` melalui `ai:process-batch` sampai tidak ada status `pending`/`processing`. Pastikan tabel `jobs` kosong, lalu hentikan worker aplikasi lewat Supervisor. Jangan deploy kode baru sebelum drain selesai: handler artikel/foto lama sudah dihapus.
4. Di produksi: `git pull origin main`, `php artisan migrate --force`, lalu `php artisan optimize:clear && php artisan optimize`.
5. Nyalakan kembali worker (`supervisorctl start all`, `php artisan queue:restart`) dan cron scheduler, matikan maintenance mode.
6. Kirim ulang sitemap di Google Search Console; URL tempat/blog lama akan 301/404.

**Rollback:** tidak ada `migrate:rollback`. Jika perlu mundur, hentikan aplikasi, restore dump dari langkah 1 ke database, dan deploy ulang commit sebelum purge.

Catatan: cache `settings` dan Redis tidak perlu dibersihkan manual selain `optimize:clear`. Setting lama yang tak terpakai (mis. slot iklan landmark) boleh dibiarkan. Cache API daftar/detail/sitemap menggunakan namespace baru agar respons non-loker lama tidak tersaji; banner PNG CariKaryawan memakai direktori `job-banners/carikaryawan` terpisah dari cache merek lama.

## Worker queue

Supervisor (`/etc/supervisor/conf.d/carikaryawan-worker.conf`, dibuat oleh `setup-vps.sh`) menjalankan 2 grup worker terpisah, agar ekstraksi yang lama tidak memblokir job AI yang cepat:

| Program | Queue | Proses | Timeout | Dipakai oleh |
|---|---|---|---|---|
| `carikaryawan-worker-fast` | `ai,default` | 2 | default | `GeneratePostContentJob`, `ProcessBatchAiQueueJob` |
| `carikaryawan-worker-jobs` | `jobs` | 1 | 900s | `RunGoogleMapsScrapeJob` (JobStreet/Glints) |

Jika menambah `onQueue('baru')` di sebuah job, tambahkan juga worker untuk queue tersebut di Supervisor dan di `setup-vps.sh`. Tanpa itu, job akan menumpuk tanpa error.

Jika server lama masih punya program `disekitar-worker-*` (termasuk `photos` dan `maps`), hentikan dan hapus file `.conf`-nya, lalu `supervisorctl reread && supervisorctl update`.

```bash
sudo supervisorctl status               # semua harus RUNNING
sudo supervisorctl reread && sudo supervisorctl update   # setelah mengubah .conf
```

## Scheduler

Cron `* * * * * php artisan schedule:run` wajib aktif. Daftar jadwal ada di `routes/console.php` (waktu dalam UTC; WIB = UTC+7): `ai:process-batch` tiap menit, `jobs:auto-harvest` harian, `threads:broadcast --type=jobs` 4 slot/hari, `threads:monitor-replies` tiap 15 menit, refresh token Threads mingguan.

## Environment penting

| Key | Fungsi |
|---|---|
| `OPENAI_API_KEY`, `OPENAI_BASE_URL`, `OPENAI_MODEL` | Konten AI. Default URL `openai-compatible` di `config/ai.php` masih host lama; set `OPENAI_COMPATIBLE_URL`/`OPENAI_BASE_URL` secara eksplisit |
| `OPENAI_MODEL_CV` | Model khusus tools karier |
| `APIFY_TOKEN` | Fallback jika tabel `apify_accounts` kosong |
| `DB_QUEUE_RETRY_AFTER` | Harus lebih besar dari timeout job terlama yang berjalan di worker terkait (default 90; job `jobs` memakai timeout 900s, pastikan antrean itu tidak di-retry prematur) |

Konfigurasi lain (iklan, analytics, Threads, maintenance, saklar otomasi) disimpan di tabel `settings` dan diatur via `/rahasia`.

## Troubleshooting cepat

| Gejala | Cek |
|---|---|
| Konten AI tidak jalan | `ai_generation_jobs` status `failed`; kuota/key OpenAI |
| Harvest 0 data | Normal untuk kota kecil (status "Selesai (0 data)"); cek saldo Apify |
| Ekstraksi menumpuk | Worker `carikaryawan-worker-jobs` RUNNING? |
| Situs 503 | Maintenance mode aktif di Settings |
| Perubahan setting tidak terlihat | Pastikan diubah lewat admin, bukan SQL langsung (cache) |

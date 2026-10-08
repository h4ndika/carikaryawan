# Testing

## Menjalankan

```bash
php artisan test
# atau
vendor/bin/pest
```

Tes memakai SQLite in-memory, jadi ekstensi `pdo_sqlite` harus aktif. Di Laragon, buka `php.ini` dan hapus `;` di baris `extension=pdo_sqlite`. Tanpa mengubah `php.ini`:

```bash
php -d extension=pdo_sqlite vendor/bin/pest
```

Semua HTTP ke luar (Apify, OpenAI, Threads) **wajib di-fake** dengan `Http::fake()`. Tes tidak boleh memakai kredit API.

## Yang sudah dites

| File | Melindungi |
|---|---|
| `tests/Feature/ExampleTest.php` | Smoke test: halaman publik utama tidak 500, detail post dan 404, maintenance 503 tapi API tetap hidup, saklar CSR API, kedaluwarsa loker 21 hari |
| `tests/Unit/JobPlaceIdTest.php` | Formula ID unik loker tidak berubah |
| `tests/Feature/PhotoScrapePipelineTest.php` | Antrean foto: batching, lock, deadline, recovery, timeout, Filament action |

## Kapan menambah tes

Tidak perlu mengejar coverage. Tambahkan tes hanya jika:

1. **Ada bug yang lolos ke production.** Tulis tes yang mereproduksi bug, baru perbaiki.
2. **Mengubah logika yang menyentuh data lama**: format slug/URL, `place_id`, aturan status/publish/kedaluwarsa.
3. **Membuat halaman publik baru**: cukup tambahkan URL-nya ke dataset `renders the main public pages`.
4. **Mengubah parser data eksternal** (normalisasi item Apify JobStreet/Glints/Maps). Simpan contoh JSON di `tests/Fixtures/` dan pastikan hasilnya.

Tes Dusk (`tests/Browser`) tidak dijalankan secara default.

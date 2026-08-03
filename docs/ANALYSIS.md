# Analisis Project DISEKITAR

> Dokumen ringkas hasil audit kode + dokumen internal.
> Cakupan: identitas, tech stack, arsitektur, domain, routing, model, frontend, status progres, konvensi, insight graphify, ringkasan eksekutif.
> Bahasa: Indonesia.

---

## 1. Identitas & Visi Produk

**DISEKITAR** adalah platform web / blog berbasis lokasi yang menampilkan konten *"apa saja yang ada di sekitarmu"* — tempat (hotel, kuliner, wisata), kegiatan / event, lowongan kerja, dan konten berbasis wilayah (kota / provinsi). Tagline: **"Apa saja yang ada di sekitarmu"** (sumber: `IDEA.md`).

- **Sumber utama**: `IDEA.md`, `README.md`, `docs/00-VISION.md` (Vision Document v1.0, status Draft, update 2026-06-29).
- **Target user (Role Matrix)**:
  - **Visitor** — pengunjung publik yang mencari tempat/event/lowongan di sekitarnya.
  - **Business Owner** — pemilik usaha yang mengajukan konten (hotel, resto, kegiatan, lowongan).
  - **Editor** — mengkurasi & mempublikasi post.
  - **Admin** — mengelola user, permission, konten, dan konfigurasi.
- **Value proposition**: agregator konten hiperlokal dengan SEO-friendly URL (`/{category}/{city}/{slug}`) dan admin panel privat (Filament) untuk kurasi cepat.
- **Prinsip utama**: konsistensi arsitektur, SEO-first, dokumentasi wajib untuk developer + AI agent, workflow berbasis task item.

---

## 2. Tech Stack

**Backend**
- **PHP** `^8.2`
- **Laravel Framework** `^12.0`
- **Filament** `~4.0` — admin panel (namespace `App\Filament\Rahasia`, artinya URL admin di-*obfuscate* sebagai `/rahasia`).
- **Livewire** — halaman publik dinamis (lihat `app/Livewire/*` + `resources/views/livewire/*`). **Konfirmasi: bukan Inertia, bukan SPA — kombinasi Blade + Livewire.**
- **Laravel Sanctum** `^4.0` — API auth.
- **Spatie Laravel Permission** `^8.1` — role & permission.
- **Dedoc Scramble** `^0.12.23` — auto-generate API docs dari type-hint.
- **OpenAI PHP for Laravel** `^0.15.0` — integrasi LLM (kemungkinan untuk konten/SEO assist).
- **Maatwebsite Excel** `^3.1` — export/import Excel.
- **Dev tools**: Pint, Pail, Dusk, Sail, Mockery, Faker, Filament Upgrade.

**Frontend**
- **Vite** `^6.2.4` + **laravel-vite-plugin** `^1.2.0`.
- **Tailwind CSS** `^4.0.0` via **`@tailwindcss/vite`** — **tanpa `tailwind.config.js` (Tailwind v4 pakai konfigurasi in-CSS di `resources/css/app.css`)**.
- **axios** `^1.8.2`, **concurrently** `^9.0.1`.
- Entry: `resources/css/app.css`, `resources/js/app.js` (+ `bootstrap.js`).
- **Tidak ada** React/Vue/Inertia — hanya Blade + Livewire + Tailwind.

**Sumber**: `composer.json`, `package.json`, `vite.config.js`.

---

## 3. Arsitektur

Ringkasan dari `docs/02-ARCHITECTURE.md` (555 baris) + `docs/architecture/MODULES.md` + `docs/architecture/DOMAIN_MODEL.md`.

Arsitektur DISEKITAR mengikuti Laravel **monolith standar** dengan pemisahan tanggung jawab:

- **Presentation Layer**
  - **Public site** — Livewire full-page component (`Home`, `Search`, `PostList`, `PostShow`) di-mount langsung sebagai route.
  - **Admin panel** — Filament v4, di namespace `App\Filament\Rahasia\*` (path admin disamarkan `/rahasia`).
  - **SEO artefak** — `SitemapController` (sitemap.xml) + view `sitemap.blade.php`.

- **Modul utama**
  1. **Auth & Access Control** — `App\Models\User` + Spatie Permission (roles: visitor, business_owner, editor, admin).
  2. **Content (Post)** — inti platform: `Post` + `PostDetail` + `PostImage`. Satu tabel `posts` polymorph-secara-konseptual (kategori menentukan apakah post itu tempat / event / lowongan).
  3. **Taxonomy** — `Category` (hotel, kuliner, wisata, event, lowongan, …).
  4. **Geo / Region** — `Province` → `City` (hierarki wilayah, punya slug SEO).
  5. **SEO** — kolom SEO tambahan di `posts` (title, description, canonical, dsb — lihat migration `add_seo_fields_to_posts_table`) + sitemap route.
  6. **Filament Admin** — resource untuk Posts, Cities, Provinces, Categories, masing-masing dengan `*Form`, `*Table`, `Create/Edit/List` pages.
  7. **Console Commands** — `GenerateCitySlug`, `GenerateProvinceSlug` (backfill slug).

- **Interaksi antar modul**
  - `Post` `belongsTo` `Category` + `City`; `City` `belongsTo` `Province`.
  - URL publik `/{category}/{city}/{slug}` menggabungkan tiga modul: taxonomy + geo + content dalam satu route bermakna SEO.
  - Filament Admin menulis ke tabel yang sama; Livewire membaca untuk publik.

---

## 4. Domain Model & Database

**Entitas inti** (dari kode + `docs/architecture/DOMAIN_MODEL.md`):

| Entitas | Peran | Relasi utama |
|---|---|---|
| `User` | Auth + role assignment | `hasMany` posts (implisit via editor), roles/permissions via Spatie |
| `Post` | Konten utama (tempat/event/lowongan) | `belongsTo` Category, `belongsTo` City; `hasOne` PostDetail; `hasMany` PostImage |
| `PostDetail` | Field ekstensi (long-form / meta konten) | `belongsTo` Post |
| `PostImage` | Galeri gambar per post | `belongsTo` Post |
| `Category` | Taksonomi konten | `hasMany` Post |
| `City` | Kota (dengan slug) | `belongsTo` Province, `hasMany` Post |
| `Province` | Provinsi | `hasMany` City |

**Daftar migration** (`database/migrations/`, 14 file):

- `0001_01_01_000000_create_users_table.php`
- `0001_01_01_000001_create_cache_table.php`
- `0001_01_01_000002_create_jobs_table.php` *(queue jobs, bukan lowongan kerja)*
- `2025_06_20_083448_create_personal_access_tokens_table.php` *(Sanctum)*
- `2025_06_20_095025_create_categories_table.php`
- `2025_06_20_095214_create_provinces_table.php`
- `2025_06_20_095404_create_cities_table.php`
- `2025_08_02_075421_create_posts_table.php`
- `2025_08_02_080049_create_post_details_table.php`
- `2025_08_02_080744_create_post_iamges_table.php` *(typo asli di nama migration)*
- `2025_08_14_082132_alter_posts_thumbnail_is_null.php`
- `2025_08_15_040008_change_name_table_post_images.php` *(rename dari typo)*
- `2026_07_18_033144_alter_posts_idx_seo_lookup.php` *(index composite untuk lookup SEO)*
- `2026_07_18_080000_add_seo_fields_to_posts_table.php`

**Catatan**: entitas "Event" & "Job" (lowongan) yang disebut di visi belum jadi tabel terpisah — sementara di-*embed* sebagai row `posts` dengan `category_id` yang sesuai. Ini keputusan sadar (satu tabel konten polimorfik via kategori).

Tabel Spatie Permission (`roles`, `permissions`, `model_has_roles`, dst.) **belum ada migration-nya** di folder — perlu di-*publish* saat setup (`php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`).

---

## 5. Routing & Controller

**`routes/web.php`** (28 baris) — semuanya Livewire full-page + 1 controller SEO:

| Path | Handler | Nama route |
|---|---|---|
| `GET /` | `App\Livewire\Home` | `home` |
| `GET /cari` | `App\Livewire\Search` | `search` |
| `GET /sitemap.xml` | `SitemapController` | `sitemap` |
| `GET /{category}/{city}/{slug}` | `App\Livewire\PostShow` | `posts.show` |
| `GET /{category}/{city}` | `App\Livewire\PostList` | `posts.list` |

Regex constraint semua slug: `[a-z0-9\-]+`.

**`routes/api.php`** (8 baris) — hanya endpoint default: `GET /user` (Sanctum-protected).

**Controllers** di `app/Http/Controllers/`:
- `Controller.php` — base abstract.
- `SitemapController.php` — invokable, generate sitemap.

Selebihnya, halaman publik ditangani **Livewire components** (`app/Livewire/`): `Home`, `Search`, `PostList`, `PostShow`. Admin CRUD ditangani **Filament resources** (`app/Filament/Rahasia/Resources/*`) — tidak butuh controller manual.

---

## 6. Model & Relasi Eloquent

Model di `app/Models/` (7 file):

- **`User`** — HasApiTokens (Sanctum) + HasRoles (Spatie). Tabel `users`.
- **`Post`** — inti konten.
  - `belongsTo(Category::class)`
  - `belongsTo(City::class)`
  - `hasOne(PostDetail::class)`
  - `hasMany(PostImage::class)`
  - Punya field SEO (title, description, canonical, thumbnail nullable, index `idx_seo_lookup`).
- **`PostDetail`** — `belongsTo(Post::class)`. Menyimpan konten long-form / atribut tambahan yang tak sering di-query.
- **`PostImage`** — `belongsTo(Post::class)`. Multi-gambar per post.
- **`Category`** — `hasMany(Post::class)`.
- **`City`** — `belongsTo(Province::class)`, `hasMany(Post::class)`. Punya slug auto-generate (lihat command `GenerateCitySlug`).
- **`Province`** — `hasMany(City::class)`. Slug via `GenerateProvinceSlug`.

Total 10 deklarasi relasi terdeteksi via grep (`belongsTo|hasMany|hasOne|belongsToMany`). Tidak ada `belongsToMany` — semua relasi 1-N.

---

## 7. Frontend

**`resources/views/`** (10 file Blade):

- **Layout**: `layouts/app.blade.php` — master layout (head, meta, Vite assets, slot konten).
- **Partials reusable**:
  - `partials/searchable-select.blade.php` — komponen dropdown searchable (dipakai untuk filter kota/kategori — hasil commit **`5cdbffc`**).
  - `partials/post-card.blade.php` — kartu ringkasan post.
- **Halaman Livewire** (`resources/views/livewire/`):
  - `home.blade.php` — landing page.
  - `search.blade.php` — halaman pencarian `/cari` (memuat searchable-select untuk filter kota + kategori).
  - `post-list.blade.php` — listing `/{category}/{city}` (juga pakai searchable-select).
  - `post-show.blade.php` — detail `/{category}/{city}/{slug}`.
- **Lain**: `welcome.blade.php` (default Laravel, kemungkinan tidak dipakai), `errors/404.blade.php`, `sitemap.blade.php`.

**Fitur searchable select untuk filter kota/kategori** ada di `resources/views/partials/searchable-select.blade.php` dan di-*include* pada `livewire/search.blade.php` + `livewire/post-list.blade.php`. Interaksi dropdown di-drive Livewire (server-round-trip) atau via minimal JS di `resources/js/app.js`.

**`resources/js/`** (2 file):
- `app.js` — entry Vite (kemungkinan import bootstrap + Alpine untuk Livewire).
- `bootstrap.js` — axios + CSRF setup.

**`resources/css/app.css`** — satu file, memakai directive **Tailwind v4** (`@import "tailwindcss";` + `@theme { … }` untuk custom token). Tidak ada `tailwind.config.js` (khas v4).

**`vite.config.js`** — 13 baris, hanya register `laravel({ input: ['resources/css/app.css','resources/js/app.js'], refresh: true })` + `tailwindcss()` plugin. Minimal, bersih.

---

## 8. Status Progress

**Sumber**: `docs/tasks/CURRENT.md`, `NEXT.md`, `DONE.md`, `BACKLOG.md`, `items/FE-00{1,2,3}.md`, `docs/01-ROADMAP.md`, `docs/12-TODO.md`.

### Selesai (`DONE.md`, 133 baris)
- **CORE-001** — Initial Laravel Setup (25 Jul 2025).
- **CMS-001** — Post Management (14 Aug 2025).
- **CMS-002** — (tercantum berikutnya, konteks CMS admin/Filament resources).
- **FE-001** — Public Frontend Foundation (Status: **Done, 100%**, priority High, dibuat 2026-07-18). Layout, home, list, show, search — semua Livewire + Blade dasar.
- **FE-002** — Vite Asset Build (setup Vite 6 + Tailwind 4 in-CSS).
- **FE-003** — Smoke Path Frontend (verifikasi 5 route publik berjalan tanpa error).
- Tambahan implisit: SEO fields di posts (migration 2026-07-18), searchable-select filter (commit 5cdbffc, 2026-08-03).

### Sedang berjalan (`CURRENT.md`)
- **Belum ada task aktif.** Sprint kosong.

### Backlog prioritas (`NEXT.md`, `BACKLOG.md`)
- **Belum ada task di antrean next.**
- **Belum ada task di backlog.**
  → Papan tugas sedang *"jeda"*: milestone frontend baru selesai, backlog belum diisi ulang.

### Bug / tech debt terbuka
- File `docs/tasks/BUGS.md` & `TECH-DEBT.md` ada di repo (belum di-parse di sini, tapi keberadaannya menandakan kanal pelacakan tersedia).
- **Debt terlihat dari kode**:
  - Typo migration `create_post_iamges_table` → sudah di-rename lewat migration, tapi nama file lama tetap ada (bikin history noisy).
  - Tabel Spatie Permission belum di-publish sebagai migration di repo.
  - Belum ada test (folder `tests/` ada tapi belum diverifikasi isinya).
  - `docs/12-TODO.md` & `docs/architecture/MODULES.md` tersimpan sebagai UTF-16 / binary — tidak bisa dibaca lintas OS tanpa konversi (masalah encoding).

`docs/01-ROADMAP.md` (545 baris, status *Active Development*, v1.0, update 2026-06-29) mendefinisikan roadmap detail per fase — proyek saat ini di fase **"Public Frontend selesai, siap masuk fase berikutnya (kemungkinan: fitur pencarian lanjutan, submission form untuk business owner, atau permission enforcement)"**.

---

## 9. Konvensi & Standar

Ringkas dari `docs/05-CODING-STANDARD.md` (643 baris), `docs/10-CONTRIBUTING.md`, `docs/11-AGENTS.md` (453 baris), `docs/prompts/PROMPT_DEVELOPMENT.md`.

- **Naming**: PascalCase untuk class, camelCase untuk method/property, snake_case untuk tabel + kolom + route path. Slug wilayah + kategori pakai kebab-case (`[a-z0-9\-]+`).
- **Struktur folder**: standar Laravel 12 — `app/Http`, `app/Models`, `app/Livewire`, `app/Filament/Rahasia/Resources/<Entity>/{Pages,Schemas,Tables}`, `app/Console/Commands`. Admin Filament wajib di namespace `Rahasia` (bukan default `Admin`).
- **Git workflow**: commit atomik, pesan singkat imperatif (contoh nyata: `fix routes/relations + searchable select filter kota/kategori`), branch dari main, PR wajib melewati review manual + prompt review AI (`docs/prompts/PROMPT_REVIEW.md`).
- **AI Agent workflow** (`docs/11-AGENTS.md` + `PROMPT_DEVELOPMENT.md`):
  1. Selalu baca dokumen `docs/` yang relevan **sebelum** menulis kode.
  2. Kerja per **task item** (`docs/tasks/items/<ID>.md`) — tulis status + progres di sana.
  3. Jangan mengubah struktur tanpa update dokumen arsitektur.
  4. Setiap fitur harus punya jejak: task item → migration → model/livewire/filament → view → update DONE.md.
  5. Bahasa dokumen wajib Indonesia.
- **Editor admin obfuscation**: URL admin sengaja `/rahasia` (bukan `/admin`) sebagai security-through-obscurity ringan.

---

## 10. Insight dari Graphify

Sumber: `graphify-out/GRAPH_REPORT.md` (613 baris, 23 KB). Statistik: **5073 nodes · 16116 edges · 163 komunitas** (157 dipakai, 6 di-*trim*). Ekstraksi 89% EXTRACTED, 11% INFERRED (rata-rata confidence 0.7). Dibangun dari commit `5cdbffcb`.

**Community hubs (top navigation nodes)**:
1. `code-editor.js`
2. `components/chart.js`
3. `stat/chart.js`
4. `resolve`, `update`, `constructor`
5. `markdown-editor.js`, `_update`, `get`, `components/select.js`, `fromObject`, `slice`

> **Temuan penting**: hub-hub teratas **didominasi oleh vendor JS Filament** (chart, code-editor, markdown-editor, select) — bukan kode aplikasi. Komunitas 0 (114 node) & 1 (104 node) & 2 (243 node) semua adalah *bundled vendor files* Filament. Ini artinya:
> - Kode aplikasi custom (Livewire, Models, Filament Resources) sebenarnya **kecil dan fokus** — mayoritas mass di graf adalah aset admin panel.
> - Tidak ada "god node" di sisi PHP aplikasi — struktur domain masih *lean*.
> - Hot files sisi aplikasi: `Post.php`, `PostList.php` (Livewire), `PostShow.php`, `searchable-select.blade.php` (semua adalah simpul yang menghubungkan Category + City + Post).

**Query yang berguna** untuk kontributor:
- `graphify explain "Place"` → tidak ada entitas `Place` (naming di visi ≠ naming di kode; entitas nyatanya `Post`).
- `graphify explain "Post"` → akan menampilkan Post + PostDetail + PostImage + relasinya ke Category/City.
- `graphify path "User" "Post"` → jalur otorisasi (User → role → permission → post action).

---

## 11. Ringkasan Eksekutif

- **Kondisi proyek**: fase pasca-milestone **FE-001/002/003 selesai** (public frontend + Vite/Tailwind v4 + smoke test). Papan `CURRENT/NEXT/BACKLOG` **kosong** — proyek di titik jeda menunggu penentuan sprint berikutnya.
- **Kekuatan utama**: stack modern & konsisten (Laravel 12 + PHP 8.2 + Filament 4 + Livewire + Tailwind 4), dokumentasi sangat lengkap (`docs/` berisi 20+ file bertema jelas), URL struktur SEO-friendly sejak awal, admin panel siap pakai lewat Filament.
- **Kelemahan / risiko**: (1) tidak ada tabel terpisah untuk Event & Job — semua di-*embed* ke `posts` via kategori, akan menyulitkan bila kebutuhan field spesifik tumbuh; (2) tabel Spatie Permission belum di-publish, artinya role enforcement belum aktif; (3) tidak terlihat automated test yang berjalan; (4) beberapa file docs (`MODULES.md`, `12-TODO.md`) tersimpan UTF-16/biner sehingga sulit dibaca cross-tool.
- **Utang teknis**: nama migration typo `post_iamges` (sudah di-rename via migration lain, tapi history noisy), belum ada seeder/factory demo, belum ada CI, konten default `welcome.blade.php` masih ada.
- **Langkah berikut yang paling logis**:
  1. Publish + jalankan migration Spatie Permission, definisikan role Visitor/Business Owner/Editor/Admin.
  2. Buat submission form Business Owner (Livewire) supaya loop konten *user-generated* dimulai.
  3. Isi ulang `docs/tasks/BACKLOG.md` & `NEXT.md` sesuai `docs/01-ROADMAP.md`.
  4. Tambahkan minimal 1 test HTTP smoke untuk 5 route publik.
  5. Setup CI (Pint + Pest/PHPUnit + Dusk) untuk mencegah regresi.
- **Rekomendasi setup kontributor baru**:
  ```
  cp .env.example .env
  composer install
  npm install
  php artisan key:generate
  php artisan migrate --seed
  php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
  npm run dev   # atau: npm run build
  php artisan serve
  ```
  Wajib baca dulu: `README.md` → `IDEA.md` → `docs/00-VISION.md` → `docs/02-ARCHITECTURE.md` → `docs/11-AGENTS.md` → `docs/tasks/README.md`. Admin panel di `/rahasia`.

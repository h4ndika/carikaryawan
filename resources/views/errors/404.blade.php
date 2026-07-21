<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman tidak ditemukan — DISEKITAR</title>
    <meta name="robots" content="noindex,follow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f7fb] font-sans text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen max-w-lg flex-col items-center justify-center px-4 py-16 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-600 to-accent-500 text-2xl font-extrabold text-white shadow-lg shadow-brand-600/30">
            404
        </div>
        <h1 class="mt-6 text-2xl font-extrabold tracking-tight text-slate-900">Halaman tidak ditemukan</h1>
        <p class="mt-3 text-slate-500">
            Kategori, kota, atau post yang kamu cari tidak ada / belum dipublish.
        </p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a
                href="{{ route('home') }}"
                class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-brand-600/25 transition hover:bg-brand-700"
            >
                Kembali ke beranda
            </a>
            <a
                href="{{ route('search') }}"
                class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-brand-300 hover:text-brand-700"
            >
                Cari dulu
            </a>
        </div>
    </main>
</body>
</html>
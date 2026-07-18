<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman tidak ditemukan — DISEKITAR</title>
    <meta name="robots" content="noindex,follow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen max-w-lg flex-col items-center justify-center px-4 py-16 text-center">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">404</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight">Halaman tidak ditemukan</h1>
        <p class="mt-3 text-slate-600">
            Kategori, kota, atau post yang kamu cari tidak ada / belum dipublish.
        </p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a
                href="{{ route('home') }}"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
            >
                Kembali ke beranda
            </a>
        </div>
    </main>
</body>
</html>
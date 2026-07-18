<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'DISEKITAR') }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Apa saja yang ada di sekitarmu — tempat, kuliner, wisata, lowongan.' }}">
    <meta name="robots" content="{{ $metaRobots ?? 'index,follow' }}">
    @if (! empty($canonicalUrl))
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif
    @if (! empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:title" content="{{ $title ?? config('app.name', 'DISEKITAR') }}">
        <meta property="og:description" content="{{ $metaDescription ?? '' }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4">
            <a href="{{ route('home') }}" class="text-lg font-bold tracking-tight text-emerald-700">
                DISEKITAR
            </a>
            <nav class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
                <a href="{{ route('home') }}" class="hover:text-emerald-700">Beranda</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-5xl px-4 py-6 text-center text-sm text-slate-500">
            &copy; {{ date('Y') }} DISEKITAR — apa saja di sekitarmu.
        </div>
    </footer>

    @livewireScripts
</body>
</html>
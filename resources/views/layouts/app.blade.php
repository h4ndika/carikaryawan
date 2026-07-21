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
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? config('app.name', 'DISEKITAR') }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Apa saja yang ada di sekitarmu — tempat, kuliner, wisata, lowongan.' }}">
    @if (! empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @if (! empty($jsonLd))
        <script type="application/ld+json">{!! $jsonLd !!}</script>
    @endif
</head>
<body class="min-h-screen bg-[#f4f7fb] font-sans text-slate-900 antialiased">
    {{-- Sticky glass header --}}
    <header class="sticky top-0 z-40 border-b border-white/60 bg-white/80 backdrop-blur-xl">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a href="{{ route('home') }}" class="group flex items-center gap-2.5" wire:navigate>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-accent-500 text-sm font-extrabold text-white shadow-md shadow-brand-600/30 transition group-hover:scale-105">
                    D
                </span>
                <span class="text-lg font-extrabold tracking-tight text-brand-900">
                    DISE<span class="text-brand-600">KITAR</span>
                </span>
            </a>

            <nav class="flex items-center gap-1 sm:gap-2">
                <a
                    href="{{ route('home') }}"
                    wire:navigate
                    class="rounded-full px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700"
                >
                    Beranda
                </a>
                <a
                    href="{{ route('search') }}"
                    wire:navigate
                    class="inline-flex items-center gap-1.5 rounded-full bg-brand-600 px-3.5 py-1.5 text-sm font-semibold text-white shadow-sm shadow-brand-600/25 transition hover:bg-brand-700"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                    </svg>
                    <span class="hidden sm:inline">Cari</span>
                </a>
            </nav>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-slate-200/80 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
            <div class="grid gap-8 sm:grid-cols-3">
                <div class="sm:col-span-1">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-600 to-accent-500 text-xs font-extrabold text-white">D</span>
                        <span class="font-extrabold tracking-tight text-brand-900">DISEKITAR</span>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-slate-500">
                        Platform lokal untuk temukan tempat, kuliner, wisata, event & lowongan di sekitarmu.
                    </p>
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Jelajah</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li><a href="{{ route('home') }}" wire:navigate class="hover:text-brand-700">Beranda</a></li>
                        <li><a href="{{ route('search') }}" wire:navigate class="hover:text-brand-700">Pencarian</a></li>
                        <li><a href="{{ route('sitemap') }}" class="hover:text-brand-700">Sitemap</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Tentang</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-500">
                        Dibangun untuk SEO lokal per kategori & kota. Konten terkurasi, cepat, ringan.
                    </p>
                </div>
            </div>
            <div class="mt-8 flex flex-col items-center justify-between gap-3 border-t border-slate-100 pt-6 text-xs text-slate-400 sm:flex-row">
                <span>&copy; {{ date('Y') }} DISEKITAR. All rights reserved.</span>
                <span>Apa saja di sekitarmu ✨</span>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
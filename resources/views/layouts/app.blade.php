<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    @php
        $pageTitle = $title ?? config('app.name', 'DISEKITAR');
        $desc = $metaDescription ?? 'Jurnal & panduan editorial lokal untuk menemukan tempat, kuliner, wisata, dan cerita bermakna di sekitarmu.';
        $currentUrl = !empty($canonicalUrl) ? $canonicalUrl : url()->current();
        $shareImage = !empty($ogImage) ? $ogImage : asset('images/placeholder.svg');
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $desc }}">
    <meta name="robots" content="{{ $metaRobots ?? 'index,follow' }}">
    @if (! empty($canonicalUrl))
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif

    <!-- Open Graph / Facebook -->
    <meta property="og:site_name" content="DISEKITAR">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $desc }}">
    <meta property="og:image" content="{{ $shareImage }}">

    <!-- Twitter / X Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $currentUrl }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $desc }}">
    <meta name="twitter:image" content="{{ $shareImage }}">

    <!-- Favicons & App Icons -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#18181b">

    <!-- Anti-FOUC Theme Init -->
    <script>
        (function() {
            try {
                if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,400;1,600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @if (! empty($jsonLd))
        @if (is_array($jsonLd))
            <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
        @else
            <script type="application/ld+json">{!! $jsonLd !!}</script>
        @endif
    @endif

    @include('partials.analytics-head')
</head>
<body class="min-h-screen bg-[#fafaf9] dark:bg-[#0c0a09] text-zinc-900 dark:text-zinc-100 antialiased selection:bg-zinc-900 dark:selection:bg-amber-500 selection:text-white dark:selection:text-zinc-950">
    @include('partials.analytics-body-start')
    <div class="min-h-screen flex flex-col">
        <!-- Top Editorial Masthead & Navigation -->
        <header class="bg-white dark:bg-zinc-900/95 dark:backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800/80 sticky top-0 z-50 transition-colors duration-200">
            <!-- Masthead Top Info Bar -->
            <div class="border-b border-zinc-100 dark:border-zinc-800 hidden sm:block">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex items-center justify-between text-[11px] uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-medium">
                    <div>
                        <span>Jurnal Kurasi Lokal</span>
                        <span class="mx-2 text-zinc-300 dark:text-zinc-700">/</span>
                        <span>Edisi {{ date('Y') }}</span>
                    </div>
                    <div>
                        <span>{{ \Illuminate\Support\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Main Nav Bar -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" wire:navigate class="group flex items-center gap-2.5">
                        <span class="font-serif-title text-2xl sm:text-3xl font-bold tracking-tight text-zinc-950 dark:text-zinc-50 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors">
                            disekitar.
                        </span>
                    </a>

                    @php
                        $navCategories = \Illuminate\Support\Facades\Cache::remember('nav_categories', 3600, function () {
                            return \App\Models\Category::orderBy('name')->take(4)->get();
                        });
                    @endphp

                    <nav class="hidden md:flex items-center gap-6 text-xs uppercase tracking-widest font-semibold text-zinc-600 dark:text-zinc-400">
                        <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors">Beranda</a>
                        @foreach ($navCategories as $navCat)
                            <a href="{{ $navCat->url() }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors">
                                {{ $navCat->name }}
                            </a>
                        @endforeach
                        <a href="{{ route('blog.index') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors flex items-center gap-1 text-amber-800 dark:text-amber-400">
                            <span>Blog</span>
                        </a>
                        <a href="{{ route('search') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors">
                            Jelajah
                        </a>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('search') }}" 
                       wire:navigate 
                       aria-label="Cari tempat dan cerita"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-xs font-medium text-zinc-700 dark:text-zinc-200 transition-colors"
                       title="Cari cerita atau tempat">
                        <i class="fa-solid fa-magnifying-glass text-zinc-500 dark:text-zinc-400 text-xs" aria-hidden="true"></i>
                        <span class="hidden sm:inline">Cari tempat & cerita...</span>
                    </a>

                    <!-- Theme Toggle Switch -->
                    <div x-data="{
                        darkMode: localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                        init() {
                            this.sync();
                            document.addEventListener('livewire:navigated', () => {
                                this.sync();
                            });
                        },
                        sync() {
                            this.darkMode = localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
                            if (this.darkMode) {
                                document.documentElement.classList.add('dark');
                            } else {
                                document.documentElement.classList.remove('dark');
                            }
                        },
                        toggle() {
                            this.darkMode = !this.darkMode;
                            if (this.darkMode) {
                                document.documentElement.classList.add('dark');
                                localStorage.theme = 'dark';
                            } else {
                                document.documentElement.classList.remove('dark');
                                localStorage.theme = 'light';
                            }
                        }
                    }" class="flex items-center">
                        <button 
                            type="button" 
                            @click="toggle()" 
                            class="relative inline-flex h-8 w-14 items-center rounded-full p-1 transition-colors duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 bg-zinc-200 dark:bg-zinc-800 hover:bg-zinc-300 dark:hover:bg-zinc-700 border border-zinc-300/80 dark:border-zinc-700 cursor-pointer" 
                            role="switch" 
                            :aria-checked="darkMode" 
                            aria-label="Ganti mode terang atau gelap"
                            title="Ganti Mode Tampilan (Light / Dark)">
                            <span 
                                class="pointer-events-none flex h-6 w-6 transform items-center justify-center rounded-full bg-white dark:bg-zinc-950 shadow-xs transition-transform duration-300 text-xs text-amber-600 dark:text-amber-400"
                                :class="darkMode ? 'translate-x-6' : 'translate-x-0'">
                                <i class="fa-solid" :class="darkMode ? 'fa-moon' : 'fa-sun'"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Main Slot -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        <!-- Clean Journal Footer -->
        <footer class="mt-20 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/80 transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                <div class="grid gap-10 md:grid-cols-12 items-start">
                    <div class="md:col-span-6 space-y-3">
                        <span class="font-serif-title text-2xl font-bold text-zinc-950 dark:text-zinc-50">disekitar.</span>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed max-w-md">
                            Publikasi kurasi independen yang mengangkat cerita di balik tempat, cita rasa, dan dinamika kehidupan kota di Indonesia.
                        </p>
                        <div class="pt-1 text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-4">
                            <a href="mailto:redaksi@disekitar.com" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors flex items-center gap-1.5">
                                <i class="fa-solid fa-envelope text-amber-500"></i> redaksi@disekitar.com
                            </a>
                        </div>
                    </div>

                    <div class="md:col-span-3 space-y-2">
                        <h4 class="text-xs uppercase tracking-widest font-bold text-zinc-900 dark:text-zinc-200">Eksplorasi</h4>
                        <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <li><a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors">Beranda</a></li>
                            <li><a href="{{ route('blog.index') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors">Blog Editorial</a></li>
                            <li><a href="{{ route('search') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors">Pencarian Cerita</a></li>
                            <li><a href="{{ route('sitemap') }}" class="hover:text-zinc-950 dark:hover:text-white transition-colors">Peta Situs (Sitemap)</a></li>
                        </ul>
                    </div>

                    <div class="md:col-span-3 space-y-2">
                        <h4 class="text-xs uppercase tracking-widest font-bold text-zinc-900 dark:text-zinc-200">Kebijakan & Redaksi</h4>
                        <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <li><a href="{{ route('page.about') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors">Tentang Kami</a></li>
                            <li><a href="{{ route('page.contact') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors">Hubungi Kami</a></li>
                            <li><a href="{{ route('page.privacy') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors">Kebijakan Privasi</a></li>
                            <li><a href="{{ route('page.terms') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors">Syarat & Ketentuan</a></li>
                            <li><a href="{{ route('page.disclaimer') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors">Penafian (Disclaimer)</a></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 pt-6 border-t border-zinc-100 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-zinc-400 dark:text-zinc-500">
                    <p>&copy; {{ date('Y') }} DISEKITAR. Hak cipta dilindungi.</p>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('page.privacy') }}" wire:navigate class="hover:underline">Privasi</a>
                        <span>&bull;</span>
                        <a href="{{ route('page.terms') }}" wire:navigate class="hover:underline">Ketentuan</a>
                        <span>&bull;</span>
                        <a href="{{ route('page.contact') }}" wire:navigate class="hover:underline">Kontak</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        window.handleImageFallback = function(img) {
            try {
                const raw = img.getAttribute('data-fallbacks');
                const fallbacks = raw ? JSON.parse(raw) : [];
                let nextIndex = parseInt(img.getAttribute('data-fallback-index') || '0', 10);
                if (Array.isArray(fallbacks) && nextIndex < fallbacks.length) {
                    img.setAttribute('data-fallback-index', nextIndex + 1);
                    img.src = fallbacks[nextIndex];
                    return;
                }
            } catch (e) {}
            img.onerror = null;
            img.src = '/images/placeholder.svg';
        };

        // Maintain dark mode on Livewire navigate
        document.addEventListener('livewire:navigated', () => {
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
    </script>

    @livewireScripts
    @include('partials.analytics-footer')
</body>
</html>
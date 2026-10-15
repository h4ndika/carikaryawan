<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    @php
        $pageTitle = $title ?? config('app.name', 'CariKaryawan');
        $desc = $metaDescription ?? 'Agregator lowongan kerja terbaru di seluruh Indonesia, lengkap dengan tools CV, surat lamaran, dan persiapan interview.';
        $currentUrl = !empty($canonicalUrl) ? $canonicalUrl : url()->current();
        $shareImage = !empty($ogImage) && !str_ends_with(strtolower($ogImage), '.svg') ? $ogImage : asset('android-chrome-512x512.png');
        $navCities = \App\Models\City::navigation();
        $jobSlug = \App\Models\Category::jobs()->value('slug') ?? 'lowongan-kerja';
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $desc }}">
    <meta name="robots" content="{{ $metaRobots ?? 'index,follow' }}">
    @if (! empty($canonicalUrl))
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif
    <meta property="og:site_name" content="CariKaryawan">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $desc }}">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:image:secure_url" content="{{ $shareImage }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $currentUrl }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $desc }}">
    <meta name="twitter:image" content="{{ $shareImage }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=ck1" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=ck1">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=ck1">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=ck1">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=ck1">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=ck1">
    <meta name="theme-color" content="#18181b">
    <script>
        window.syncTheme = function() {
            try {
                document.documentElement.classList.toggle('dark', localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches));
            } catch (e) {}
        };
        window.syncTheme();
        document.addEventListener('livewire:navigated', window.syncTheme);
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
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
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased selection:bg-blue-700 selection:text-white">
    @include('partials.analytics-body-start')
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-4 focus:z-[60] bg-white dark:bg-zinc-900 p-3">Lewati ke konten</a>
    <div class="min-h-screen flex flex-col">
        <header x-data="{ menuOpen: false }" @click.outside="menuOpen = false" @keydown.escape.window="if (menuOpen) { menuOpen = false; $refs.menuToggle.focus(); }" class="bg-white dark:bg-zinc-900 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 shrink-0" aria-label="CariKaryawan, beranda">
                    <img src="{{ asset('favicon.svg') }}?v=ck1" width="36" height="36" alt="" class="rounded-lg">
                    <span class="text-lg sm:text-xl font-extrabold tracking-tight">carikaryawan</span>
                </a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('search') }}" wire:navigate class="hidden sm:inline-flex px-4 py-2.5 text-sm font-semibold hover:text-blue-700 dark:hover:text-blue-400">Cari loker</a>
                    <a href="{{ route('tools.hub') }}" wire:navigate class="hidden sm:inline-flex px-4 py-2.5 text-sm font-semibold hover:text-blue-700 dark:hover:text-blue-400">Tools karier</a>
                    <button type="button" x-data="{ darkMode: document.documentElement.classList.contains('dark') }"
                        @click="darkMode = !document.documentElement.classList.contains('dark'); document.documentElement.classList.toggle('dark', darkMode); try { localStorage.theme = darkMode ? 'dark' : 'light'; } catch (e) {}"
                        :aria-pressed="darkMode" aria-label="Mode gelap" title="Ganti tema"
                        class="flex items-center justify-center w-11 h-11 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer">
                        <span x-show="!darkMode"><i class="fa-solid fa-moon" aria-hidden="true"></i></span>
                        <span x-show="darkMode" x-cloak><i class="fa-solid fa-sun" aria-hidden="true"></i></span>
                    </button>
                    <button x-ref="menuToggle" type="button" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-controls="mobile-menu" aria-label="Buka menu navigasi" class="sm:hidden flex items-center justify-center w-11 h-11 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer">
                        <i class="fa-solid" :class="menuOpen ? 'fa-xmark' : 'fa-bars'" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <nav aria-label="Navigasi utama" class="hidden sm:flex max-w-7xl mx-auto px-6 lg:px-8 gap-x-6 flex-wrap text-sm font-medium text-zinc-600 dark:text-zinc-300">
                <a href="{{ route('home') }}" wire:navigate class="py-3 hover:text-blue-700 dark:hover:text-blue-400">Beranda</a>
                @foreach ($navCities as $navCity)
                    <a href="{{ route('posts.list', ['category' => $jobSlug, 'city' => $navCity->slug]) }}" wire:navigate @if(request()->segment(2) === $navCity->slug) aria-current="page" @endif class="py-3 hover:text-blue-700 dark:hover:text-blue-400">{{ $navCity->name }}</a>
                @endforeach
            </nav>
            <nav id="mobile-menu" aria-label="Navigasi ponsel" x-show="menuOpen" x-cloak class="sm:hidden px-4 pb-4 max-h-[70svh] overflow-y-auto text-sm font-medium">
                <a href="{{ route('home') }}" wire:navigate class="block p-3">Beranda</a>
                @foreach ($navCities as $navCity)
                    <a href="{{ route('posts.list', ['category' => $jobSlug, 'city' => $navCity->slug]) }}" wire:navigate class="block p-3">{{ $navCity->name }}</a>
                @endforeach
                <a href="{{ route('search') }}" wire:navigate class="block p-3">Cari loker</a>
                <a href="{{ route('tools.hub') }}" wire:navigate class="block p-3">Tools karier</a>
            </nav>
        </header>
        <main id="main-content" class="flex-grow" tabindex="-1">{{ $slot }}</main>
        <footer class="mt-16 bg-zinc-100 dark:bg-zinc-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid gap-10 md:grid-cols-12">
                    <div class="md:col-span-6">
                        <span class="text-xl font-extrabold tracking-tight">carikaryawan</span>
                        <p class="mt-4 max-w-md text-base leading-relaxed text-zinc-600 dark:text-zinc-300">Lowongan dari berbagai sumber, satu tempat untuk mencari. Lamaran dikirim langsung lewat tautan asli pemberi kerja.</p>
                        <a href="mailto:kontak@carikaryawan.com" class="inline-block mt-4 text-sm hover:underline">kontak@carikaryawan.com</a>
                    </div>
                    <div class="md:col-span-3">
                        <h2 class="text-sm font-bold mb-3">Lowongan & karier</h2>
                        <ul class="space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                            @foreach ($navCities as $navCity)
                                <li><a href="{{ route('posts.list', ['category' => $jobSlug, 'city' => $navCity->slug]) }}" wire:navigate class="hover:underline">{{ $navCity->name }}</a></li>
                            @endforeach
                            <li><a href="{{ route('search') }}" wire:navigate class="hover:underline">Semua lowongan</a></li>
                            <li><a href="{{ route('tools.hub') }}" wire:navigate class="hover:underline">Tools karier</a></li>
                        </ul>
                    </div>
                    <div class="md:col-span-3">
                        <h2 class="text-sm font-bold mb-3">Informasi</h2>
                        <ul class="space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                            <li><a href="{{ route('page.about') }}" wire:navigate class="hover:underline">Tentang kami</a></li>
                            <li><a href="{{ route('page.contact') }}" wire:navigate class="hover:underline">Hubungi kami</a></li>
                            <li><a href="{{ route('page.privacy') }}" wire:navigate class="hover:underline">Kebijakan privasi</a></li>
                            <li><a href="{{ route('page.terms') }}" wire:navigate class="hover:underline">Syarat & ketentuan</a></li>
                            <li><a href="{{ route('page.disclaimer') }}" wire:navigate class="hover:underline">Penafian</a></li>
                            <li><a href="{{ route('sitemap') }}" class="hover:underline">Peta situs</a></li>
                        </ul>
                    </div>
                </div>
                <p class="mt-10 text-sm text-zinc-600 dark:text-zinc-400">&copy; {{ date('Y') }} CariKaryawan. Hak cipta dilindungi.</p>
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
    </script>
    @livewireScripts
    @include('partials.analytics-footer')
</body>
</html>

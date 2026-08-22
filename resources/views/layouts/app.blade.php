<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'DISEKITAR') }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Jurnal & panduan editorial lokal untuk menemukan tempat, kuliner, wisata, dan cerita bermakna di sekitarmu.' }}">
    <meta name="robots" content="{{ $metaRobots ?? 'index,follow' }}">
    @if (! empty($canonicalUrl))
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? config('app.name', 'DISEKITAR') }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Jurnal & panduan editorial lokal untuk menemukan tempat, kuliner, wisata, dan cerita bermakna di sekitarmu.' }}">
    @if (! empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,400;1,600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @if (! empty($jsonLd))
        <script type="application/ld+json">{!! $jsonLd !!}</script>
    @endif
</head>
<body class="min-h-screen bg-[#fafaf9] text-zinc-900 antialiased selection:bg-zinc-900 selection:text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Editorial Masthead & Navigation -->
        <header class="bg-white border-b border-zinc-200 sticky top-0 z-50">
            <!-- Masthead Top Info Bar -->
            <div class="border-b border-zinc-100 hidden sm:block">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex items-center justify-between text-[11px] uppercase tracking-wider text-zinc-500 font-medium">
                    <div>
                        <span>Jurnal Kurasi Lokal</span>
                        <span class="mx-2 text-zinc-300">/</span>
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
                        <span class="font-serif-title text-2xl sm:text-3xl font-bold tracking-tight text-zinc-950 group-hover:text-amber-800 transition-colors">
                            disekitar.
                        </span>
                    </a>

                    @php
                        $navCategories = \Illuminate\Support\Facades\Cache::remember('nav_categories', 3600, function () {
                            return \App\Models\Category::orderBy('name')->take(4)->get();
                        });
                    @endphp

                    <nav class="hidden md:flex items-center gap-6 text-xs uppercase tracking-widest font-semibold text-zinc-600">
                        <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 transition-colors">Beranda</a>
                        @foreach ($navCategories as $navCat)
                            <a href="{{ route('home', ['category' => $navCat->slug]) }}" wire:navigate class="hover:text-zinc-950 transition-colors">
                                {{ $navCat->name }}
                            </a>
                        @endforeach
                        <a href="{{ route('search') }}" wire:navigate class="hover:text-zinc-950 transition-colors text-amber-800">
                            Jelajah
                        </a>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('search') }}" 
                       wire:navigate 
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-zinc-100 hover:bg-zinc-200 text-xs font-medium text-zinc-700 transition-colors"
                       title="Cari cerita atau tempat">
                        <i class="fa-solid fa-magnifying-glass text-zinc-500 text-xs"></i>
                        <span class="hidden sm:inline">Cari tempat & cerita...</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Page Main Slot -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        <!-- Clean Journal Footer -->
        <footer class="mt-20 border-t border-zinc-200 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                <div class="grid gap-10 md:grid-cols-12 items-start">
                    <div class="md:col-span-6 space-y-3">
                        <span class="font-serif-title text-2xl font-bold text-zinc-950">disekitar.</span>
                        <p class="text-sm text-zinc-600 leading-relaxed max-w-md">
                            Publikasi kurasi independen yang mengangkat cerita di balik tempat, cita rasa, dan dinamika kehidupan kota di Indonesia.
                        </p>
                    </div>

                    <div class="md:col-span-3 space-y-2">
                        <h4 class="text-xs uppercase tracking-widest font-bold text-zinc-900">Navigasi</h4>
                        <ul class="space-y-2 text-sm text-zinc-600">
                            <li><a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 transition-colors">Beranda</a></li>
                            <li><a href="{{ route('search') }}" wire:navigate class="hover:text-zinc-950 transition-colors">Pencarian Cerita</a></li>
                            <li><a href="{{ route('sitemap') }}" class="hover:text-zinc-950 transition-colors">Peta Situs</a></li>
                        </ul>
                    </div>

                    <div class="md:col-span-3 space-y-2">
                        <h4 class="text-xs uppercase tracking-widest font-bold text-zinc-900">Tentang</h4>
                        <p class="text-xs text-zinc-500 leading-relaxed">
                            Setiap tempat memiliki cerita. Kami mendokumentasikan sudut-sudut menarik untuk menemani perjalananmu.
                        </p>
                    </div>
                </div>

                <div class="mt-12 pt-6 border-t border-zinc-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-zinc-400">
                    <p>&copy; {{ date('Y') }} DISEKITAR. Hak cipta dilindungi.</p>
                    <p>Kurasi Cerita & Tempat Indonesia</p>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
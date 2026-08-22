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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        .heading { font-family: 'Playfair Display', Georgia, serif; }
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    </style>

    @if (! empty($jsonLd))
        <script type="application/ld+json">{!! $jsonLd !!}</script>
    @endif
</head>
<body class="min-h-screen bg-[#faf7f2] text-[#2c1810] antialiased selection:bg-[#8d6e5e] selection:text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Editorial Navigation Bar -->
        <header class="bg-[#faf7f2]/95 backdrop-blur-md border-b border-[#e8d9c0] sticky top-0 z-50 transition-all">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-[#8d6e5e] rounded-full flex items-center justify-center text-white text-base font-bold shadow-sm group-hover:bg-[#2c1810] transition-colors">
                        D
                    </div>
                    <span class="text-2xl font-bold tracking-tight heading text-[#2c1810]">disekitar</span>
                </a>

                <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-[#2c1810]/80">
                    <a href="{{ route('home') }}" wire:navigate class="hover:text-[#8d6e5e] transition-colors">Beranda</a>
                    <a href="{{ route('search') }}" wire:navigate class="hover:text-[#8d6e5e] transition-colors">Jelajah Cerita</a>
                    <a href="{{ route('sitemap') }}" class="hover:text-[#8d6e5e] transition-colors">Indeks Kota</a>
                </nav>

                <div class="flex items-center gap-3">
                    <a href="{{ route('search') }}" wire:navigate class="p-2 text-[#2c1810]/80 hover:text-[#8d6e5e] transition-colors" title="Cari Tempat & Cerita">
                        <i class="fa-solid fa-magnifying-glass text-lg"></i>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Slot -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        <!-- Editorial Footer -->
        <footer class="mt-24 border-t border-[#e8d9c0] bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="grid gap-10 sm:grid-cols-3">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 bg-[#8d6e5e] rounded-full flex items-center justify-center text-white text-xs font-bold">
                                D
                            </div>
                            <span class="font-bold tracking-tight heading text-xl text-[#2c1810]">disekitar</span>
                        </div>
                        <p class="text-sm leading-relaxed text-[#2c1810]/70 max-w-sm">
                            Jurnal kurasi lokal untuk menemukan tempat, kuliner, penginapan, dan cerita bermakna di sekitarmu.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#8d6e5e] mb-4">Jelajah</h3>
                        <ul class="space-y-2.5 text-sm text-[#2c1810]/70 font-medium">
                            <li><a href="{{ route('home') }}" wire:navigate class="hover:text-[#8d6e5e] transition-colors">Beranda</a></li>
                            <li><a href="{{ route('search') }}" wire:navigate class="hover:text-[#8d6e5e] transition-colors">Pencarian Cerita</a></li>
                            <li><a href="{{ route('sitemap') }}" class="hover:text-[#8d6e5e] transition-colors">Peta Situs / Kota</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#8d6e5e] mb-4">Tentang Disekitar</h3>
                        <p class="text-sm leading-relaxed text-[#2c1810]/70">
                            Fokus pada cerita autentik, visual berkualitas, dan panduan praktis yang mudah diakses pembaca.
                        </p>
                    </div>
                </div>
                <div class="mt-12 pt-8 border-t border-[#e8d9c0] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#8d6e5e]">
                    <span>&copy; {{ date('Y') }} DISEKITAR. Hak cipta dilindungi undang-undang.</span>
                    <span class="font-medium">Jurnal Kurasi Lokal</span>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
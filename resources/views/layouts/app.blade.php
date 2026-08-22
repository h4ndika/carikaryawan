<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'DISEKITAR') }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Editorial platform untuk temukan tempat, kuliner, wisata, event, dan inspirasi di sekitarmu.' }}">
    <meta name="robots" content="{{ $metaRobots ?? 'index,follow' }}">
    @if (! empty($canonicalUrl))
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? config('app.name', 'DISEKITAR') }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Editorial platform untuk temukan tempat, kuliner, wisata, event, dan inspirasi di sekitarmu.' }}">
    @if (! empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        .heading { font-family: 'Playfair Display', serif; }
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
    @if (! empty($jsonLd))
        <script type="application/ld+json">{!! $jsonLd !!}</script>
    @endif
</head>
<body class="min-h-screen bg-[#f8f1e3] text-[#2c1810] antialiased">
    <div class="max-w-screen-2xl mx-auto">
        <header class="bg-white border-b border-[#e8d9c0] sticky top-0 z-50">
            <div class="px-6 sm:px-8 py-5 flex items-center justify-between">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-x-2.5 group">
                    <div class="w-9 h-9 bg-[#8d6e5e] rounded-full flex items-center justify-center text-white text-base font-extrabold transition group-hover:scale-105">
                        D
                    </div>
                    <span class="text-xl font-light tracking-tighter heading">disekitar</span>
                </a>

                <nav class="hidden md:flex items-center gap-x-8 text-sm font-medium">
                    <a href="{{ route('home') }}" wire:navigate class="hover:text-[#8d6e5e] transition-colors">Beranda</a>
                    <a href="{{ route('search') }}" wire:navigate class="hover:text-[#8d6e5e] transition-colors">Jelajah</a>
                    <a href="#" class="hover:text-[#8d6e5e] transition-colors">Tentang</a>
                </nav>

                <div class="flex items-center gap-x-4">
                    <a href="{{ route('search') }}" wire:navigate class="text-lg hover:text-[#8d6e5e] transition-colors">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                    <a href="#" class="px-5 py-2 text-xs font-medium border border-[#8d6e5e] text-[#8d6e5e] hover:bg-[#8d6e5e] hover:text-white transition-colors">
                        Subscribe
                    </a>
                </div>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="mt-20 border-t border-[#e8d9c0] bg-white">
            <div class="mx-auto max-w-screen-2xl px-6 sm:px-8 py-12">
                <div class="grid gap-12 sm:grid-cols-3">
                    <div>
                        <div class="flex items-center gap-x-2.5 mb-4">
                            <div class="w-8 h-8 bg-[#8d6e5e] rounded-full flex items-center justify-center text-white text-xs font-extrabold">
                                D
                            </div>
                            <span class="font-light tracking-tight heading text-lg">disekitar</span>
                        </div>
                        <p class="text-sm leading-relaxed text-[#2c1810]/70">
                            Platform editorial untuk menemukan tempat, kuliner, wisata, event, dan inspirasi di sekitarmu.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-[0.5px] text-[#8d6e5e] mb-4">Jelajahi</h3>
                        <ul class="space-y-2 text-sm text-[#2c1810]/70">
                            <li><a href="{{ route('home') }}" wire:navigate class="hover:text-[#8d6e5e] transition-colors">Beranda</a></li>
                            <li><a href="{{ route('search') }}" wire:navigate class="hover:text-[#8d6e5e] transition-colors">Pencarian</a></li>
                            <li><a href="{{ route('sitemap') }}" class="hover:text-[#8d6e5e] transition-colors">Sitemap</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-[0.5px] text-[#8d6e5e] mb-4">Tentang</h3>
                        <p class="text-sm leading-relaxed text-[#2c1810]/70">
                            Konten berkualitas tinggi, cepat, dan ringan. Dibuat untuk pembaca yang menghargai cerita sejati.
                        </p>
                    </div>
                </div>
                <div class="mt-10 pt-8 border-t border-[#e8d9c0] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#8d6e5e]/70">
                    <span>&copy; {{ date('Y') }} DISEKITAR. Semua hak dilindungi.</span>
                    <span>Apa saja di sekitarmu</span>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
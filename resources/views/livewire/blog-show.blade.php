<div class="min-h-screen py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb & Category -->
        <nav class="flex items-center gap-2 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-medium mb-6 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">Beranda</a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <a href="{{ route('blog.index') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">Blog</a>
            @if ($article->category)
                <span class="text-zinc-300 dark:text-zinc-700">/</span>
                <a href="{{ route('blog.index', ['category' => $article->category->slug]) }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">
                    {{ $article->category->name }}
                </a>
            @endif
            @if ($article->city)
                <span class="text-zinc-300 dark:text-zinc-700">/</span>
                <span class="text-zinc-700 dark:text-zinc-300 font-semibold">{{ $article->city->name }}</span>
            @endif
        </nav>

        <!-- Article Header -->
        <header class="w-full mb-8">
            <div class="max-w-4xl space-y-4 mb-6">
                <div class="flex flex-wrap items-center gap-3">
                    @if ($article->category)
                        <span class="px-2.5 py-1 rounded bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60 text-xs font-bold uppercase tracking-wider">
                            {{ $article->category->name }}
                        </span>
                    @endif
                    @if ($article->city)
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                            <i class="fa-solid fa-location-dot text-amber-800 dark:text-amber-400 text-[11px]"></i>
                            {{ $article->city->name }}
                        </span>
                    @endif
                </div>

                <h1 class="font-serif-title text-3xl sm:text-4xl md:text-5xl font-bold leading-tight text-zinc-950 dark:text-zinc-50 tracking-tight">
                    {{ $article->title }}
                </h1>

                @if ($article->excerpt)
                    <p class="text-lg sm:text-xl text-zinc-600 dark:text-zinc-400 font-normal leading-relaxed">
                        {{ $article->excerpt }}
                    </p>
                @endif
            </div>

            <!-- Author / Curator Metadata & Share (Full Width matching Image) -->
            <div class="w-full pt-4 border-t border-zinc-200 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-4 text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        D
                    </div>
                    <div>
                        <span class="text-zinc-900 dark:text-zinc-200 font-bold block">Kurasi Redaksi DISEKITAR</span>
                        <span>
                            {{ $article->published_at ? $article->published_at->translatedFormat('d F Y') : date('d F Y') }} · 
                            {{ $article->readingTime() }} · 
                            <i class="fa-regular fa-eye ml-1"></i> {{ number_format($article->views_count) }} dibaca
                        </span>
                    </div>
                </div>

                <!-- Action & Share Buttons -->
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-zinc-400 dark:text-zinc-500 text-[11px] uppercase tracking-wider font-semibold">Bagikan:</span>
                    <a href="https://wa.me/?text={{ urlencode($article->title . ' ' . $article->url()) }}" 
                       target="_blank" 
                       class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 flex items-center justify-center transition-colors"
                       title="WhatsApp">
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode($article->url()) }}" 
                       target="_blank" 
                       class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 flex items-center justify-center transition-colors"
                       title="X / Twitter">
                        <i class="fa-brands fa-x-twitter text-sm"></i>
                    </a>
                    <button onclick="navigator.clipboard.writeText('{{ $article->url() }}'); alert('Tautan artikel berhasil disalin!');"
                            class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 flex items-center justify-center transition-colors"
                            title="Salin Tautan">
                        <i class="fa-solid fa-link text-xs"></i>
                    </button>
                </div>
            </div>

            @if ($article->city && ($article->city->latitude || $article->city->longitude))
                <x-weather-aqi-widget :lat="$article->city->latitude" 
                                      :lon="$article->city->longitude" 
                                      :location-name="$article->city->name" />
            @endif
        </header>

        <!-- Featured Image -->
        @if ($article->featured_image_url)
            <div class="mb-6 sm:mb-8">
                <figure class="relative rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-800">
                    <button type="button" 
                            wire:click="openLightbox(@js($article->featured_image_url))"
                            class="block w-full cursor-zoom-in group">
                        <img src="{{ $article->featured_image_url }}" 
                             alt="{{ $article->title }}" 
                             onerror="this.onerror=null; this.src='/images/placeholder.svg';"
                             class="w-full max-h-[520px] object-cover transition duration-700 group-hover:scale-[1.01]"
                             loading="eager"
                             fetchpriority="high">
                        <div class="absolute bottom-4 right-4 flex items-center gap-2">
                            <span class="bg-black/60 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-xs font-medium opacity-90 group-hover:opacity-100 transition-opacity">
                                <i class="fa-solid fa-expand mr-1.5"></i> Perbesar Foto
                            </span>
                        </div>
                    </button>
                </figure>
            </div>
        @endif

        <!-- Main 2-Column Content Layout (8 Cols Article : 4 Cols Sidebar) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            
            <!-- Left Column: Story Prose & Content (8 cols) -->
            <div class="lg:col-span-8">
                <article class="prose-editorial max-w-none bg-white dark:bg-zinc-900 px-6 sm:px-10 pt-6 sm:pt-8 pb-8 sm:pb-12 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
                    {!! $article->content !!}
                </article>

                <!-- Ad Slot di Bawah Konten -->
                <div class="mt-8">
                    <x-ad-slot slotName="ad_post_before_gallery" />
                </div>
            </div>

            <!-- Right Column: Sidebar (4 cols) -->
            <aside class="lg:col-span-4 space-y-6 sticky top-24">
                
                <!-- Ad Slot Sidebar Top -->
                <x-ad-slot slotName="ad_post_sidebar_top" class="!mt-0 !mb-6" />

                <!-- Overview Card -->
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs space-y-5">
                    <div class="pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-[10px] uppercase font-bold tracking-widest text-amber-800 dark:text-amber-400 block mb-1">
                            Panduan Kurasi
                        </span>
                        <h3 class="font-serif-title font-bold text-lg text-zinc-950 dark:text-zinc-50 leading-snug">
                            {{ $article->title }}
                        </h3>
                    </div>

                    <div class="space-y-4 text-xs">
                        @if ($article->city)
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-amber-800 dark:text-amber-400 flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-location-dot text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Wilayah</span>
                                    <span class="text-zinc-800 dark:text-zinc-200 font-semibold leading-relaxed">{{ $article->city->name }}</span>
                                </div>
                            </div>
                        @endif

                        @if ($article->category)
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-amber-800 dark:text-amber-400 flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-tags text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Kategori</span>
                                    <span class="text-zinc-800 dark:text-zinc-200 font-semibold">{{ $article->category->name }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-amber-800 dark:text-amber-400 flex-shrink-0 mt-0.5">
                                <i class="fa-solid fa-clock text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Waktu Membaca</span>
                                <span class="text-zinc-800 dark:text-zinc-200 font-medium">{{ $article->readingTime() }}</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-amber-800 dark:text-amber-400 flex-shrink-0 mt-0.5">
                                <i class="fa-solid fa-sparkles text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Sumber Data</span>
                                <span class="text-zinc-800 dark:text-zinc-200 font-medium">Ulasan & Rating Komunitas Lokal DISEKITAR</span>
                            </div>
                        </div>
                    </div>

                    @if ($article->city)
                        <div class="pt-2">
                            <a href="{{ route('search', ['city' => $article->city->slug]) }}" 
                               wire:navigate
                               class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-zinc-900 dark:bg-zinc-100 hover:bg-zinc-800 dark:hover:bg-white text-white dark:text-zinc-950 text-xs font-semibold tracking-wide transition-colors">
                                <i class="fa-solid fa-compass"></i>
                                Jelajahi Semua Tempat di {{ $article->city->name }}
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Curated Places In This Listicle -->
                @if ($curatedPlaces->isNotEmpty())
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="pb-3 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                            <h4 class="font-serif-title font-bold text-base text-zinc-950 dark:text-zinc-50">
                                Tempat Dalam Kurasi
                            </h4>
                            <span class="text-[11px] font-semibold text-amber-800 dark:text-amber-400">
                                {{ $curatedPlaces->count() }} Pilihan
                            </span>
                        </div>

                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($curatedPlaces as $index => $place)
                                <div class="py-3 first:pt-0 last:pb-0 flex items-center gap-3 group">
                                    <div class="w-7 h-7 rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-400 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ $place->url() }}" wire:navigate class="font-semibold text-xs text-zinc-900 dark:text-zinc-100 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors truncate block">
                                            {{ $place->title }}
                                        </a>
                                        <div class="flex items-center gap-2 text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                            @if ($place->rating)
                                                <span class="text-amber-500 font-bold"><i class="fa-solid fa-star text-[9px]"></i> {{ number_format($place->rating, 1) }}</span>
                                            @endif
                                            <span>{{ $place->category?->name }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ $place->url() }}" wire:navigate class="text-zinc-400 group-hover:text-amber-800 dark:group-hover:text-amber-400 text-xs">
                                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>

        <!-- Related Articles / Extended Exploration -->
        @if ($relatedArticles->isNotEmpty())
            <section class="mt-20 pt-12 border-t border-zinc-200 dark:border-zinc-800">
                <div class="mb-8 flex items-baseline justify-between">
                    <div>
                        <span class="text-xs uppercase tracking-widest font-bold text-amber-800 dark:text-amber-400 block mb-1">Rekomendasi Bacaan</span>
                        <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-zinc-950 dark:text-zinc-50">
                            Artikel & Panduan Terkait Lainnya
                        </h2>
                    </div>
                    <a href="{{ route('blog.index') }}" 
                       wire:navigate
                       class="text-xs font-bold text-zinc-900 dark:text-zinc-200 hover:text-amber-800 dark:hover:text-amber-400 hidden sm:inline-flex items-center gap-1">
                        Lihat Semua Artikel <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach ($relatedArticles as $rel)
                        <article class="group flex flex-col bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
                            <a href="{{ $rel->url() }}" wire:navigate class="block aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800 relative">
                                <img src="{{ $rel->featured_image_url ?: asset('images/placeholder.svg') }}" 
                                     alt="{{ $rel->title }}" 
                                     onerror="this.onerror=null; this.src='/images/placeholder.svg';"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                     loading="lazy">
                                @if ($rel->category)
                                    <span class="absolute top-3 left-3 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-sm px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 shadow-xs border border-zinc-200/50 dark:border-zinc-800">
                                        {{ $rel->category->name }}
                                    </span>
                                @endif
                            </a>

                            <div class="flex flex-1 flex-col justify-between p-5 space-y-3">
                                <div>
                                    <h3 class="font-serif-title font-bold text-lg leading-snug text-zinc-950 dark:text-zinc-100 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors line-clamp-2">
                                        <a href="{{ $rel->url() }}" wire:navigate>
                                            {{ $rel->title }}
                                        </a>
                                    </h3>
                                    @if ($rel->excerpt)
                                        <p class="mt-2 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed line-clamp-2">
                                            {{ $rel->excerpt }}
                                        </p>
                                    @endif
                                </div>

                                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                    <div class="flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                                        <i class="fa-solid fa-location-dot text-amber-800 dark:text-amber-400 text-[11px]"></i>
                                        <span class="truncate">{{ $rel->city?->name ?? 'Indonesia' }}</span>
                                    </div>
                                    <span>{{ $rel->readingTime() }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

    </div>

    <!-- Lightbox Modal -->
    @if ($lightbox)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
             wire:click="closeLightbox"
             x-data
             x-on:keydown.escape.window="$wire.closeLightbox()">
            <button type="button"
                    class="absolute right-6 top-6 rounded-full bg-white/10 hover:bg-white/20 px-4 py-2 text-xs font-semibold text-white transition cursor-pointer"
                    wire:click.stop="closeLightbox">
                Tutup ✕
            </button>
            <img src="{{ $lightbox }}"
                 alt="Preview Foto"
                 referrerpolicy="no-referrer"
                 onerror="this.onerror=null; this.src='/images/placeholder.svg';"
                 class="max-h-[90vh] max-w-[90vw] rounded-xl object-contain shadow-2xl"
                 wire:click.stop>
        </div>
    @endif
</div>

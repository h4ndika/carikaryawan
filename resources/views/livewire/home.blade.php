<div class="min-h-screen">
    @php
        $isPrioritizeArticles = ($prioritizeArticles ?? false) && isset($latestArticles) && $latestArticles->isNotEmpty();
        $leadArticle = $isPrioritizeArticles ? $latestArticles->first() : null;
        $sideArticles = $isPrioritizeArticles ? $latestArticles->slice(1, 3) : collect();
        $remainingArticles = $isPrioritizeArticles ? $latestArticles->slice(4) : collect();

        $featuredPost = $posts->first();
        $sideFeatured = $posts->slice(1, 3);
        $feedPosts = $posts->slice(4);
        // Fallback if less than 4 posts
        if ($feedPosts->isEmpty()) {
            $feedPosts = $posts->slice(1);
        }
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        @if ($isPrioritizeArticles && $leadArticle)
        <!-- Lead Editorial Spread: PRIORITAS ARTIKEL BLOG (AdSense Review Optimized) -->
        <section class="border-b border-zinc-200 dark:border-zinc-800 pb-12 mb-12">
            <div class="flex items-center justify-between mb-6">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">
                    <i class="fa-solid fa-feather-pointed"></i> Jurnal Editorial Utama
                </span>
                <a href="{{ route('blog.index') }}" wire:navigate class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 hover:underline flex items-center gap-1">
                    <span>Semua Artikel</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                <!-- Lead Article (7 cols) -->
                <article class="lg:col-span-7 group">
                    <a href="{{ $leadArticle->url() }}" 
                       wire:navigate class="block space-y-4">
                        <div class="aspect-[16/10] overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/60 dark:border-zinc-800 relative shadow-sm">
                            <img src="{{ $leadArticle->featured_image_url ?: asset('images/placeholder.svg') }}" 
                                 alt="{{ $leadArticle->title }}" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                 loading="eager"
                                 fetchpriority="high"
                                 decoding="async">
                            @if ($leadArticle->city)
                                <span class="absolute bottom-2.5 right-2.5 px-2.5 py-1 rounded bg-black/70 backdrop-blur-xs text-[11px] font-semibold text-white tracking-wide">
                                    <i class="fa-solid fa-location-dot text-amber-400 mr-1 text-[10px]"></i>{{ $leadArticle->city->name }}
                                </span>
                            @endif
                        </div>

                        <div class="space-y-2.5">
                            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400">
                                <span>{{ $leadArticle->category->name ?? 'PANDUAN LOKAL' }}</span>
                                <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                <span class="text-zinc-500 dark:text-zinc-400 font-medium">{{ $leadArticle->readingTime() }}</span>
                            </div>

                            <h1 class="font-serif-title text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight text-zinc-950 dark:text-zinc-50 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors">
                                {{ $leadArticle->title }}
                            </h1>

                            <p class="text-zinc-600 dark:text-zinc-400 text-sm sm:text-base leading-relaxed line-clamp-3">
                                {{ $leadArticle->excerpt }}
                            </p>

                            <div class="flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400 pt-1 font-medium">
                                <span class="text-zinc-800 dark:text-zinc-300 flex items-center gap-1.5">
                                    <i class="fa-solid fa-pen-nib text-amber-800 dark:text-amber-400 text-[11px]"></i>
                                    Redaksi DISEKITAR
                                </span>
                                <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                <span>{{ $leadArticle->published_at ? $leadArticle->published_at->translatedFormat('d F Y') : date('d F Y') }}</span>
                            </div>
                        </div>
                    </a>
                </article>

                <!-- Side Articles (5 cols) -->
                <div class="lg:col-span-5 lg:border-l lg:border-zinc-200 dark:lg:border-zinc-800 lg:pl-10 space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <h2 class="text-xs uppercase tracking-widest font-bold text-zinc-900 dark:text-zinc-200">Rekomendasi Terkini</h2>
                        <span class="text-[11px] text-zinc-400 dark:text-zinc-500 font-medium">Kurasi Pilihan</span>
                    </div>

                    <div class="space-y-6 divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($sideArticles as $sArticle)
                            <article class="pt-6 first:pt-0 group">
                                <a href="{{ $sArticle->url() }}" 
                                   wire:navigate class="grid grid-cols-3 gap-4 items-start">
                                    <div class="col-span-2 space-y-1.5">
                                        <span class="text-[10px] uppercase font-bold tracking-wider text-amber-800 dark:text-amber-400 block">
                                            {{ $sArticle->category->name ?? 'EDITORIAL' }}
                                        </span>
                                        <h3 class="font-serif-title font-bold text-base leading-snug text-zinc-950 dark:text-zinc-100 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors line-clamp-2">
                                            {{ $sArticle->title }}
                                        </h3>
                                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                                            <span>{{ $sArticle->city->name ?? 'Indonesia' }}</span>
                                            <span>•</span>
                                            <span>{{ $sArticle->readingTime() }}</span>
                                        </div>
                                    </div>
                                    <div class="col-span-1 aspect-square rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/60 dark:border-zinc-800 flex items-center justify-center relative">
                                        <img src="{{ $sArticle->featured_image_url ?: asset('images/placeholder.svg') }}" 
                                             alt="{{ $sArticle->title }}" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                             loading="lazy">
                                    </div>
                                </a>
                            </article>
                        @empty
                            <p class="text-xs text-zinc-400 dark:text-zinc-500 py-4">Belum ada artikel tambahan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        @elseif ($featuredPost)
        <!-- Lead Editorial Spread (Default Hero Magazine Layout) -->
        @php
            $featuredThumb = $featuredPost->primary_image_url;
            $fallbackFeaturedUrls = $featuredPost->fallback_image_urls;
        @endphp
        <section class="border-b border-zinc-200 dark:border-zinc-800 pb-12 mb-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                <!-- Lead Story (7 cols) -->
                <article class="lg:col-span-7 group">
                    <a href="{{ $featuredPost->url() }}" 
                       wire:navigate class="block space-y-4">
                        <div class="aspect-[16/10] overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/60 dark:border-zinc-800 relative">
                            @if ($featuredThumb)
                                <img src="{{ $featuredThumb }}" 
                                     alt="{{ $featuredPost->title }}" 
                                     data-fallbacks='@json($fallbackFeaturedUrls ?? [])'
                                     onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                     loading="eager"
                                     fetchpriority="high"
                                     decoding="async">
                                @if ($featuredSource = $featuredPost->primary_image_source)
                                    <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded bg-black/60 backdrop-blur-xs text-[10px] font-medium text-white/90 tracking-wide pointer-events-none">
                                        Foto: {{ $featuredSource }}
                                    </span>
                                @endif
                            @else
                                <div class="w-full h-full bg-zinc-100 dark:bg-zinc-800 flex flex-col items-center justify-center text-zinc-400 dark:text-zinc-500 p-8">
                                    <i class="fa-solid fa-camera text-4xl text-zinc-300 dark:text-zinc-600 mb-2"></i>
                                    <span class="text-xs uppercase font-bold tracking-widest text-zinc-400 dark:text-zinc-500">DISEKITAR KURASI</span>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-2.5">
                            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400">
                                <span>{{ $featuredPost->category->name ?? 'REKOMENDASI' }}</span>
                                @if ($featuredPost->isJob() && $featuredPost->isExpired())
                                    <span class="text-red-600 dark:text-red-400 font-bold text-[10px] bg-red-50 dark:bg-red-950/50 px-2 py-0.5 rounded border border-red-200 dark:border-red-900">Ditutup</span>
                                @endif
                                <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                <span class="text-zinc-500 dark:text-zinc-400 font-medium">{{ max(1, round(str_word_count(strip_tags($featuredPost->content ?? '')) / 200)) }} min baca</span>
                            </div>

                            <h1 class="font-serif-title text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight text-zinc-950 dark:text-zinc-50 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors">
                                {{ $featuredPost->title }}
                            </h1>

                            <p class="text-zinc-600 dark:text-zinc-400 text-sm sm:text-base leading-relaxed line-clamp-3">
                                {{ $featuredPost->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($featuredPost->content ?? ''), 180) }}
                            </p>

                            <div class="flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400 pt-1 font-medium">
                                <span class="text-zinc-800 dark:text-zinc-300 flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-amber-800 dark:text-amber-400 text-[11px]"></i>
                                    {{ $featuredPost->city->name ?? 'Indonesia' }}
                                </span>
                                <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                <span>{{ $featuredPost->published_at ? \Illuminate\Support\Carbon::parse($featuredPost->published_at)->translatedFormat('d F Y') : date('d F Y') }}</span>
                            </div>
                        </div>
                    </a>
                </article>

                <!-- Side Lead Stories (5 cols) -->
                <div class="lg:col-span-5 lg:border-l lg:border-zinc-200 dark:lg:border-zinc-800 lg:pl-10 space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <h2 class="text-xs uppercase tracking-widest font-bold text-zinc-900 dark:text-zinc-200">Catatan Kurator</h2>
                        <span class="text-[11px] text-zinc-400 dark:text-zinc-500 font-medium">Sorotan Pekan Ini</span>
                    </div>

                    <div class="space-y-6 divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($sideFeatured as $sidePost)
                            @php
                                $sideThumb = $sidePost->primary_image_url;
                                $fallbackSideUrls = $sidePost->fallback_image_urls;
                            @endphp
                            <article class="pt-6 first:pt-0 group">
                                <a href="{{ $sidePost->url() }}" 
                                   wire:navigate class="grid grid-cols-3 gap-4 items-start">
                                    <div class="col-span-2 space-y-1.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] uppercase font-bold tracking-wider text-amber-800 dark:text-amber-400 block">
                                                @if ($sidePost->isJob())
                                                    <i class="fa-solid fa-briefcase text-[10px] mr-0.5"></i>
                                                @endif
                                                {{ $sidePost->category->name ?? 'CERITA' }}
                                            </span>
                                            @if ($sidePost->isJob() && $sidePost->isExpired())
                                                <span class="text-red-600 dark:text-red-400 font-bold text-[9px] bg-red-50 dark:bg-red-950/50 px-1.5 py-0.5 rounded border border-red-200 dark:border-red-900">Ditutup</span>
                                            @endif
                                        </div>
                                        <h3 class="font-serif-title font-bold text-base leading-snug text-zinc-950 dark:text-zinc-100 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors line-clamp-2">
                                            {{ $sidePost->title }}
                                        </h3>
                                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                                            <span>{{ $sidePost->city->name ?? 'Indonesia' }}</span>
                                            <span>•</span>
                                            <span>{{ max(1, round(str_word_count(strip_tags($sidePost->content ?? '')) / 200)) }} min</span>
                                        </div>
                                    </div>
                                    <div class="col-span-1 aspect-square rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/60 dark:border-zinc-800 flex items-center justify-center relative">
                                        @if ($sideThumb)
                                            <img src="{{ $sideThumb }}" 
                                                 alt="{{ $sidePost->title }}" 
                                                 data-fallbacks='@json($fallbackSideUrls)'
                                                 onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                 loading="lazy">
                                            @if ($sideSource = $sidePost->primary_image_source)
                                                <span class="absolute bottom-1 right-1 px-1 py-0.5 rounded bg-black/60 backdrop-blur-xs text-[8px] font-medium text-white/90 pointer-events-none">
                                                    {{ $sideSource }}
                                                </span>
                                            @endif
                                        @else
                                            <div class="w-full h-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 dark:text-zinc-600">
                                                <i class="fa-solid fa-camera text-base"></i>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            </article>
                        @empty
                            <p class="text-xs text-zinc-400 dark:text-zinc-500 py-4">Belum ada catatan sampingan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- Ad Slot: Bawah Hero -->
        <x-ad-slot slotName="ad_home_hero" class="!my-0 mb-12" />

        <!-- Panduan & Jurnal Editorial Pilihan (Jika bukan mode Prioritas Artikel) -->
        @if (!$isPrioritizeArticles && isset($latestArticles) && $latestArticles->isNotEmpty())
        <section class="mb-14 pb-12 border-b border-zinc-200 dark:border-zinc-800">
            <div class="flex items-end justify-between mb-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 mb-2">
                        <i class="fa-solid fa-feather-pointed"></i> Jurnal Kurasi
                    </span>
                    <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-zinc-950 dark:text-zinc-50">
                        Panduan Eksplorasi & Editorial
                    </h2>
                </div>
                <a href="{{ route('blog.index') }}" wire:navigate class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300 flex items-center gap-1 transition-colors">
                    <span>Lihat Semua Artikel</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($latestArticles->take(3) as $art)
                    <article class="flex flex-col bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-xl overflow-hidden hover:shadow-md transition-all duration-300 group">
                        <a href="{{ $art->url() }}" wire:navigate class="block relative aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                            <img src="{{ $art->featured_image_url ?: asset('images/placeholder.svg') }}" 
                                 alt="{{ $art->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 loading="lazy">
                            <span class="absolute top-3 left-3 px-2 py-0.5 rounded bg-black/60 backdrop-blur-xs text-[10px] font-semibold text-white tracking-wide uppercase">
                                {{ $art->city->name ?? 'Indonesia' }}
                            </span>
                        </a>
                        <div class="p-5 flex flex-col flex-grow">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 mb-2">
                                {{ $art->category->name ?? 'Kurasi Tempat' }}
                            </div>
                            <h3 class="font-serif-title font-bold text-base text-zinc-950 dark:text-zinc-50 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors line-clamp-2 mb-2">
                                <a href="{{ $art->url() }}" wire:navigate>
                                    {{ $art->title }}
                                </a>
                            </h3>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 line-clamp-2 leading-relaxed mb-4 flex-grow">
                                {{ $art->excerpt }}
                            </p>
                            <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-[11px] text-zinc-500 dark:text-zinc-400 font-medium">
                                <span>{{ $art->published_at ? $art->published_at->translatedFormat('d M Y') : 'Baru' }}</span>
                                <span class="group-hover:translate-x-1 transition-transform text-amber-800 dark:text-amber-400 font-semibold">Baca &rarr;</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
        @elseif ($isPrioritizeArticles && $remainingArticles->isNotEmpty())
        <!-- Sisa Artikel Editorial jika prioritas aktif -->
        <section class="mb-14 pb-12 border-b border-zinc-200 dark:border-zinc-800">
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-serif-title text-2xl font-bold text-zinc-950 dark:text-zinc-50">
                    Artikel Editorial Lainnya
                </h2>
                <a href="{{ route('blog.index') }}" wire:navigate class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 hover:underline flex items-center gap-1">
                    <span>Semua Artikel</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($remainingArticles as $art)
                    <article class="flex flex-col bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-xl overflow-hidden hover:shadow-md transition-all duration-300 group">
                        <a href="{{ $art->url() }}" wire:navigate class="block relative aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                            <img src="{{ $art->featured_image_url ?: asset('images/placeholder.svg') }}" 
                                 alt="{{ $art->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 loading="lazy">
                            <span class="absolute top-3 left-3 px-2 py-0.5 rounded bg-black/60 backdrop-blur-xs text-[10px] font-semibold text-white tracking-wide uppercase">
                                {{ $art->city->name ?? 'Indonesia' }}
                            </span>
                        </a>
                        <div class="p-5 flex flex-col flex-grow">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 mb-2">
                                {{ $art->category->name ?? 'Kurasi Tempat' }}
                            </div>
                            <h3 class="font-serif-title font-bold text-base text-zinc-950 dark:text-zinc-50 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors line-clamp-2 mb-2">
                                <a href="{{ $art->url() }}" wire:navigate>
                                    {{ $art->title }}
                                </a>
                            </h3>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 line-clamp-2 leading-relaxed mb-4 flex-grow">
                                {{ $art->excerpt }}
                            </p>
                            <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-[11px] text-zinc-500 dark:text-zinc-400 font-medium">
                                <span>{{ $art->published_at ? $art->published_at->translatedFormat('d M Y') : 'Baru' }}</span>
                                <span class="group-hover:translate-x-1 transition-transform text-amber-800 dark:text-amber-400 font-semibold">Baca &rarr;</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Category Filter Tabs -->
        @include('partials.category-tabs', ['categories' => $categories])

        <!-- Main Feed & Editorial Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14">
            <!-- Article Feed Column (8 cols) -->
            <div class="lg:col-span-8">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-zinc-950 dark:text-zinc-50">
                        {{ !empty($selectedCategory) ? 'Kategori: ' . $activeCategoryName : 'Cerita & Ulasan Terbaru' }}
                    </h2>
                    <span class="text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-semibold">Semua Wilayah</span>
                </div>

                @if ($posts->isEmpty())
                    <div class="rounded-xl border border-dashed border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-50 dark:bg-zinc-800 text-zinc-400 mb-3">
                            <i class="fa-solid fa-feather-pointed text-xl"></i>
                        </div>
                        <h3 class="font-serif-title text-lg font-bold text-zinc-900 dark:text-zinc-100">Belum Ada Cerita Terbit</h3>
                        <p class="mt-1 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                            Konten pada kategori ini belum tersedia atau masih dalam draf.
                        </p>
                    </div>
                @else
                    <div class="space-y-10">
                        @foreach ($feedPosts as $post)
                            @include('partials.article-feed', ['post' => $post])
                        @endforeach
                    </div>

                    <!-- Explore More Places Link (styled like Baca Catatan) -->
                    <div class="mt-8 pt-6 border-t border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">Tertarik mencari tempat lain?</span>
                        <a href="{{ route('search') }}" 
                           wire:navigate
                           class="font-semibold text-sm text-zinc-900 dark:text-zinc-200 hover:text-amber-800 dark:hover:text-amber-400 flex items-center gap-2 transition-colors group">
                            <span>Jelajahi banyak tempat</span>
                            <i class="fa-solid fa-arrow-right text-[11px] transition-transform duration-300 group-hover:translate-x-1"></i>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Journal Sidebar (4 cols) -->
            <aside class="lg:col-span-4 space-y-10">
                <!-- Pilihan Redaksi -->
                <div class="bg-white dark:bg-zinc-900/90 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs">
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-6 text-zinc-900 dark:text-zinc-200 pb-3 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                        <span>Pilihan Redaksi</span>
                        <i class="fa-solid fa-bookmark text-amber-800 dark:text-amber-400 text-xs"></i>
                    </h3>
                    @include('partials.editor-picks', ['posts' => $posts->take(4)])
                </div>

                <!-- Ad Slot: Sidebar Beranda -->
                <x-ad-slot slotName="ad_home_sidebar" />

                <!-- Direktori Kota Terkurasi -->
                @if (isset($cities) && $cities->count() > 0)
                <div class="bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-3 text-zinc-900 dark:text-zinc-200">Jelajah Kota</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed mb-4">
                        Pilih kota untuk melihat kurasi kuliner, hotel, dan destinasi pilihan di sekitarmu.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($cities->take(12) as $city)
                            <a href="{{ route('search', ['city' => $city->slug]) }}" 
                               wire:navigate
                               class="px-2.5 py-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-950 dark:hover:border-zinc-400 hover:bg-zinc-900 dark:hover:bg-zinc-100 hover:text-white dark:hover:text-zinc-950 rounded-md text-xs font-medium text-zinc-700 dark:text-zinc-300 transition-colors">
                                {{ $city->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Manifesto Editorial -->
                <div class="border-l-2 border-amber-800 dark:border-amber-500 pl-4 py-1">
                    <h4 class="font-serif-title font-bold text-sm text-zinc-950 dark:text-zinc-100">Tentang Kurasi Kami</h4>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed mt-1">
                        Setiap tempat yang diulas dipilih dengan pertimbangan kualitas, orisinalitas, dan kenyamanan pengunjung.
                    </p>
                </div>
            </aside>
        </div>
    </div>
</div>
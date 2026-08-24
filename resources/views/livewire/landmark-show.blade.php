<div class="min-h-screen bg-[#fafaf9] dark:bg-zinc-950 transition-colors duration-300">
    @php
        $isFirstPage = $posts->currentPage() === 1;
        $featuredPost = $isFirstPage && $posts->count() >= 4 ? $posts->first() : null;
        $sideFeatured = $featuredPost ? $posts->slice(1, 3) : collect();
        $feedPosts = $featuredPost ? $posts->slice(4) : $posts;
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        
        <!-- Breadcrumbs Navigation -->
        <nav class="flex items-center gap-2 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-medium mb-6">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors cursor-pointer">Beranda</a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            @if ($landmark->city)
                <a href="{{ route('search', ['city' => $landmark->city->slug]) }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors cursor-pointer">{{ $landmark->city->name }}</a>
                <span class="text-zinc-300 dark:text-zinc-700">/</span>
            @endif
            @if ($selectedCategory)
                <a href="{{ route('category.home', ['category' => $selectedCategory->slug]) }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors cursor-pointer">{{ $selectedCategory->name }}</a>
                <span class="text-zinc-300 dark:text-zinc-700">/</span>
            @endif
            <span class="text-zinc-900 dark:text-zinc-100 font-semibold">Di Sekitar {{ $landmark->name }}</span>
        </nav>

        <!-- Landmark Header Banner -->
        <header class="mb-10 max-w-4xl">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-200 dark:border-amber-900/50">
                    <i class="fa-solid fa-map-pin text-[11px]"></i>
                    {{ strtoupper(str_replace('_', ' ', $landmark->type ?: 'Landmark')) }}
                </span>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-zinc-200/80 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                    <i class="fa-solid fa-circle-dot text-[9px] text-emerald-500"></i>
                    Radius {{ $radius }} km
                </span>
                @if ($landmark->city)
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                        {{ $landmark->city->name }}
                    </span>
                @endif
            </div>

            <h1 class="font-serif-title text-3xl sm:text-4xl lg:text-5xl font-bold text-zinc-950 dark:text-zinc-50 tracking-tight leading-tight">
                @if ($selectedCategory)
                    {{ $selectedCategory->name }} di Sekitar {{ $landmark->name }}
                @else
                    Rekomendasi di Sekitar {{ $landmark->name }}
                @endif
            </h1>

            <p class="mt-3 text-sm sm:text-base text-zinc-600 dark:text-zinc-400 leading-relaxed max-w-3xl">
                @if ($landmark->description)
                    {{ $landmark->description }}
                @else
                    Daftar kurasi tempat menarik, kuliner, hotel, dan destinasi populer pilihan dalam radius {{ $radius }} km di dekat {{ $landmark->name }}{{ $landmark->city ? ', ' . $landmark->city->name : '' }}.
                @endif
            </p>
        </header>

        <!-- Lead Editorial Spread (Hero Magazine Layout - Page 1) -->
        @if ($featuredPost)
            @php
                $featuredThumb = $featuredPost->primary_image_url;
                $fallbackFeaturedUrls = $featuredPost->fallback_image_urls;
                $featuredDist = isset($featuredPost->distance_km) ? round((float) $featuredPost->distance_km, 2) : null;
            @endphp
            <section class="border-b border-zinc-200 dark:border-zinc-800 pb-12 mb-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                    <!-- Lead Story (7 cols) -->
                    <article class="lg:col-span-7 group">
                        <a href="{{ $featuredPost->url() }}" 
                           wire:navigate class="block space-y-4 cursor-pointer">
                            <div class="aspect-[16/10] overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/60 dark:border-zinc-800 relative">
                                @if ($featuredThumb)
                                    <img src="{{ $featuredThumb }}" 
                                         alt="{{ $featuredPost->title }}" 
                                         data-fallbacks='@json($fallbackFeaturedUrls ?? [])'
                                         onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                         loading="lazy">
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

                                @if ($featuredDist !== null)
                                    <div class="absolute top-3 right-3 px-2.5 py-1 rounded-lg bg-zinc-950/80 backdrop-blur-xs text-white text-xs font-bold flex items-center gap-1.5 shadow-xs pointer-events-none">
                                        <i class="fa-solid fa-location-arrow text-amber-400 text-[10px]"></i>
                                        <span>{{ $featuredDist < 1 ? round($featuredDist * 1000) . ' m' : $featuredDist . ' km' }} dari {{ $landmark->name }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-2.5">
                                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400">
                                    <span>{{ $featuredPost->category->name ?? 'REKOMENDASI' }}</span>
                                    <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                    <span class="text-zinc-500 dark:text-zinc-400 font-medium">{{ max(1, round(str_word_count(strip_tags($featuredPost->content ?? '')) / 200)) }} min baca</span>
                                </div>

                                <h2 class="font-serif-title text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight text-zinc-950 dark:text-zinc-50 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors">
                                    {{ $featuredPost->title }}
                                </h2>

                                <p class="text-zinc-600 dark:text-zinc-400 text-sm sm:text-base leading-relaxed line-clamp-3">
                                    {{ $featuredPost->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($featuredPost->content ?? ''), 180) }}
                                </p>

                                <div class="flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400 pt-1 font-medium">
                                    <span class="text-zinc-800 dark:text-zinc-300 flex items-center gap-1.5">
                                        <i class="fa-solid fa-location-dot text-amber-800 dark:text-amber-400 text-[11px]"></i>
                                        {{ $featuredPost->city->name ?? 'Indonesia' }}
                                    </span>
                                    @if ($featuredDist !== null)
                                        <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                        <span class="text-amber-800 dark:text-amber-400 font-semibold">{{ $featuredDist < 1 ? round($featuredDist * 1000) . ' m' : $featuredDist . ' km' }} terdekat</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </article>

                    <!-- Side Lead Stories (5 cols) -->
                    <div class="lg:col-span-5 lg:border-l lg:border-zinc-200 dark:lg:border-zinc-800 lg:pl-10 space-y-6">
                        <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                            <h2 class="text-xs uppercase tracking-widest font-bold text-zinc-900 dark:text-zinc-200">Sorotan di Sekitar Sini</h2>
                            <span class="text-[11px] text-zinc-400 dark:text-zinc-500 font-medium">Radius {{ $radius }} km</span>
                        </div>

                        <div class="space-y-6 divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($sideFeatured as $sidePost)
                                @php
                                    $sideThumb = $sidePost->primary_image_url;
                                    $fallbackSideUrls = $sidePost->fallback_image_urls;
                                    $sideDist = isset($sidePost->distance_km) ? round((float) $sidePost->distance_km, 2) : null;
                                @endphp
                                <article class="pt-6 first:pt-0 group">
                                    <a href="{{ $sidePost->url() }}" 
                                       wire:navigate class="grid grid-cols-3 gap-4 items-start cursor-pointer">
                                        <div class="col-span-2 space-y-1.5">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] uppercase font-bold tracking-wider text-amber-800 dark:text-amber-400 block">
                                                    {{ $sidePost->category->name ?? 'CERITA' }}
                                                </span>
                                                @if ($sideDist !== null)
                                                    <span class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">
                                                        • {{ $sideDist < 1 ? round($sideDist * 1000) . ' m' : $sideDist . ' km' }}
                                                    </span>
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

        <!-- Category Filter Tabs (Home-style horizontal tabs with cursor pointer) -->
        <div class="border-b border-zinc-200 dark:border-zinc-800 mb-10">
            <div class="flex items-center gap-6 overflow-x-auto pb-3 text-xs font-semibold uppercase tracking-wider no-scrollbar">
                <button type="button" 
                        wire:click="filterCategory(null)" 
                        class="pb-2 cursor-pointer transition-colors whitespace-nowrap {{ empty($categorySlug) ? 'border-b-2 border-zinc-950 dark:border-zinc-100 text-zinc-950 dark:text-zinc-50 font-bold' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                    Semua Tempat
                </button>
                @foreach ($categories as $cat)
                    <button type="button" 
                            wire:click="filterCategory('{{ $cat->slug }}')" 
                            class="pb-2 cursor-pointer transition-colors whitespace-nowrap {{ $categorySlug === $cat->slug ? 'border-b-2 border-zinc-950 dark:border-zinc-100 text-zinc-950 dark:text-zinc-50 font-bold' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Main Feed & Editorial Sidebar Layout (Home-style Grid) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14" id="posts-feed">
            <!-- Article Feed Column (8 cols) -->
            <div class="lg:col-span-8">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-zinc-950 dark:text-zinc-50">
                        {{ $selectedCategory ? $selectedCategory->name . ' di Sekitar ' . $landmark->name : 'Cerita & Tempat di Sekitar ' . $landmark->name }}
                    </h2>
                    <span class="text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-semibold">
                        {{ $posts->total() }} Tempat
                    </span>
                </div>

                @if ($posts->isEmpty() || ($featuredPost && $feedPosts->isEmpty()))
                    <div class="rounded-xl border border-dashed border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-50 dark:bg-zinc-800 text-zinc-400 mb-3">
                            <i class="fa-solid fa-map-location-dot text-xl text-amber-800 dark:text-amber-400"></i>
                        </div>
                        <h3 class="font-serif-title text-lg font-bold text-zinc-900 dark:text-zinc-100">Belum Ada Tempat Terdaftar</h3>
                        <p class="mt-1 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                            Belum ada tempat terdaftar dalam kategori ini dalam radius {{ $radius }} km dari {{ $landmark->name }}.
                        </p>
                        @if ($categorySlug)
                            <button type="button" 
                                    wire:click="filterCategory(null)" 
                                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-950 hover:bg-zinc-800 cursor-pointer transition-colors">
                                <i class="fa-solid fa-arrow-rotate-left text-xs"></i>
                                Tampilkan Semua Kategori
                            </button>
                        @endif
                    </div>
                @else
                    <div class="space-y-10">
                        @foreach ($feedPosts as $post)
                            @php
                                $thumb = $post->primary_image_url;
                                $fallbackThumbUrls = $post->fallback_image_urls;
                                $distance = isset($post->distance_km) ? round((float) $post->distance_km, 2) : null;
                            @endphp
                            <article class="group pb-10 border-b border-zinc-200/80 dark:border-zinc-800/80 last:border-0 last:pb-0">
                                <div class="flex flex-col sm:flex-row gap-6 md:gap-8 items-start">
                                    <!-- Thumbnail -->
                                    <div class="w-full sm:w-5/12 flex-shrink-0">
                                        <a href="{{ $post->url() }}" 
                                           wire:navigate class="block overflow-hidden rounded-xl aspect-[16/10] bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/60 dark:border-zinc-800 relative cursor-pointer">
                                            @if ($thumb)
                                                <img src="{{ $thumb }}" 
                                                     alt="{{ $post->title }}" 
                                                     data-fallbacks='@json($fallbackThumbUrls)'
                                                     onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                                     loading="lazy">
                                                @if ($source = $post->primary_image_source)
                                                    <span class="absolute bottom-2 right-2 px-1.5 py-0.5 rounded bg-black/60 backdrop-blur-xs text-[9px] font-medium text-white/90 tracking-wide pointer-events-none">
                                                        Foto: {{ $source }}
                                                    </span>
                                                @endif
                                            @else
                                                <div class="w-full h-full bg-zinc-100 dark:bg-zinc-800 flex flex-col items-center justify-center text-zinc-400 dark:text-zinc-500 p-4">
                                                    <i class="fa-solid fa-camera text-2xl mb-1.5 text-zinc-300 dark:text-zinc-600"></i>
                                                    <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400 dark:text-zinc-500">DISEKITAR</span>
                                                </div>
                                            @endif

                                            @if ($distance !== null)
                                                <div class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-zinc-950/80 backdrop-blur-xs text-white text-[10px] font-bold flex items-center gap-1 shadow-xs pointer-events-none">
                                                    <i class="fa-solid fa-location-arrow text-amber-400 text-[9px]"></i>
                                                    <span>{{ $distance < 1 ? round($distance * 1000) . ' m' : $distance . ' km' }}</span>
                                                </div>
                                            @endif
                                        </a>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="w-full sm:w-7/12 flex flex-col justify-between space-y-3">
                                        <div>
                                            <div class="flex items-center gap-2 text-[11px] uppercase tracking-wider text-amber-800 dark:text-amber-400 font-bold mb-2">
                                                <span>{{ $post->category->name ?? 'TEMPAT' }}</span>
                                                <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                                <span class="text-zinc-500 dark:text-zinc-400 font-medium">{{ max(1, round(str_word_count(strip_tags($post->content ?? '')) / 200)) }} min baca</span>
                                            </div>
                                            
                                            <h3 class="font-serif-title text-xl sm:text-2xl font-bold leading-snug text-zinc-950 dark:text-zinc-100 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors">
                                                <a href="{{ $post->url() }}" 
                                                   wire:navigate class="cursor-pointer">
                                                    {{ $post->title }}
                                                </a>
                                            </h3>
                                            
                                            <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed line-clamp-2 mt-2">
                                                {{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 130) }}
                                            </p>
                                        </div>
                                        
                                        <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400 pt-3 border-t border-zinc-100 dark:border-zinc-800 font-medium">
                                            <div class="flex items-center gap-2 text-zinc-700 dark:text-zinc-300">
                                                <div class="flex items-center gap-1">
                                                    <i class="fa-solid fa-location-dot text-amber-800 dark:text-amber-400 text-[11px]"></i>
                                                    <span>{{ $post->city->name ?? 'Indonesia' }}</span>
                                                </div>
                                                @if ($post->google_rating)
                                                    <span>•</span>
                                                    <span class="flex items-center gap-1 font-semibold text-zinc-800 dark:text-zinc-200">
                                                        <i class="fa-solid fa-star text-amber-500 text-[10px]"></i>
                                                        {{ number_format((float) $post->google_rating, 1) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <a href="{{ $post->url() }}" 
                                               wire:navigate
                                               class="font-semibold text-zinc-900 dark:text-zinc-200 hover:text-amber-800 dark:hover:text-amber-400 flex items-center gap-1.5 transition-colors cursor-pointer">
                                                Baca Catatan <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <!-- Pagination with smooth scroll -->
                    <div class="mt-10">
                        {{ $posts->links('partials.pagination') }}
                    </div>
                @endif
            </div>

            <!-- Landmark & Editorial Sidebar (4 cols) -->
            <aside class="lg:col-span-4 space-y-10">
                <!-- Info Landmark Card -->
                <div class="bg-white dark:bg-zinc-900/90 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs space-y-4">
                    <h3 class="text-xs uppercase tracking-widest font-bold text-zinc-900 dark:text-zinc-200 pb-3 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                        <span>Tentang Landmark</span>
                        <i class="fa-solid fa-circle-info text-amber-800 dark:text-amber-400 text-xs"></i>
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-map-pin text-amber-800 dark:text-amber-400 mt-0.5"></i>
                            <div>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100 block">{{ $landmark->name }}</span>
                                <span class="text-zinc-500 dark:text-zinc-400">{{ $landmark->city?->name ?? 'Indonesia' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5 text-zinc-600 dark:text-zinc-400">
                            <i class="fa-solid fa-crosshairs text-zinc-400"></i>
                            <span>{{ number_format($landmark->latitude, 4) }}, {{ number_format($landmark->longitude, 4) }}</span>
                        </div>

                        <div class="flex items-center gap-2.5 text-zinc-600 dark:text-zinc-400">
                            <i class="fa-solid fa-compass-drafting text-zinc-400"></i>
                            <span>Cakupan Radius Pencarian: <strong class="text-zinc-900 dark:text-zinc-200">{{ $radius }} km</strong></span>
                        </div>
                    </div>

                    {{-- Hidden as requested: Google Maps button
                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($landmark->name . ' ' . ($landmark->city?->name ?? '')) }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg bg-zinc-900 dark:bg-zinc-100 hover:bg-zinc-800 dark:hover:bg-white text-white dark:text-zinc-950 text-xs font-semibold tracking-wide transition-colors cursor-pointer">
                            <i class="fa-solid fa-map-location-dot"></i>
                            Buka di Google Maps
                        </a>
                    </div>
                    --}}
                </div>

                <!-- Landmark Lain di Kota Ini -->
                @if (isset($otherLandmarks) && $otherLandmarks->isNotEmpty())
                    <div class="bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                        <h3 class="text-xs uppercase tracking-widest font-bold mb-3 text-zinc-900 dark:text-zinc-200">
                            Landmark Lain di {{ $landmark->city?->name ?? 'Wilayah Ini' }}
                        </h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed mb-4">
                            Jelajahi tempat-tempat menarik di sekitar titik transit dan ikon lainnya:
                        </p>
                        <div class="space-y-2">
                            @foreach ($otherLandmarks as $other)
                                <a href="{{ route('landmark.show', ['landmark' => $other->slug]) }}" 
                                   wire:navigate
                                   class="block px-3 py-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-950 dark:hover:border-zinc-400 rounded-lg text-xs font-medium text-zinc-800 dark:text-zinc-200 hover:text-amber-800 dark:hover:text-amber-400 transition-colors cursor-pointer">
                                    <div class="flex items-center justify-between">
                                        <span>{{ $other->name }}</span>
                                        <span class="text-[10px] text-zinc-400 uppercase font-semibold">{{ $other->type ?: 'Transit' }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Manifesto Editorial -->
                <div class="border-l-2 border-amber-800 dark:border-amber-500 pl-4 py-1">
                    <h4 class="font-serif-title font-bold text-sm text-zinc-950 dark:text-zinc-100">Kurasi Jarak Nyata</h4>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed mt-1">
                        Semua tempat dihitung secara presisi menggunakan formula geolokasi (Haversine) dari titik pusat {{ $landmark->name }} untuk memastikan jarak aktual terdekat bagi mobilitas Anda.
                    </p>
                </div>
            </aside>
        </div>

    </div>
</div>

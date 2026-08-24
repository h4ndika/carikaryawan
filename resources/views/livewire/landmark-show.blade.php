<div class="min-h-screen bg-[#fafaf9] dark:bg-zinc-950 py-8 sm:py-12 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumbs Navigation -->
        <nav class="flex items-center gap-2 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-medium mb-6">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">Beranda</a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            @if ($selectedCategory)
                <a href="{{ route('category.home', ['category' => $selectedCategory->slug]) }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">{{ $selectedCategory->name }}</a>
                <span class="text-zinc-300 dark:text-zinc-700">/</span>
            @endif
            <span class="text-zinc-900 dark:text-zinc-100 font-semibold">Di Sekitar {{ $landmark->name }}</span>
        </nav>

        <!-- Landmark Hero Banner -->
        <header class="mb-10 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl p-6 sm:p-8 lg:p-10 shadow-xs relative overflow-hidden">
            <div class="max-w-3xl relative z-10">
                <div class="flex flex-wrap items-center gap-2.5 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-200 dark:border-amber-900/50">
                        <i class="fa-solid fa-map-pin text-[11px]"></i>
                        {{ strtoupper(str_replace('_', ' ', $landmark->type ?: 'Landmark')) }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
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

                <p class="mt-3 text-sm sm:text-base text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    @if ($landmark->description)
                        {{ $landmark->description }}
                    @else
                        Daftar tempat populer dan pilihan terbaik yang berada dalam jarak terdekat (radius {{ $radius }} km) dari {{ $landmark->name }}{{ $landmark->city ? ', ' . $landmark->city->name : '' }}.
                    @endif
                </p>

                <div class="mt-4 flex items-center gap-4 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                    <span class="text-zinc-900 dark:text-zinc-200 font-semibold">
                        {{ $posts->total() }} tempat ditemukan
                    </span>
                    <span>•</span>
                    <span>Koordinat: {{ number_format($landmark->latitude, 4) }}, {{ number_format($landmark->longitude, 4) }}</span>
                </div>
            </div>

            <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-gradient-to-l from-amber-50/40 dark:from-amber-950/10 to-transparent pointer-events-none hidden sm:block"></div>
        </header>

        <!-- Category Filter Tabs -->
        <div class="mb-8 overflow-x-auto pb-2 scrollbar-none">
            <div class="flex items-center gap-2 min-w-max">
                <button type="button" 
                        wire:click="filterCategory(null)" 
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 {{ empty($categorySlug) ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-950 shadow-xs' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700' }}">
                    <i class="fa-solid fa-border-all mr-1.5 text-xs"></i>
                    Semua Kategori
                </button>

                @foreach ($categories as $cat)
                    <button type="button" 
                            wire:click="filterCategory('{{ $cat->slug }}')" 
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 {{ $categorySlug === $cat->slug ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-950 shadow-xs' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700' }}">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Post Grid Section -->
        <div id="landmark-posts-feed" class="scroll-mt-6">
            @if ($posts->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($posts as $post)
                        @php
                            $thumb = $post->primary_image_url;
                            $distance = isset($post->distance_km) ? round((float) $post->distance_km, 2) : null;
                        @endphp
                        <article class="group bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden shadow-xs hover:shadow-md transition-all duration-300 flex flex-col">
                            <!-- Image Container -->
                            <a href="{{ $post->url() }}" wire:navigate class="block aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800 relative">
                                @if ($thumb)
                                    <img src="{{ $thumb }}" 
                                         alt="{{ $post->title }}" 
                                         data-fallbacks='@json($post->fallback_image_urls ?? [])'
                                         onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                         loading="lazy">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-zinc-400 dark:text-zinc-500">
                                        <i class="fa-solid fa-image text-3xl mb-1 text-zinc-300 dark:text-zinc-600"></i>
                                        <span class="text-[10px] font-bold uppercase tracking-wider">DISEKITAR</span>
                                    </div>
                                @endif

                                <!-- Distance Badge on Top-Right -->
                                @if ($distance !== null)
                                    <div class="absolute top-2.5 right-2.5 px-2.5 py-1 rounded-lg bg-zinc-950/80 backdrop-blur-xs text-white text-[11px] font-bold flex items-center gap-1 shadow-xs pointer-events-none">
                                        <i class="fa-solid fa-location-arrow text-amber-400 text-[10px]"></i>
                                        <span>{{ $distance < 1 ? round($distance * 1000) . ' m' : $distance . ' km' }}</span>
                                    </div>
                                @endif

                                <!-- Category Badge on Top-Left -->
                                @if ($post->category)
                                    <div class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-md bg-white/90 dark:bg-zinc-900/90 backdrop-blur-xs text-zinc-900 dark:text-zinc-100 text-[10px] font-bold uppercase tracking-wider pointer-events-none">
                                        {{ $post->category->name }}
                                    </div>
                                @endif
                            </a>

                            <!-- Card Content -->
                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-serif-title text-base sm:text-lg font-bold text-zinc-950 dark:text-zinc-50 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors leading-snug line-clamp-2">
                                        <a href="{{ $post->url() }}" wire:navigate>
                                            {{ $post->title }}
                                        </a>
                                    </h3>

                                    <p class="mt-2 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2 leading-relaxed">
                                        {{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 100) }}
                                    </p>
                                </div>

                                <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                    <span class="flex items-center gap-1 truncate max-w-[180px]">
                                        <i class="fa-solid fa-location-dot text-zinc-400 text-[11px]"></i>
                                        <span class="truncate">{{ $post->city->name ?? 'Indonesia' }}</span>
                                    </span>

                                    @if ($post->google_rating)
                                        <span class="flex items-center gap-1 font-semibold text-zinc-800 dark:text-zinc-200">
                                            <i class="fa-solid fa-star text-amber-500 text-[10px]"></i>
                                            {{ number_format((float) $post->google_rating, 1) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-10" x-data x-init="$wire.on('page-changed', () => { document.getElementById('landmark-posts-feed')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); })">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl p-12 text-center max-w-lg mx-auto">
                    <div class="w-14 h-14 mx-auto rounded-full bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-800 dark:text-amber-400 text-2xl mb-4">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <h3 class="font-serif-title text-xl font-bold text-zinc-950 dark:text-zinc-50 mb-2">
                        Belum Ada Tempat Terdaftar
                    </h3>
                    <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed mb-6">
                        Belum ada tempat dalam kategori ini yang terdata dalam radius {{ $radius }} km dari {{ $landmark->name }}.
                    </p>
                    @if ($categorySlug)
                        <button type="button" 
                                wire:click="filterCategory(null)" 
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-950 hover:bg-zinc-800 transition-colors">
                            <i class="fa-solid fa-arrow-rotate-left text-xs"></i>
                            Tampilkan Semua Kategori
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

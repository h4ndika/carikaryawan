<div class="min-h-screen bg-[#fafaf9] dark:bg-zinc-950 py-8 sm:py-12 transition-colors duration-300">
    @php
        $isFirstPageWithoutCity = $posts->currentPage() === 1 && empty($citySlug);
        $featuredPost = $isFirstPageWithoutCity && $posts->count() >= 4 ? $posts->first() : null;
        $sideFeatured = $featuredPost ? $posts->slice(1, 3) : collect();
        $feedPosts = $featuredPost ? $posts->slice(4) : $posts;
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumbs Navigation -->
        <nav class="flex items-center gap-2 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-medium mb-6">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">Beranda</a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <a href="{{ route('category.home', ['category' => $category->slug]) }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">{{ $category->name }}</a>
            @if ($citySlug && $city)
                <span class="text-zinc-300 dark:text-zinc-700">/</span>
                <span class="text-zinc-900 dark:text-zinc-100 font-semibold">{{ $city->name }}</span>
            @endif
        </nav>

        <!-- Category Header (shown when city filtered or fewer posts) -->
        @if (!$featuredPost)
            <header class="mb-10 max-w-3xl">
                <span class="text-xs uppercase tracking-widest font-bold text-amber-800 dark:text-amber-400 block mb-1">
                    Kurasi Direktori
                </span>
                <h1 class="font-serif-title text-3xl sm:text-4xl lg:text-5xl font-bold text-zinc-950 dark:text-zinc-50 tracking-tight">
                    @if ($citySlug && $city)
                        {{ $category->name }} di {{ $city->name }}
                    @else
                        {{ $category->name }}
                    @endif
                </h1>
                <p class="mt-2 text-sm sm:text-base text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    @if ($citySlug && $city)
                        Rekomendasi dan ulasan {{ strtolower($category->name) }} pilihan di wilayah {{ $city->name }}.
                    @else
                        Rekomendasi dan ulasan {{ strtolower($category->name) }} pilihan di seluruh wilayah.
                    @endif
                    @if (! $posts->isEmpty())
                        <span class="font-semibold text-zinc-900 dark:text-zinc-200">({{ $posts->total() }} catatan terbit)</span>
                    @endif
                </p>
            </header>
        @endif

        <!-- Lead Editorial Spread (Hero Magazine Layout - Page 1) -->
        @if ($featuredPost)
            @php
                $featuredThumb = $featuredPost->primary_image_url;
                $fallbackFeaturedUrls = $featuredPost->fallback_image_urls;
            @endphp
            <section class="border-b border-zinc-200 dark:border-zinc-800 pb-12 mb-10">
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
                            </div>

                            <div class="space-y-2.5">
                                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400">
                                    <span>{{ $featuredPost->category->name ?? $category->name }}</span>
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
                                    <span class="text-zinc-300 dark:text-zinc-700">•</span>
                                    <span>{{ $featuredPost->published_at ? \Illuminate\Support\Carbon::parse($featuredPost->published_at)->translatedFormat('d F Y') : date('d F Y') }}</span>
                                </div>
                            </div>
                        </a>
                    </article>

                    <!-- Side Lead Stories (5 cols) -->
                    <div class="lg:col-span-5 lg:border-l lg:border-zinc-200 dark:lg:border-zinc-800 lg:pl-10 space-y-6">
                        <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                            <h3 class="text-xs uppercase tracking-widest font-bold text-zinc-900 dark:text-zinc-200">Catatan Kurator</h3>
                            <span class="text-[11px] text-zinc-400 dark:text-zinc-500 font-medium">Pilihan {{ $category->name }}</span>
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
                                            <span class="text-[10px] uppercase font-bold tracking-wider text-amber-800 dark:text-amber-400 block">
                                                {{ $sidePost->category->name ?? $category->name }}
                                            </span>
                                            <h4 class="font-serif-title font-bold text-base leading-snug text-zinc-950 dark:text-zinc-100 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors line-clamp-2">
                                                {{ $sidePost->title }}
                                            </h4>
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
                                <p class="text-xs text-zinc-400 dark:text-zinc-500 py-4">Belum ada catatan kurator.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <!-- City Quick Filter Bar -->
        @if (isset($cities) && $cities->count() > 0)
            <div class="mb-10 pb-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 flex-shrink-0 mr-1">
                    Wilayah:
                </span>
                <a href="{{ route('category.home', ['category' => $category->slug]) }}" 
                   wire:navigate
                   class="flex-shrink-0 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors {{ empty($citySlug) ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:border-zinc-900 dark:hover:border-zinc-400' }}">
                    Semua Kota
                </a>
                @foreach ($cities as $cityItem)
                    <a href="{{ route('posts.list', ['category' => $category->slug, 'city' => $cityItem->slug]) }}" 
                       wire:navigate
                       class="flex-shrink-0 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $citySlug === $cityItem->slug ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:border-zinc-900 dark:hover:border-zinc-400' }}">
                        {{ $cityItem->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Main Feed & Editorial Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14">
            <!-- Article Feed Column (8 cols) -->
            <div id="posts-feed" class="lg:col-span-8 scroll-mt-24">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-zinc-950 dark:text-zinc-50">
                        @if ($citySlug && $city)
                            Daftar {{ $category->name }} di {{ $city->name }}
                        @else
                            Semua Catatan {{ $category->name }}
                        @endif
                    </h2>
                    <span class="text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-semibold">
                        {{ $posts->total() }} Catatan
                    </span>
                </div>

                @if ($feedPosts->isEmpty())
                    <div class="rounded-xl border border-dashed border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-50 dark:bg-zinc-800 text-zinc-400 mb-3">
                            <i class="fa-solid fa-feather-pointed text-xl"></i>
                        </div>
                        <h3 class="font-serif-title text-lg font-bold text-zinc-900 dark:text-zinc-100">Belum Ada Cerita Terbit</h3>
                        <p class="mt-1 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                            Konten pada kategori atau kota ini belum tersedia atau masih dalam draf.
                        </p>
                        <a href="{{ route('category.home', ['category' => $category->slug]) }}" wire:navigate class="mt-6 inline-flex rounded-xl bg-zinc-900 dark:bg-zinc-100 px-5 py-2.5 text-xs font-semibold text-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-white transition-colors">
                            Lihat Semua {{ $category->name }}
                        </a>
                    </div>
                @else
                    <div class="space-y-10">
                        @foreach ($feedPosts as $post)
                            @include('partials.article-feed', ['post' => $post])
                        @endforeach
                    </div>

                    <!-- Custom Editorial Pagination Links -->
                    <div class="mt-10 pt-6 border-t border-zinc-200 dark:border-zinc-800">
                        {{ $posts->links('partials.pagination') }}
                    </div>
                @endif
            </div>

            <!-- Journal Sidebar (4 cols) -->
            <aside class="lg:col-span-4 space-y-10">
                <!-- Pilihan Redaksi -->
                @if (isset($editorPicks) && $editorPicks->count() > 0)
                <div class="bg-white dark:bg-zinc-900/90 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs">
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-6 text-zinc-900 dark:text-zinc-200 pb-3 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                        <span>Pilihan Redaksi</span>
                        <i class="fa-solid fa-bookmark text-amber-800 dark:text-amber-400 text-xs"></i>
                    </h3>
                    @include('partials.editor-picks', ['posts' => $editorPicks])
                </div>
                @endif

                <!-- Ad Slot: Sidebar Kategori -->
                <x-ad-slot slotName="ad_category_sidebar" />

                <!-- Direktori Kota Terkurasi -->
                @if (isset($cities) && $cities->count() > 0)
                <div class="bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-3 text-zinc-900 dark:text-zinc-200">Jelajah Kota</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed mb-4">
                        Pilih kota untuk melihat kurasi {{ strtolower($category->name) }} pilihan di sekitarmu.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($cities->take(16) as $c)
                            <a href="{{ route('posts.list', ['category' => $category->slug, 'city' => $c->slug]) }}" 
                               wire:navigate
                               class="px-2.5 py-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-950 dark:hover:border-zinc-400 hover:bg-zinc-900 dark:hover:bg-zinc-100 hover:text-white dark:hover:text-zinc-950 rounded-md text-xs font-medium text-zinc-700 dark:text-zinc-300 transition-colors {{ $citySlug === $c->slug ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 border-zinc-900 dark:border-zinc-100' : '' }}">
                                {{ $c->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Manifesto Editorial -->
                <div class="border-l-2 border-amber-800 dark:border-amber-500 pl-4 py-1">
                    <h4 class="font-serif-title font-bold text-sm text-zinc-950 dark:text-zinc-100">Kurasi Terverifikasi</h4>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed mt-1">
                        Setiap tempat kategori {{ strtolower($category->name) }} diulas secara mendalam untuk memastikan ulasan yang autentik dan bermanfaat bagi pengunjung.
                    </p>
                </div>
            </aside>
        </div>

    </div>
</div>
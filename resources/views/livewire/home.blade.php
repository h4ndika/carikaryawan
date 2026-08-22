<div class="min-h-screen">
    @php
        $featuredPost = $posts->first();
        $sideFeatured = $posts->slice(1, 3);
        $feedPosts = $posts->slice(4);
        // Fallback if less than 4 posts
        if ($feedPosts->isEmpty()) {
            $feedPosts = $posts->slice(1);
        }
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <!-- Lead Editorial Spread (Hero Magazine Layout) -->
        @if ($featuredPost)
        @php
            $featuredThumb = $featuredPost->primary_image_url;
            $fallbackFeaturedUrls = $featuredPost->fallback_image_urls;
        @endphp
        <section class="border-b border-zinc-200 pb-12 mb-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                <!-- Lead Story (7 cols) -->
                <article class="lg:col-span-7 group">
                    <a href="{{ route('posts.show', ['category' => $featuredPost->category->slug, 'city' => $featuredPost->city->slug, 'slug' => $featuredPost->slug]) }}" 
                       wire:navigate class="block space-y-4">
                        <div class="aspect-[16/10] overflow-hidden rounded-xl bg-zinc-100 border border-zinc-200/60 relative">
                            @if ($featuredThumb)
                                <img src="{{ $featuredThumb }}" 
                                     alt="{{ $featuredPost->title }}" 
                                     data-fallbacks='@json($fallbackFeaturedUrls ?? [])'
                                     onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                     loading="lazy">
                            @else
                                <div class="w-full h-full bg-zinc-100 flex flex-col items-center justify-center text-zinc-400 p-8">
                                    <i class="fa-solid fa-camera text-4xl text-zinc-300 mb-2"></i>
                                    <span class="text-xs uppercase font-bold tracking-widest text-zinc-400">DISEKITAR KURASI</span>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-2.5">
                            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-800">
                                <span>{{ $featuredPost->category->name ?? 'REKOMENDASI' }}</span>
                                <span class="text-zinc-300">•</span>
                                <span class="text-zinc-500 font-medium">{{ max(1, round(str_word_count(strip_tags($featuredPost->content ?? '')) / 200)) }} min baca</span>
                            </div>

                            <h1 class="font-serif-title text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight text-zinc-950 group-hover:text-amber-800 transition-colors">
                                {{ $featuredPost->title }}
                            </h1>

                            <p class="text-zinc-600 text-sm sm:text-base leading-relaxed line-clamp-3">
                                {{ $featuredPost->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($featuredPost->content ?? ''), 180) }}
                            </p>

                            <div class="flex items-center gap-4 text-xs text-zinc-500 pt-1 font-medium">
                                <span class="text-zinc-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-amber-800 text-[11px]"></i>
                                    {{ $featuredPost->city->name ?? 'Indonesia' }}
                                </span>
                                <span class="text-zinc-300">•</span>
                                <span>{{ $featuredPost->published_at ? \Illuminate\Support\Carbon::parse($featuredPost->published_at)->translatedFormat('d F Y') : date('d F Y') }}</span>
                            </div>
                        </div>
                    </a>
                </article>

                <!-- Side Lead Stories (5 cols) -->
                <div class="lg:col-span-5 lg:border-l lg:border-zinc-200 lg:pl-10 space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                        <h2 class="text-xs uppercase tracking-widest font-bold text-zinc-900">Catatan Kurator</h2>
                        <span class="text-[11px] text-zinc-400 font-medium">Sorotan Pekan Ini</span>
                    </div>

                    <div class="space-y-6 divide-y divide-zinc-100">
                        @forelse ($sideFeatured as $sidePost)
                            @php
                                $sideThumb = $sidePost->primary_image_url;
                                $fallbackSideUrls = $sidePost->fallback_image_urls;
                            @endphp
                            <article class="pt-6 first:pt-0 group">
                                <a href="{{ route('posts.show', ['category' => $sidePost->category->slug, 'city' => $sidePost->city->slug, 'slug' => $sidePost->slug]) }}" 
                                   wire:navigate class="grid grid-cols-3 gap-4 items-start">
                                    <div class="col-span-2 space-y-1.5">
                                        <span class="text-[10px] uppercase font-bold tracking-wider text-amber-800 block">
                                            {{ $sidePost->category->name ?? 'CERITA' }}
                                        </span>
                                        <h3 class="font-serif-title font-bold text-base leading-snug text-zinc-950 group-hover:text-amber-800 transition-colors line-clamp-2">
                                            {{ $sidePost->title }}
                                        </h3>
                                        <div class="text-[11px] text-zinc-500 flex items-center gap-2">
                                            <span>{{ $sidePost->city->name ?? 'Indonesia' }}</span>
                                            <span>•</span>
                                            <span>{{ max(1, round(str_word_count(strip_tags($sidePost->content ?? '')) / 200)) }} min</span>
                                        </div>
                                    </div>
                                    <div class="col-span-1 aspect-square rounded-lg overflow-hidden bg-zinc-100 border border-zinc-200/60 flex items-center justify-center">
                                        @if ($sideThumb)
                                            <img src="{{ $sideThumb }}" 
                                                 alt="{{ $sidePost->title }}" 
                                                 data-fallbacks='@json($fallbackSideUrls)'
                                                 onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                 loading="lazy">
                                        @else
                                            <div class="w-full h-full bg-zinc-100 flex items-center justify-center text-zinc-400">
                                                <i class="fa-solid fa-camera text-base"></i>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            </article>
                        @empty
                            <p class="text-xs text-zinc-400 py-4">Belum ada catatan sampingan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- Category Filter Tabs -->
        @include('partials.category-tabs', ['categories' => $categories])

        <!-- Main Feed & Editorial Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14">
            <!-- Article Feed Column (8 cols) -->
            <div class="lg:col-span-8">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-zinc-200">
                    <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-zinc-950">
                        {{ !empty($selectedCategory) ? 'Kategori: ' . ucfirst($selectedCategory) : 'Cerita & Ulasan Terbaru' }}
                    </h2>
                    <span class="text-xs uppercase tracking-wider text-zinc-500 font-semibold">Semua Wilayah</span>
                </div>

                @if ($posts->isEmpty())
                    <div class="rounded-xl border border-dashed border-zinc-200 bg-white p-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-50 text-zinc-400 mb-3">
                            <i class="fa-solid fa-feather-pointed text-xl"></i>
                        </div>
                        <h3 class="font-serif-title text-lg font-bold text-zinc-900">Belum Ada Cerita Terbit</h3>
                        <p class="mt-1 text-xs sm:text-sm text-zinc-500 max-w-sm mx-auto">
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
                    <div class="mt-8 pt-6 border-t border-zinc-200/80 flex items-center justify-between">
                        <span class="text-xs text-zinc-500 font-medium">Tertarik mencari tempat lain?</span>
                        <a href="{{ route('search') }}" 
                           wire:navigate
                           class="font-semibold text-sm text-zinc-900 hover:text-amber-800 flex items-center gap-2 transition-colors group">
                            <span>Jelajahi banyak tempat</span>
                            <i class="fa-solid fa-arrow-right text-[11px] transition-transform duration-300 group-hover:translate-x-1"></i>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Journal Sidebar (4 cols) -->
            <aside class="lg:col-span-4 space-y-10">
                <!-- Pilihan Redaksi -->
                <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-6 text-zinc-900 pb-3 border-b border-zinc-100 flex items-center justify-between">
                        <span>Pilihan Redaksi</span>
                        <i class="fa-solid fa-bookmark text-amber-800 text-xs"></i>
                    </h3>
                    @include('partials.editor-picks', ['posts' => $posts->take(4)])
                </div>

                <!-- Direktori Kota Terkurasi -->
                @if (isset($cities) && $cities->count() > 0)
                <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-6">
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-3 text-zinc-900">Jelajah Kota</h3>
                    <p class="text-xs text-zinc-500 leading-relaxed mb-4">
                        Pilih kota untuk melihat kurasi kuliner, hotel, dan destinasi pilihan di sekitarmu.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($cities->take(12) as $city)
                            <a href="{{ route('search', ['city' => $city->slug]) }}" 
                               wire:navigate
                               class="px-2.5 py-1 bg-white border border-zinc-200 hover:border-zinc-950 hover:bg-zinc-900 hover:text-white rounded-md text-xs font-medium text-zinc-700 transition-colors">
                                {{ $city->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Manifesto Editorial -->
                <div class="border-l-2 border-amber-800 pl-4 py-1">
                    <h4 class="font-serif-title font-bold text-sm text-zinc-950">Tentang Kurasi Kami</h4>
                    <p class="text-xs text-zinc-600 leading-relaxed mt-1">
                        Setiap tempat yang diulas dipilih dengan pertimbangan kualitas, orisinalitas, dan kenyamanan pengunjung.
                    </p>
                </div>
            </aside>
        </div>
    </div>
</div>
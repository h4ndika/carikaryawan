<div class="min-h-screen">
    @php
        $featuredPost = $posts->first();
        $feedPosts = $posts->slice(1);
        $editorPicks = $posts->take(3);
    @endphp

    <!-- Hero Editorial Spotlight -->
    @if ($featuredPost)
    <section class="bg-[#2c1810] text-[#faf7f2] overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20 grid grid-cols-1 lg:grid-cols-12 gap-8 md:gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md px-3.5 py-1.5 rounded-full text-xs font-semibold tracking-wider text-[#faf7f2] border border-white/15">
                    <span class="w-2 h-2 bg-[#8d6e5e] rounded-full animate-pulse"></span>
                    SOROTAN UTAMA • {{ strtoupper($featuredPost->category->name ?? 'REKOMENDASI') }}
                </div>

                <h1 class="text-3xl sm:text-4xl md:text-5xl heading font-bold leading-tight tracking-tight text-[#faf7f2]">
                    <a href="{{ route('posts.show', ['category' => $featuredPost->category->slug, 'city' => $featuredPost->city->slug, 'slug' => $featuredPost->slug]) }}" 
                       wire:navigate class="hover:text-[#e8d9c0] transition-colors">
                        {{ $featuredPost->title }}
                    </a>
                </h1>

                <p class="text-sm sm:text-base text-[#faf7f2]/80 leading-relaxed max-w-2xl line-clamp-3">
                    {{ $featuredPost->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($featuredPost->content ?? ''), 180) }}
                </p>

                <div class="flex flex-wrap items-center gap-4 text-xs text-[#faf7f2]/70">
                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot text-[#8d6e5e]"></i>
                        <span class="font-medium text-[#faf7f2]">{{ $featuredPost->city->name ?? 'Indonesia' }}</span>
                    </div>
                    <span>•</span>
                    <div>
                        {{ max(1, round(str_word_count(strip_tags($featuredPost->content ?? '')) / 200)) }} min baca
                    </div>
                    <span>•</span>
                    <div>
                        {{ $featuredPost->published_at ? $featuredPost->published_at->translatedFormat('d M Y') : date('d M Y') }}
                    </div>
                </div>

                <div class="pt-2">
                    <a href="{{ route('posts.show', ['category' => $featuredPost->category->slug, 'city' => $featuredPost->city->slug, 'slug' => $featuredPost->slug]) }}" 
                       wire:navigate
                       class="inline-flex items-center gap-2.5 px-6 py-3 bg-[#8d6e5e] text-white font-medium text-sm hover:bg-[#a07e6c] transition-all rounded-lg shadow">
                        Baca Selengkapnya <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>

            <div class="lg:col-span-5">
                <a href="{{ route('posts.show', ['category' => $featuredPost->category->slug, 'city' => $featuredPost->city->slug, 'slug' => $featuredPost->slug]) }}" 
                   wire:navigate class="block group overflow-hidden rounded-2xl aspect-[4/3] bg-black/20 shadow-xl border border-white/10">
                    @if ($featuredPost->featured_image_url)
                        <img src="{{ $featuredPost->featured_image_url }}" 
                             alt="{{ $featuredPost->title }}" 
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    @else
                        <div class="w-full h-full bg-[#3c2419] flex items-center justify-center text-white/30">
                            <i class="fa-solid fa-image text-5xl"></i>
                        </div>
                    @endif
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- Container Feed & Sidebar -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Category Filter Tabs -->
        @include('partials.category-tabs', ['categories' => $categories])

        <!-- Main Feed & Editorial Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <!-- Article Feed Column -->
            <div class="lg:col-span-8">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-[#e8d9c0]">
                    <h2 class="text-2xl sm:text-3xl heading font-bold tracking-tight text-[#2c1810]">Cerita Terbaru</h2>
                    <span class="text-xs uppercase tracking-wider text-[#8d6e5e] font-semibold">Terkurasi</span>
                </div>

                @if ($posts->isEmpty())
                    <div class="rounded-2xl border-2 border-dashed border-[#e8d9c0] bg-white px-8 py-16 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#faf7f2] text-[#8d6e5e] mb-4">
                            <i class="fa-solid fa-feather-pointed text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold heading text-[#2c1810]">Belum Ada Cerita Terbit</h3>
                        <p class="mt-2 text-xs sm:text-sm text-[#2c1810]/70 max-w-sm mx-auto">
                            Konten sedang disiapkan. Masuk ke panel admin untuk mempublikasikan artikel pertama.
                        </p>
                    </div>
                @else
                    <div class="space-y-8">
                        @foreach ($feedPosts->isNotEmpty() ? $feedPosts : $posts as $post)
                            @include('partials.article-feed', ['post' => $post])
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if (method_exists($posts, 'links'))
                        <div class="mt-12 pt-6 border-t border-[#e8d9c0]">
                            {{ $posts->links() }}
                        </div>
                    @endif
                @endif
            </div>

            <!-- Sidebar: Editor's Picks & City Directory -->
            <aside class="lg:col-span-4 space-y-8">
                <!-- Curated Spotlight -->
                <div class="bg-white border border-[#e8d9c0] p-6 rounded-2xl shadow-sm">
                    <h3 class="uppercase text-xs tracking-wider font-bold mb-6 text-[#8d6e5e] flex items-center gap-2">
                        <i class="fa-solid fa-bookmark text-xs"></i>
                        Pilihan Redaksi
                    </h3>
                    @include('partials.editor-picks', ['posts' => $editorPicks])
                </div>

                <!-- Curated City Highlights -->
                <div class="bg-[#2c1810] text-[#faf7f2] p-6 rounded-2xl shadow-sm">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#8d6e5e]">Jelajah Wilayah</span>
                    <h4 class="text-lg heading font-bold mt-1 mb-2 text-[#faf7f2]">Temukan Cerita Per Kota</h4>
                    <p class="text-xs text-[#faf7f2]/70 leading-relaxed mb-5">
                        Dari sudut kuliner tersembunyi hingga tempat staycation terbaik di berbagai kota favorit.
                    </p>
                    <a href="{{ route('search') }}" wire:navigate
                       class="inline-flex items-center gap-1.5 text-xs font-bold text-[#e8d9c0] hover:text-white transition-colors">
                        Buka Direktori Kota <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </aside>
        </div>
    </div>
</div>
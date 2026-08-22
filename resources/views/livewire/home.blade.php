<div class="min-h-screen">
    <!-- Hero Editorial Spotlight -->
    <header class="relative h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-[#2c1810] to-[#1a0f08]">
        <div class="absolute inset-0 opacity-40">
            <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=2000&h=1200&fit=crop" 
                 alt="Hero background" 
                 class="w-full h-full object-cover">
        </div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/60 to-black/90"></div>
        
        <div class="relative z-10 max-w-4xl px-6 text-center text-white">
            <div class="inline-flex items-center gap-x-2 bg-white/10 backdrop-blur-md px-4 py-1.5 rounded-full text-xs tracking-widest mb-6 border border-white/20">
                <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                CERITA UNGGULAN
            </div>
            <h1 class="text-6xl md:text-7xl heading font-light leading-none mb-6 tracking-tighter">
                Revolusi diam di
                <span class="text-[#f5d0a8]">desa-desa pegunungan Jepang</span>
            </h1>
            <p class="text-lg md:text-xl text-white/80 max-w-2xl mx-auto leading-relaxed">
                Generasi baru pengrajin onsen dan pembuat sake secara diam-diam menulis ulang aturan pedesaan Jepang.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-10">
                <a href="{{ route('search') }}" wire:navigate
                   class="px-10 py-4 bg-white text-[#2c1810] font-medium text-base hover:bg-[#f5d0a8] transition-colors">
                    Baca cerita lengkap
                </a>
                <a href="#latest" 
                   class="px-10 py-4 border border-white/70 text-white font-medium text-base hover:bg-white/10 transition-colors">
                    Jelajahi lebih lanjut
                </a>
            </div>
        </div>

        <!-- Scroll indicator -->
        <div class="absolute bottom-12 left-1/2 -translate-x-1/2 flex flex-col items-center gap-y-2 text-white/60 text-xs tracking-widest">
            <span>Scroll untuk lanjut</span>
            <i class="fa-solid fa-chevron-down animate-bounce"></i>
        </div>
    </header>

    <!-- Category Tabs -->
    @include('partials.category-tabs', ['categories' => $categories])

    <!-- Main content area -->
    <div class="grid grid-cols-12 gap-8 px-6 sm:px-8 py-16 max-w-screen-2xl mx-auto" id="latest">
        <!-- Primary Feed -->
        <main class="col-span-12 lg:col-span-8">
            <div class="flex items-center justify-between mb-12">
                <h2 class="text-4xl md:text-5xl heading font-light tracking-tighter">Cerita Terbaru</h2>
                <a href="{{ route('search') }}" wire:navigate class="text-sm font-medium flex items-center gap-x-2 text-[#8d6e5e] hover:gap-x-3 transition-all hidden sm:flex">
                    Lihat semua <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
            
            <div class="space-y-20">
                @if ($posts->isEmpty())
                    <div class="rounded-2xl border-2 border-dashed border-[#e8d9c0] bg-white px-8 py-16 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#f8f1e3] text-[#8d6e5e] mb-4">
                            <i class="fa-solid fa-feather text-2xl"></i>
                        </div>
                        <p class="text-lg font-light heading text-[#2c1810]">Belum ada cerita</p>
                        <p class="mt-2 text-sm text-[#2c1810]/70">Publish konten dari panel admin untuk mulai tampil di sini.</p>
                    </div>
                @else
                    @foreach ($posts as $index => $post)
                        @include('partials.article-feed', ['post' => $post, 'index' => $loop->iteration])
                    @endforeach
                @endif
            </div>
        </main>

        <!-- Sidebar: Editor Picks -->
        <aside class="col-span-12 lg:col-span-4">
            <div class="bg-white border border-[#e8d9c0] p-8 sticky top-24">
                <h3 class="uppercase text-xs tracking-[1px] font-medium mb-8 text-[#8d6e5e]">Pilihan Editor</h3>
                @include('partials.editor-picks', ['posts' => $posts->take(3)])
            </div>
        </aside>
    </div>
</div>
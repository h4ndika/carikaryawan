<div class="min-h-screen bg-[#fafaf9] dark:bg-zinc-950 py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <header class="mb-10 max-w-3xl">
            <span class="text-xs uppercase tracking-widest font-bold text-amber-800 dark:text-amber-400 block mb-1">
                Eksplorasi & Direktori
            </span>
            <h1 class="font-serif-title text-3xl sm:text-4xl lg:text-5xl font-bold text-zinc-950 dark:text-zinc-50 tracking-tight">
                Cari Tempat & Cerita di Sekitarmu
            </h1>
            <p class="mt-2 text-sm sm:text-base text-zinc-600 dark:text-zinc-400 leading-relaxed">
                Temukan ulasan kuliner autentik, rekomendasi penginapan, dan destinasi terkurasi dengan filter lokasi serta kategori.
            </p>
        </header>

        <!-- Search & Filter Controls -->
        <section class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 sm:p-6 shadow-sm mb-10">
            <div class="grid gap-4 sm:grid-cols-12 items-end">
                
                <!-- Keyword Input (6 cols) -->
                <div class="sm:col-span-12 lg:col-span-6">
                    <label for="q" class="block text-xs uppercase tracking-wider font-bold text-zinc-700 dark:text-zinc-300 mb-1.5">
                        Kata Kunci Pencarian
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400 dark:text-zinc-500 text-xs"></i>
                        <input
                            id="q"
                            type="search"
                            wire:model.live.debounce.400ms="q"
                            placeholder="Ketik nama tempat, makanan, atau topik..."
                            class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/80 py-2.5 pl-9 pr-4 text-xs sm:text-sm font-medium text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 outline-none transition focus:border-zinc-900 dark:focus:border-zinc-400 focus:bg-white dark:focus:bg-zinc-800 focus:ring-1 focus:ring-zinc-900 dark:focus:ring-zinc-400"
                        >
                    </div>
                </div>

                <!-- Category Filter (3 cols) -->
                <div class="sm:col-span-6 lg:col-span-3">
                    <label for="category" class="block text-xs uppercase tracking-wider font-bold text-zinc-700 dark:text-zinc-300 mb-1.5">
                        Kategori
                    </label>
                    @include('partials.searchable-select', [
                        'model' => 'category',
                        'options' => $categories,
                        'placeholder' => 'Pilih kategori',
                        'emptyLabel' => 'Semua Kategori',
                        'id' => 'category',
                    ])
                </div>

                <!-- City Filter (3 cols) -->
                <div class="sm:col-span-6 lg:col-span-3">
                    <label for="city" class="block text-xs uppercase tracking-wider font-bold text-zinc-700 dark:text-zinc-300 mb-1.5">
                        Kota
                    </label>
                    @include('partials.searchable-select', [
                        'model' => 'city',
                        'options' => $cities,
                        'placeholder' => 'Pilih kota',
                        'emptyLabel' => 'Semua Kota',
                        'id' => 'city',
                    ])
                </div>

            </div>
        </section>

        <!-- Ad Slot: Bawah Filter Pencarian -->
        <x-ad-slot slotName="ad_search_top" />

        <!-- Results Stream -->
        @if ($posts->isEmpty())
            <div class="rounded-2xl border border-dashed border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-14 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-50 dark:bg-zinc-800 text-zinc-400 mb-3">
                    <i class="fa-solid fa-magnifying-glass text-xl"></i>
                </div>
                <h3 class="font-serif-title text-lg font-bold text-zinc-900 dark:text-zinc-100">Tidak Ada Hasil Ditemukan</h3>
                <p class="mt-1 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                    Coba gunakan kata kunci yang lebih umum atau atur ulang pilihan kategori dan kota.
                </p>
            </div>
        @else
            <!-- Result Meta Bar -->
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-zinc-200 dark:border-zinc-800">
                <p class="text-xs uppercase tracking-wider font-semibold text-zinc-500 dark:text-zinc-400">
                    Menampilkan <span class="text-zinc-950 dark:text-zinc-100 font-bold">{{ $posts->total() }}</span> cerita terkurasi
                </p>
                @if ($q || $category || $city)
                    <button wire:click="$set('q', ''); $set('category', ''); $set('city', '')" 
                            type="button"
                            class="text-xs text-amber-800 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300 font-semibold flex items-center gap-1 transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-[11px]"></i> Reset Filter
                    </button>
                @endif
            </div>

            <!-- Posts Feed (Editorial layout matching Home) -->
            <div id="posts-feed" class="scroll-mt-24 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 sm:p-8 shadow-xs">
                <div class="space-y-10">
                    @foreach ($posts as $post)
                        @include('partials.article-feed', ['post' => $post])
                    @endforeach
                </div>
            </div>

            <!-- Custom Editorial Pagination Links -->
            <div class="mt-8">
                {{ $posts->links('partials.pagination') }}
            </div>
        @endif

    </div>
</div>
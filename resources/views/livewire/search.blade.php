<div class="min-h-screen bg-[#fafaf9] py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <header class="mb-10 max-w-3xl">
            <span class="text-xs uppercase tracking-widest font-bold text-amber-800 block mb-1">
                Eksplorasi & Direktori
            </span>
            <h1 class="font-serif-title text-3xl sm:text-4xl lg:text-5xl font-bold text-zinc-950 tracking-tight">
                Cari Tempat & Cerita di Sekitarmu
            </h1>
            <p class="mt-2 text-sm sm:text-base text-zinc-600 leading-relaxed">
                Temukan ulasan kuliner autentik, rekomendasi penginapan, dan destinasi terkurasi dengan filter lokasi serta kategori.
            </p>
        </header>

        <!-- Search & Filter Controls -->
        <section class="bg-white border border-zinc-200 rounded-2xl p-5 sm:p-6 shadow-sm mb-10">
            <div class="grid gap-4 sm:grid-cols-12 items-end">
                
                <!-- Keyword Input (6 cols) -->
                <div class="sm:col-span-12 lg:col-span-6">
                    <label for="q" class="block text-xs uppercase tracking-wider font-bold text-zinc-700 mb-1.5">
                        Kata Kunci Pencarian
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400 text-xs"></i>
                        <input
                            id="q"
                            type="search"
                            wire:model.live.debounce.400ms="q"
                            placeholder="Ketik nama tempat, makanan, atau topik..."
                            class="w-full rounded-lg border border-zinc-200 bg-zinc-50/50 py-2.5 pl-9 pr-4 text-xs sm:text-sm font-medium text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white focus:ring-1 focus:ring-zinc-900"
                        >
                    </div>
                </div>

                <!-- Category Filter (3 cols) -->
                <div class="sm:col-span-6 lg:col-span-3">
                    <label for="category" class="block text-xs uppercase tracking-wider font-bold text-zinc-700 mb-1.5">
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
                    <label for="city" class="block text-xs uppercase tracking-wider font-bold text-zinc-700 mb-1.5">
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

        <!-- Results Stream -->
        @if ($posts->isEmpty())
            <div class="rounded-2xl border border-dashed border-zinc-200 bg-white p-14 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-50 text-zinc-400 mb-3">
                    <i class="fa-solid fa-magnifying-glass text-xl"></i>
                </div>
                <h3 class="font-serif-title text-lg font-bold text-zinc-900">Tidak Ada Hasil Ditemukan</h3>
                <p class="mt-1 text-xs sm:text-sm text-zinc-500 max-w-sm mx-auto">
                    Coba gunakan kata kunci yang lebih umum atau atur ulang pilihan kategori dan kota.
                </p>
            </div>
        @else
            <!-- Result Meta Bar -->
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-zinc-200">
                <p class="text-xs uppercase tracking-wider font-semibold text-zinc-500">
                    Menampilkan <span class="text-zinc-950 font-bold">{{ $posts->total() }}</span> cerita terkurasi
                </p>
                @if ($q || $category || $city)
                    <button wire:click="$set('q', ''); $set('category', ''); $set('city', '')" 
                            type="button"
                            class="text-xs text-amber-800 hover:text-amber-900 font-semibold flex items-center gap-1 transition-colors">
                        <i class="fa-solid fa-xmark text-[11px]"></i> Reset Filter
                    </button>
                @endif
            </div>

            <!-- Posts Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>

            <!-- Pagination Links -->
            <div class="mt-12 pt-6 border-t border-zinc-200">
                {{ $posts->links() }}
            </div>
        @endif

    </div>
</div>
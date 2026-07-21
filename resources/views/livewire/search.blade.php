<div>
    <section class="border-b border-slate-200/80 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Cari di sekitarmu</h1>
            <p class="mt-1.5 text-slate-500">Filter kata kunci, kategori, dan kota — hasil update live.</p>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <section class="relative z-20 mb-8 overflow-visible rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-6">
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="sm:col-span-3">
                    <label for="q" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Kata kunci</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                        </svg>
                        <input
                            id="q"
                            type="search"
                            wire:model.live.debounce.400ms="q"
                            placeholder="Contoh: hotel murah, bakso, lowongan..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-3 text-sm font-medium text-slate-900 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20"
                        >
                    </div>
                </div>
                <div class="relative">
                    <label for="category" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Kategori</label>
                    @include('partials.searchable-select', [
                        'model' => 'category',
                        'options' => $categories,
                        'placeholder' => 'Pilih kategori',
                        'emptyLabel' => 'Semua kategori',
                        'id' => 'category',
                    ])
                </div>
                <div class="relative">
                    <label for="city" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Kota</label>
                    @include('partials.searchable-select', [
                        'model' => 'city',
                        'options' => $cities,
                        'placeholder' => 'Pilih kota',
                        'emptyLabel' => 'Semua kota',
                        'id' => 'city',
                    ])
                </div>
            </div>
        </section>

        @if ($posts->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <p class="font-semibold text-slate-800">Tidak ada hasil</p>
                <p class="mt-1 text-sm text-slate-500">Coba kata kunci lain atau longgarkan filter.</p>
            </div>
        @else
            <p class="mb-4 text-sm font-medium text-slate-500">
                <span class="font-bold text-brand-700">{{ $posts->total() }}</span> hasil ditemukan
            </p>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>
            <div class="mt-10">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
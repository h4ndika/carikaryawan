<div>
    <header class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Cari di sekitarmu</h1>
        <p class="mt-1 text-slate-600">Filter berdasarkan kata kunci, kategori, dan kota.</p>
    </header>

    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="sm:col-span-3">
                <label for="q" class="mb-1 block text-xs font-medium text-slate-600">Kata kunci</label>
                <input
                    id="q"
                    type="search"
                    wire:model.live.debounce.400ms="q"
                    placeholder="Contoh: hotel murah, bakso, lowongan..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
            </div>
            <div>
                <label for="category" class="mb-1 block text-xs font-medium text-slate-600">Kategori</label>
                <select
                    id="category"
                    wire:model.live="category"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $item)
                        <option value="{{ $item->slug }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="city" class="mb-1 block text-xs font-medium text-slate-600">Kota</label>
                <select
                    id="city"
                    wire:model.live="city"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
                    <option value="">Semua kota</option>
                    @foreach ($cities as $item)
                        <option value="{{ $item->slug }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    @if ($posts->isEmpty())
        <p class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-slate-500">
            Tidak ada hasil. Coba kata kunci lain atau longgarkan filter.
        </p>
    @else
        <p class="mb-4 text-sm text-slate-500">{{ $posts->total() }} hasil</p>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                @include('partials.post-card', ['post' => $post])
            @endforeach
        </div>
        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    @endif
</div>
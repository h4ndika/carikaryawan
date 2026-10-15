<div class="py-10 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <header class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">Cari lowongan kerja</h1>
            <p class="mt-3 text-base text-zinc-600 dark:text-zinc-300">Temukan posisi yang cocok di kota tujuanmu.</p>
        </header>
        <section aria-label="Filter lowongan" class="bg-white dark:bg-zinc-900 rounded-xl p-5 sm:p-6 mb-8">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="q" class="block text-sm font-semibold mb-2">Posisi atau kata kunci</label>
                    <input id="q" type="search" wire:model.live.debounce.400ms="q" placeholder="Contoh: admin, barista, desainer" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-4 py-3 text-base">
                </div>
                <div>
                    <label for="city" class="block text-sm font-semibold mb-2">Kota</label>
                    <select id="city" wire:model.live="city" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-4 py-3 text-base">
                        <option value="">Semua kota</option>
                        @foreach ($cities as $cityOption)
                            <option value="{{ $cityOption->slug }}">{{ $cityOption->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>
        <x-ad-slot slotName="ad_search_top" />
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <p class="text-sm text-zinc-600 dark:text-zinc-300" role="status">{{ $posts->total() }} lowongan ditemukan</p>
            @if ($q || $category || $city)
                <button wire:click="resetFilters" type="button" class="px-3 py-2 text-sm font-semibold text-blue-700 dark:text-blue-400 hover:underline cursor-pointer">Reset filter</button>
            @endif
        </div>
        <div wire:loading.delay role="status" class="mb-4 text-sm text-zinc-600 dark:text-zinc-300">Memuat lowongan...</div>
        <div id="posts-feed" class="scroll-mt-32 space-y-3">
            @forelse ($posts as $post)
                @include('partials.article-feed', ['post' => $post])
            @empty
                <div class="rounded-xl bg-white dark:bg-zinc-900 p-8 sm:p-10">
                    <h2 class="text-xl font-bold">Belum ada yang cocok</h2>
                    <p class="mt-3 text-base text-zinc-600 dark:text-zinc-300">Coba kata kunci lain atau pilih semua kota untuk memperluas pencarian.</p>
                </div>
            @endforelse
        </div>
        <div class="mt-8">{{ $posts->links('partials.pagination') }}</div>
    </div>
</div>

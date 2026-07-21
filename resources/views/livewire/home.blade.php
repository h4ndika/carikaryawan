<div>
    {{-- Hero --}}
    <section class="hero-mesh relative">
        <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
            <div class="absolute -left-20 top-10 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-accent-400/20 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-6xl px-4 pb-16 pt-12 sm:px-6 sm:pb-20 sm:pt-16">
            <div class="mx-auto max-w-2xl text-center">
                <p class="mb-3 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white/90 backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-400"></span>
                    Platform lokal Indonesia
                </p>
                <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Apa saja di
                    <span class="bg-gradient-to-r from-white to-brand-200 bg-clip-text text-transparent">sekitarmu</span>?
                </h1>
                <p class="mx-auto mt-4 max-w-xl text-base text-white/80 sm:text-lg">
                    Temukan hotel, kuliner, wisata, event & lowongan di kota favoritmu — cepat, rapi, siap dieksplor.
                </p>
            </div>

            {{-- Search card --}}
            <div class="relative z-20 mx-auto mt-8 max-w-3xl overflow-visible rounded-2xl bg-white p-4 shadow-2xl shadow-brand-950/30 sm:p-5">
                <form wire:submit="goToList" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
                    <div class="relative">
                        <label for="selectedCategory" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">
                            Kategori
                        </label>
                        @include('partials.searchable-select', [
                            'model' => 'selectedCategory',
                            'options' => $categories,
                            'placeholder' => 'Pilih kategori',
                            'emptyLabel' => 'Pilih kategori',
                            'id' => 'selectedCategory',
                        ])
                        @error('selectedCategory')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="relative">
                        <label for="selectedCity" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">
                            Kota / Daerah
                        </label>
                        @include('partials.searchable-select', [
                            'model' => 'selectedCity',
                            'options' => $cities,
                            'placeholder' => 'Pilih kota',
                            'emptyLabel' => 'Pilih kota',
                            'id' => 'selectedCity',
                        ])
                        @error('selectedCity')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-end">
                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700 active:scale-[0.98] sm:min-w-[120px]"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                            </svg>
                            Cari
                        </button>
                    </div>
                </form>

                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span class="font-medium">Atau:</span>
                    <a href="{{ route('search') }}" wire:navigate class="rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-brand-700 transition hover:bg-brand-50">
                        Pencarian lanjutan →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        {{-- Category chips --}}
        @if ($categories->isNotEmpty())
                <section class="-mt-6 relative z-0 mb-10">
                <div class="chip-scroll flex gap-2 overflow-x-auto rounded-2xl border border-slate-200/80 bg-white p-3 shadow-lg shadow-slate-200/50">
                    @foreach ($categories as $category)
                        <button
                            type="button"
                            wire:click="$set('selectedCategory', '{{ $category->slug }}')"
                            class="shrink-0 rounded-full border px-4 py-2 text-sm font-semibold transition
                                {{ $selectedCategory === $category->slug
                                    ? 'border-brand-600 bg-brand-600 text-white shadow-sm shadow-brand-600/30'
                                    : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700' }}"
                        >
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Latest --}}
        <section class="pb-4">
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">Terbaru di sekitarmu</h2>
                    <p class="mt-1 text-sm text-slate-500">Update listing yang baru dipublish</p>
                </div>
                <a href="{{ route('search') }}" wire:navigate class="hidden text-sm font-semibold text-brand-700 hover:text-brand-800 sm:inline">
                    Lihat semua →
                </a>
            </div>

            @if ($posts->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-500">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="mt-4 font-semibold text-slate-800">Belum ada post published</p>
                    <p class="mt-1 text-sm text-slate-500">Publish konten dari panel admin untuk mulai tampil di sini.</p>
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        @include('partials.post-card', ['post' => $post])
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Value props --}}
        <section class="mt-12 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                </div>
                <h3 class="mt-3 font-bold text-slate-900">Berbasis lokasi</h3>
                <p class="mt-1 text-sm leading-relaxed text-slate-500">Konten terstruktur per kota & kategori — SEO lokal kuat.</p>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-500/10 text-accent-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                </div>
                <h3 class="mt-3 font-bold text-slate-900">Cepat & ringan</h3>
                <p class="mt-1 text-sm leading-relaxed text-slate-500">Optimized untuk shared hosting — image URL/CDN friendly.</p>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                    </svg>
                </div>
                <h3 class="mt-3 font-bold text-slate-900">Siap dieksplor</h3>
                <p class="mt-1 text-sm leading-relaxed text-slate-500">Detail kaya, galeri, related posts — UX ala travel platform.</p>
            </div>
        </section>
    </div>
</div>
<div>
    {{-- Compact page hero --}}
    <section class="border-b border-slate-200/80 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <nav class="mb-4 flex flex-wrap items-center gap-1.5 text-sm text-slate-500">
                <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-700">Beranda</a>
                <svg class="h-3.5 w-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-slate-800">{{ $category->name }}</span>
                <svg class="h-3.5 w-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-slate-800">{{ $city->name }}</span>
            </nav>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                        {{ $category->name }} di {{ $city->name }}
                    </h1>
                    <p class="mt-1.5 text-slate-500">
                        Daftar {{ strtolower($category->name) }} di sekitar {{ $city->name }}.
                        @if (! $posts->isEmpty())
                            <span class="font-semibold text-brand-700">{{ $posts->total() }} hasil</span>
                        @endif
                    </p>
                </div>
                <a
                    href="{{ route('search', ['category' => $category->slug, 'city' => $city->slug]) }}"
                    wire:navigate
                    class="inline-flex items-center gap-1.5 self-start rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                    </svg>
                    Filter lanjutan
                </a>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        @if ($posts->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <p class="mt-4 font-semibold text-slate-800">Belum ada post di sini</p>
                <p class="mt-1 text-sm text-slate-500">Coba kategori atau kota lain, atau gunakan pencarian.</p>
                <a href="{{ route('home') }}" wire:navigate class="mt-5 inline-flex rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-700">
                    Kembali ke beranda
                </a>
            </div>
        @else
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
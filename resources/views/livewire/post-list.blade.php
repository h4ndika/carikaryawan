<div class="min-h-screen bg-[#fafaf9] dark:bg-zinc-950 py-8 sm:py-12 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb & Header -->
        <nav class="flex items-center gap-2 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-medium mb-6">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">Beranda</a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <a href="{{ route('category.home', ['category' => $category->slug]) }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">{{ $category->name }}</a>
            @if ($citySlug && $city)
                <span class="text-zinc-300 dark:text-zinc-700">/</span>
                <span class="text-zinc-900 dark:text-zinc-100 font-semibold">{{ $city->name }}</span>
            @endif
        </nav>

        <header class="mb-10 max-w-3xl">
            <span class="text-xs uppercase tracking-widest font-bold text-amber-800 dark:text-amber-400 block mb-1">
                Kurasi Direktori
            </span>
            <h1 class="font-serif-title text-3xl sm:text-4xl lg:text-5xl font-bold text-zinc-950 dark:text-zinc-50 tracking-tight">
                @if ($citySlug && $city)
                    {{ $category->name }} di {{ $city->name }}
                @else
                    {{ $category->name }}
                @endif
            </h1>
            <p class="mt-2 text-sm sm:text-base text-zinc-600 dark:text-zinc-400 leading-relaxed">
                @if ($citySlug && $city)
                    Rekomendasi dan ulasan {{ strtolower($category->name) }} pilihan di wilayah {{ $city->name }}.
                @else
                    Rekomendasi dan ulasan {{ strtolower($category->name) }} pilihan di seluruh Indonesia.
                @endif
                @if (! $posts->isEmpty())
                    <span class="font-semibold text-zinc-900 dark:text-zinc-200">({{ $posts->total() }} catatan terbit)</span>
                @endif
            </p>
        </header>

        @if ($posts->isEmpty())
            <div class="rounded-2xl border border-dashed border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-14 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-50 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 mb-3">
                    <i class="fa-solid fa-feather-pointed text-xl"></i>
                </div>
                <h3 class="font-serif-title text-lg font-bold text-zinc-900 dark:text-zinc-100">Belum Ada Cerita di Sini</h3>
                <p class="mt-1 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                    Coba jelajahi kategori atau kota lain, atau gunakan menu pencarian.
                </p>
                <a href="{{ route('home') }}" wire:navigate class="mt-6 inline-flex rounded-xl bg-zinc-900 dark:bg-zinc-100 px-5 py-2.5 text-xs font-semibold text-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-white transition-colors">
                    Kembali ke Beranda
                </a>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>

            <div class="mt-12 pt-6 border-t border-zinc-200 dark:border-zinc-800">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
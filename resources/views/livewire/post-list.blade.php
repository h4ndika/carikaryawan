<div class="min-h-screen bg-[#fafaf9] py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb & Header -->
        <nav class="flex items-center gap-2 text-xs uppercase tracking-wider text-zinc-500 font-medium mb-6">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 transition-colors">Beranda</a>
            <span class="text-zinc-300">/</span>
            <a href="{{ route('category.home', ['category' => $category->slug]) }}" wire:navigate class="hover:text-zinc-950 transition-colors">{{ $category->name }}</a>
            @if ($citySlug)
                <span class="text-zinc-300">/</span>
                <span class="text-zinc-900 font-semibold">{{ $city->name }}</span>
            @endif
        </nav>

        <header class="mb-10 max-w-3xl">
            <span class="text-xs uppercase tracking-widest font-bold text-amber-800 block mb-1">
                Kurasi Direktori
            </span>
            <h1 class="font-serif-title text-3xl sm:text-4xl lg:text-5xl font-bold text-zinc-950 tracking-tight">
                @if ($citySlug)
                    {{ $category->name }} di {{ $city->name }}
                @else
                    {{ $category->name }}
                @endif
            </h1>
            <p class="mt-2 text-sm sm:text-base text-zinc-600 leading-relaxed">
                @if ($citySlug)
                    Rekomendasi dan ulasan {{ strtolower($category->name) }} pilihan di wilayah {{ $city->name }}.
                @else
                    Rekomendasi dan ulasan {{ strtolower($category->name) }} pilihan di seluruh Indonesia.
                @endif
                @if (! $posts->isEmpty())
                    <span class="font-semibold text-zinc-900">({{ $posts->total() }} catatan terbit)</span>
                @endif
            </p>
        </header>

        @if ($posts->isEmpty())
            <div class="rounded-2xl border border-dashed border-zinc-200 bg-white p-14 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-50 text-zinc-400 mb-3">
                    <i class="fa-solid fa-feather-pointed text-xl"></i>
                </div>
                <h3 class="font-serif-title text-lg font-bold text-zinc-900">Belum Ada Cerita di Sini</h3>
                <p class="mt-1 text-xs sm:text-sm text-zinc-500 max-w-sm mx-auto">
                    Coba jelajahi kategori atau kota lain, atau gunakan menu pencarian.
                </p>
                <a href="{{ route('home') }}" wire:navigate class="mt-6 inline-flex rounded-xl bg-zinc-900 px-5 py-2.5 text-xs font-semibold text-white hover:bg-zinc-800 transition-colors">
                    Kembali ke Beranda
                </a>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>

            <div class="mt-12 pt-6 border-t border-zinc-200">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
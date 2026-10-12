<div class="min-h-screen bg-[#fafaf9] dark:bg-zinc-950 py-8 sm:py-12 transition-colors duration-300">
    @php
        $feedPosts = $posts;
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumbs Navigation -->
        <nav class="flex items-center gap-2 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-medium mb-6">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">Beranda</a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <a href="{{ route('category.home', ['category' => $category->slug]) }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">{{ $category->name }}</a>
            @if ($citySlug && $city)
                <span class="text-zinc-300 dark:text-zinc-700">/</span>
                <span class="text-zinc-900 dark:text-zinc-100 font-semibold">{{ $city->name }}</span>
            @endif
        </nav>

        <!-- Category Header -->
            <header class="mb-10 max-w-3xl">
                <span class="text-xs uppercase tracking-widest font-bold text-amber-800 dark:text-amber-400 block mb-1">
                    Lowongan Kerja
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
                        Lowongan {{ strtolower($category->name) }} terbaru di {{ $city->name }}.
                    @else
                        Lowongan {{ strtolower($category->name) }} terbaru di seluruh Indonesia.
                    @endif
                    @if (! $posts->isEmpty())
                        <span class="font-semibold text-zinc-900 dark:text-zinc-200">({{ $posts->total() }} lowongan)</span>
                    @endif
                </p>
            </header>


        <!-- City Quick Filter Bar -->
        @if (isset($cities) && $cities->count() > 0)
            <div class="mb-10 pb-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 flex-shrink-0 mr-1">
                    Wilayah:
                </span>
                <a href="{{ route('category.home', ['category' => $category->slug]) }}" 
                   wire:navigate
                   class="flex-shrink-0 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors {{ empty($citySlug) ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:border-zinc-900 dark:hover:border-zinc-400' }}">
                    Semua Kota
                </a>
                @foreach ($cities as $cityItem)
                    <a href="{{ route('posts.list', ['category' => $category->slug, 'city' => $cityItem->slug]) }}" 
                       wire:navigate
                       class="flex-shrink-0 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $citySlug === $cityItem->slug ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:border-zinc-900 dark:hover:border-zinc-400' }}">
                        {{ $cityItem->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Main Feed & Editorial Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14">
            <!-- Article Feed Column (8 cols) -->
            <div id="posts-feed" class="lg:col-span-8 scroll-mt-24">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-zinc-950 dark:text-zinc-50">
                        @if ($citySlug && $city)
                            Daftar {{ $category->name }} di {{ $city->name }}
                        @else
                            Semua Lowongan {{ $category->name }}
                        @endif
                    </h2>
                    <span class="text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-semibold">
                        {{ $posts->total() }} Lowongan
                    </span>
                </div>

                @if ($feedPosts->isEmpty())
                    <div class="rounded-xl border border-dashed border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-50 dark:bg-zinc-800 text-zinc-400 mb-3">
                            <i class="fa-solid fa-feather-pointed text-xl"></i>
                        </div>
                        <h3 class="font-serif-title text-lg font-bold text-zinc-900 dark:text-zinc-100">Belum Ada Lowongan</h3>
                        <p class="mt-1 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                            Belum ada lowongan pada kategori atau kota ini. Coba lagi nanti.
                        </p>
                        <a href="{{ route('category.home', ['category' => $category->slug]) }}" wire:navigate class="mt-6 inline-flex rounded-xl bg-zinc-900 dark:bg-zinc-100 px-5 py-2.5 text-xs font-semibold text-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-white transition-colors">
                            Lihat Semua {{ $category->name }}
                        </a>
                    </div>
                @else
                    <div class="space-y-10">
                        @foreach ($feedPosts as $post)
                            @include('partials.article-feed', ['post' => $post])
                        @endforeach
                    </div>

                    <!-- Pagination Links -->
                    <div class="mt-10 pt-6 border-t border-zinc-200 dark:border-zinc-800">
                        {{ $posts->links('partials.pagination') }}
                    </div>
                @endif
            </div>

            <!-- Journal Sidebar (4 cols) -->
            <aside class="lg:col-span-4 space-y-10">
                <!-- Lowongan Terbaru -->
                @if (isset($editorPicks) && $editorPicks->count() > 0)
                <div class="bg-white dark:bg-zinc-900/90 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs">
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-6 text-zinc-900 dark:text-zinc-200 pb-3 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                        <span>Lowongan Terbaru</span>
                        <i class="fa-solid fa-bookmark text-amber-800 dark:text-amber-400 text-xs"></i>
                    </h3>
                    @include('partials.editor-picks', ['posts' => $editorPicks])
                </div>
                @endif

                <!-- Ad Slot: Sidebar Kategori -->
                <x-ad-slot slotName="ad_category_sidebar" />

                <!-- Kota -->
                @if (isset($cities) && $cities->count() > 0)
                <div class="bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
                    <h3 class="text-xs uppercase tracking-widest font-bold mb-3 text-zinc-900 dark:text-zinc-200">Jelajah Kota</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed mb-4">
                        Pilih kota untuk melihat lowongan {{ strtolower($category->name) }} di sana.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($cities->take(16) as $c)
                            <a href="{{ route('posts.list', ['category' => $category->slug, 'city' => $c->slug]) }}" 
                               wire:navigate
                               class="px-2.5 py-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-950 dark:hover:border-zinc-400 hover:bg-zinc-900 dark:hover:bg-zinc-100 hover:text-white dark:hover:text-zinc-950 rounded-md text-xs font-medium text-zinc-700 dark:text-zinc-300 transition-colors {{ $citySlug === $c->slug ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 border-zinc-900 dark:border-zinc-100' : '' }}">
                                {{ $c->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Catatan Agregator -->
                <div class="border-l-2 border-amber-800 dark:border-amber-500 pl-4 py-1">
                    <h4 class="font-serif-title font-bold text-sm text-zinc-950 dark:text-zinc-100">Lamar ke Sumber Asli</h4>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed mt-1">
                        CariKaryawan mengumpulkan lowongan dari berbagai sumber. Kami bukan pemberi kerja; lamaran dikirim lewat tautan asli lowongan.
                    </p>
                </div>
            </aside>
        </div>

    </div>
</div>
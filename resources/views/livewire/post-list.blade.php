<div class="py-10 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav aria-label="Jejak halaman" class="flex flex-wrap gap-2 text-sm text-zinc-600 dark:text-zinc-300 mb-6">
            <a href="{{ route('home') }}" wire:navigate class="hover:underline">Beranda</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('category.home', ['category' => $category->slug]) }}" wire:navigate class="hover:underline">Lowongan kerja</a>
            @if ($city)
                <span aria-hidden="true">/</span><span>{{ $city->name }}</span>
            @endif
        </nav>
        <header class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">{{ $city ? 'Loker di ' . $city->name : 'Lowongan kerja' }}</h1>
            <p class="mt-3 text-base text-zinc-600 dark:text-zinc-300">{{ $posts->total() }} lowongan. Periksa detailnya dan lamar lewat sumber asli.</p>
        </header>
        @if ($cities->isNotEmpty())
            <nav aria-label="Filter kota" class="flex flex-wrap gap-3 mb-8 text-sm">
                <a href="{{ route('category.home', ['category' => $category->slug]) }}" wire:navigate class="px-4 py-3 rounded-lg {{ !$citySlug ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-white dark:bg-zinc-800' }}">Semua kota</a>
                @foreach ($cities as $cityItem)
                    <a href="{{ route('posts.list', ['category' => $category->slug, 'city' => $cityItem->slug]) }}" wire:navigate @if($citySlug === $cityItem->slug) aria-current="page" @endif class="px-4 py-3 rounded-lg {{ $citySlug === $cityItem->slug ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-white dark:bg-zinc-800' }}">{{ $cityItem->name }}</a>
                @endforeach
            </nav>
        @endif
        <div class="grid gap-10 lg:grid-cols-12 items-start">
            <section id="posts-feed" class="lg:col-span-8 scroll-mt-32 space-y-3" aria-label="Daftar lowongan">
                @forelse ($posts as $post)
                    @include('partials.article-feed', ['post' => $post])
                @empty
                    <div class="rounded-xl bg-white dark:bg-zinc-900 p-8">
                        <h2 class="text-xl font-bold">Belum ada lowongan</h2>
                        <p class="mt-3 text-base text-zinc-600 dark:text-zinc-300">Coba cari posisi di kota lain.</p>
                        <a href="{{ route('search') }}" wire:navigate class="inline-block mt-5 font-semibold text-blue-700 dark:text-blue-400 hover:underline">Cari semua lowongan</a>
                    </div>
                @endforelse
                <div class="pt-5">{{ $posts->links('partials.pagination') }}</div>
            </section>
            <aside class="lg:col-span-4 space-y-6">
                @if ($editorPicks->isNotEmpty())
                    <h2 class="text-lg font-bold">Lowongan terbaru lainnya</h2>
                    @include('partials.editor-picks', ['posts' => $editorPicks])
                @endif
                <x-ad-slot slotName="ad_category_sidebar" />
                <div class="p-6 bg-zinc-100 dark:bg-zinc-900 rounded-xl">
                    <h2 class="text-lg font-bold">Siap melamar?</h2>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">Siapkan CV dan surat lamaran sebelum membuka tautan pemberi kerja.</p>
                    <a href="{{ route('tools.hub') }}" wire:navigate class="inline-block mt-4 text-sm font-semibold hover:underline">Buka tools karier</a>
                </div>
            </aside>
        </div>
    </div>
</div>

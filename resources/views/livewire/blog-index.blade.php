<main class="flex-grow bg-[#faf9f6] dark:bg-zinc-950 py-8 sm:py-12 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Hero Header -->
        <div class="mb-10 text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-widest bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 mb-4">
                <i class="fa-solid fa-feather-pointed"></i> Jurnal & Editorial
            </span>
            <h1 class="font-serif-title text-3xl sm:text-5xl font-bold tracking-tight text-zinc-950 dark:text-zinc-50 mb-3">
                Panduan Eksplorasi Lokal
            </h1>
            <p class="text-zinc-600 dark:text-zinc-400 text-sm sm:text-base leading-relaxed">
                Artikel kurasi, rekomendasi tempat hits, kuliner autentik, dan cerita tersembunyi dari berbagai sudut kota di Indonesia.
            </p>
        </div>

        <!-- Filter Bar -->
        @if ($categories->isNotEmpty() || $cities->isNotEmpty())
            <div class="mb-8 p-4 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl flex flex-wrap items-center justify-between gap-4 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <button wire:click="$set('selectedCategory', '')" 
                            class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors {{ empty($selectedCategory) ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                        Semua Topik
                    </button>
                    @foreach ($categories as $cat)
                        <button wire:click="$set('selectedCategory', '{{ $cat->slug }}')"
                                class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $selectedCategory === $cat->slug ? 'bg-amber-800 text-white dark:bg-amber-600' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>

                @if ($cities->isNotEmpty())
                    <div class="w-full sm:w-56">
                        @include('partials.searchable-select', [
                            'model' => 'selectedCity',
                            'options' => $cities,
                            'placeholder' => 'Pilih kota...',
                            'emptyLabel' => 'Semua Kota',
                            'allowEmpty' => true,
                            'id' => 'filter-city',
                        ])
                    </div>
                @endif
            </div>
        @endif

        <!-- Articles Grid -->
        @if ($articles->isEmpty())
            <div class="text-center py-16 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl p-8">
                <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-400 mx-auto flex items-center justify-center text-xl mb-3">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-1">Belum Ada Artikel</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Artikel editorial untuk kategori atau kota ini sedang disiapkan.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                @foreach ($articles as $article)
                    <article class="flex flex-col bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl overflow-hidden hover:shadow-lg transition-all duration-300 group">
                        <a href="{{ $article->url() }}" wire:navigate class="block relative aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                            <img src="{{ $article->featured_image_url ?: asset('images/placeholder.svg') }}" 
                                 alt="{{ $article->title }}" 
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60"></div>
                            @if ($article->city)
                                <span class="absolute bottom-3 left-3 px-2.5 py-1 rounded-md text-[11px] font-bold bg-black/70 backdrop-blur-sm text-white flex items-center gap-1">
                                    <i class="fa-solid fa-location-dot text-amber-400 text-[10px]"></i>
                                    {{ $article->city->name }}
                                </span>
                            @endif
                        </a>

                        <div class="p-5 sm:p-6 flex flex-col flex-grow">
                            <div class="flex items-center gap-2 text-[11px] text-zinc-500 dark:text-zinc-400 mb-2.5">
                                @if ($article->category)
                                    <span class="font-semibold uppercase tracking-wider text-amber-800 dark:text-amber-400">
                                        {{ $article->category->name }}
                                    </span>
                                    <span>•</span>
                                @endif
                                <span>{{ $article->published_at?->translatedFormat('d M Y') }}</span>
                                <span>•</span>
                                <span>{{ $article->readingTime() }}</span>
                            </div>

                            <h2 class="font-serif-title text-xl font-bold text-zinc-950 dark:text-zinc-50 leading-snug group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors mb-2.5">
                                <a href="{{ $article->url() }}" wire:navigate>
                                    {{ $article->title }}
                                </a>
                            </h2>

                            <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4 line-clamp-3">
                                {{ $article->excerpt }}
                            </p>

                            <div class="mt-auto pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs font-semibold text-amber-800 dark:text-amber-400">
                                <a href="{{ $article->url() }}" wire:navigate class="inline-flex items-center gap-1 hover:gap-2 transition-all">
                                    <span>Baca Selengkapnya</span>
                                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $articles->links() }}
            </div>
        @endif
    </div>
</main>

<main class="flex-grow bg-[#faf9f6] dark:bg-zinc-950 py-8 sm:py-12 transition-colors duration-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400 mb-6 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors">Beranda</a>
            <span>/</span>
            <a href="{{ route('blog.index') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-white transition-colors">Blog</a>
            @if ($article->city)
                <span>/</span>
                <span class="text-zinc-700 dark:text-zinc-300 font-medium">{{ $article->city->name }}</span>
            @endif
        </nav>

        <!-- Article Header -->
        <header class="mb-8">
            <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400 mb-3">
                @if ($article->category)
                    <span class="font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400">
                        {{ $article->category->name }}
                    </span>
                    <span>•</span>
                @endif
                <span>{{ $article->published_at?->translatedFormat('d F Y') }}</span>
                <span>•</span>
                <span>{{ $article->readingTime() }}</span>
                <span>•</span>
                <span><i class="fa-regular fa-eye"></i> {{ number_format($article->views_count) }} dibaca</span>
            </div>

            <h1 class="font-serif-title text-2xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-zinc-950 dark:text-zinc-50 leading-[1.2] mb-4">
                {{ $article->title }}
            </h1>

            @if ($article->excerpt)
                <p class="text-base sm:text-lg text-zinc-600 dark:text-zinc-300 font-sans leading-relaxed border-l-2 border-amber-600 pl-4 my-4 italic">
                    {{ $article->excerpt }}
                </p>
            @endif
        </header>

        <!-- Featured Image -->
        @if ($article->featured_image_url)
            <div class="relative aspect-[16/9] w-full rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 shadow-md mb-8">
                <img src="{{ $article->featured_image_url }}" 
                     alt="{{ $article->title }}" 
                     class="w-full h-full object-cover">
            </div>
        @endif

        <!-- Article Body Content -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl p-6 sm:p-10 shadow-sm mb-10">
            <div class="prose prose-zinc dark:prose-invert max-w-none prose-headings:font-serif-title prose-headings:font-bold prose-h2:text-2xl sm:prose-h2:text-3xl prose-h2:border-b prose-h2:border-zinc-100 dark:prose-h2:border-zinc-800 prose-h2:pb-2 prose-h2:mt-8 prose-h2:mb-4 prose-p:leading-relaxed prose-p:text-zinc-700 dark:prose-p:text-zinc-300 prose-a:text-amber-800 dark:prose-a:text-amber-400 prose-a:no-underline hover:prose-a:underline">
                {!! $article->content !!}
            </div>

            <!-- Share Section -->
            <div class="mt-10 pt-6 border-t border-zinc-100 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-4">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    Bagikan Artikel Ini:
                </span>
                <div class="flex items-center gap-2">
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($article->title . ' ' . $article->url()) }}" 
                       target="_blank" 
                       rel="noopener"
                       class="w-9 h-9 rounded-full bg-emerald-500 text-white flex items-center justify-center hover:opacity-90 transition-opacity"
                       title="Bagikan ke WhatsApp">
                        <i class="fa-brands fa-whatsapp text-base"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode($article->url()) }}" 
                       target="_blank" 
                       rel="noopener"
                       class="w-9 h-9 rounded-full bg-zinc-900 text-white dark:bg-zinc-800 dark:hover:bg-zinc-700 flex items-center justify-center hover:opacity-90 transition-opacity"
                       title="Bagikan ke X/Twitter">
                        <i class="fa-brands fa-x-twitter text-sm"></i>
                    </a>
                    <button onclick="navigator.clipboard.writeText('{{ $article->url() }}'); alert('Tautan artikel berhasil disalin!');"
                            class="w-9 h-9 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 flex items-center justify-center hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors"
                            title="Salin Tautan">
                        <i class="fa-solid fa-link text-sm"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Related Articles -->
        @if ($relatedArticles->isNotEmpty())
            <div class="mt-12">
                <h3 class="font-serif-title text-xl sm:text-2xl font-bold text-zinc-950 dark:text-zinc-50 mb-6">
                    Artikel Terkait Lainnya
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @foreach ($relatedArticles as $rel)
                        <article class="bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-xl overflow-hidden hover:shadow-md transition-shadow group flex flex-col">
                            <a href="{{ $rel->url() }}" wire:navigate class="aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800 block">
                                <img src="{{ $rel->featured_image_url ?: asset('images/placeholder.svg') }}" 
                                     alt="{{ $rel->title }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </a>
                            <div class="p-4 flex flex-col flex-grow">
                                <span class="text-[10px] uppercase font-bold text-amber-800 dark:text-amber-400 mb-1">
                                    {{ $rel->city?->name ?? 'Indonesia' }}
                                </span>
                                <h4 class="font-serif-title text-sm font-bold text-zinc-950 dark:text-zinc-50 leading-snug group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors line-clamp-2 mb-2">
                                    <a href="{{ $rel->url() }}" wire:navigate>
                                        {{ $rel->title }}
                                    </a>
                                </h4>
                                <span class="mt-auto text-[11px] text-zinc-400">
                                    {{ $rel->published_at?->translatedFormat('d M Y') }}
                                </span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</main>

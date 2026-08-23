@php
    $href = route('posts.show', [
        'category' => $post->category->slug,
        'city' => $post->city->slug,
        'slug' => $post->slug,
    ]);
    $thumb = $post->primary_image_url;
    $fallbackThumbUrls = $post->fallback_image_urls;
@endphp

<article class="group flex flex-col bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
    <a href="{{ $href }}" wire:navigate class="block aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800 relative">
        @if ($thumb)
            <img
                src="{{ $thumb }}"
                alt="{{ $post->title }}"
                data-fallbacks='@json($fallbackThumbUrls)'
                onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                loading="lazy"
            >
        @else
            <div class="flex h-full w-full flex-col items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 p-4">
                <i class="fa-solid fa-camera text-2xl mb-1 text-zinc-300 dark:text-zinc-600"></i>
                <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400 dark:text-zinc-500">DISEKITAR</span>
            </div>
        @endif

        <span class="absolute top-3 left-3 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-sm px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 shadow-xs border border-zinc-200/50 dark:border-zinc-800">
            {{ $post->category->name }}
        </span>
    </a>

    <div class="flex flex-1 flex-col justify-between p-5 space-y-3">
        <div>
            <h3 class="font-serif-title font-bold text-lg leading-snug text-zinc-950 dark:text-zinc-100 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors line-clamp-2">
                <a href="{{ $href }}" wire:navigate>
                    {{ $post->title }}
                </a>
            </h3>

            @if ($post->excerpt)
                <p class="mt-2 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed line-clamp-2">
                    {{ $post->excerpt }}
                </p>
            @endif
        </div>

        <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400 font-medium">
            <div class="flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                <i class="fa-solid fa-location-dot text-amber-800 dark:text-amber-400 text-[11px]"></i>
                <span class="truncate">{{ $post->city->name }}</span>
            </div>
            <span>{{ max(1, round(str_word_count(strip_tags($post->content ?? '')) / 200)) }} min baca</span>
        </div>
    </div>
</article>
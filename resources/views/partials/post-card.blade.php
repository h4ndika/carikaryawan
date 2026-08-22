@php
    $href = route('posts.show', [
        'category' => $post->category->slug,
        'city' => $post->city->slug,
        'slug' => $post->slug,
    ]);
    $thumb = $post->featured_image_url ?: $post->thumbnail;
@endphp

<article class="group flex flex-col bg-white border border-zinc-200 rounded-xl overflow-hidden shadow-sm hover:border-zinc-300 transition-all">
    <a href="{{ $href }}" wire:navigate class="block aspect-[16/10] overflow-hidden bg-zinc-100 relative">
        @if ($thumb)
            <img
                src="{{ $thumb }}"
                alt="{{ $post->title }}"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                loading="lazy"
            >
        @else
            <div class="flex h-full w-full flex-col items-center justify-center bg-zinc-100 text-zinc-400 p-4">
                <i class="fa-solid fa-camera text-2xl mb-1 text-zinc-300"></i>
                <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400">DISEKITAR</span>
            </div>
        @endif

        <span class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider text-amber-800 shadow-sm">
            {{ $post->category->name }}
        </span>
    </a>

    <div class="flex flex-1 flex-col justify-between p-5 space-y-3">
        <div>
            <h3 class="font-serif-title font-bold text-lg leading-snug text-zinc-950 group-hover:text-amber-800 transition-colors line-clamp-2">
                <a href="{{ $href }}" wire:navigate>
                    {{ $post->title }}
                </a>
            </h3>

            @if ($post->excerpt)
                <p class="mt-2 text-xs sm:text-sm text-zinc-600 leading-relaxed line-clamp-2">
                    {{ $post->excerpt }}
                </p>
            @endif
        </div>

        <div class="pt-3 border-t border-zinc-100 flex items-center justify-between text-xs text-zinc-500 font-medium">
            <div class="flex items-center gap-1.5 text-zinc-700">
                <i class="fa-solid fa-location-dot text-amber-800 text-[11px]"></i>
                <span class="truncate">{{ $post->city->name }}</span>
            </div>
            <span>{{ max(1, round(str_word_count(strip_tags($post->content ?? '')) / 200)) }} min baca</span>
        </div>
    </div>
</article>
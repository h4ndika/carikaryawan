<div class="divide-y divide-zinc-100">
    @if (isset($posts) && $posts->count() > 0)
        @foreach ($posts as $pick)
        <article class="py-4 first:pt-0 last:pb-0 group">
            <a href="{{ route('posts.show', ['category' => $pick->category->slug, 'city' => $pick->city->slug, 'slug' => $pick->slug]) }}" 
               wire:navigate class="flex items-start gap-4">
                <span class="font-serif-title text-2xl font-bold text-zinc-300 group-hover:text-amber-800 transition-colors leading-none pt-0.5">
                    {{ sprintf('%02d', $loop->iteration) }}
                </span>
                <div class="flex-grow">
                    <span class="text-[10px] font-bold text-amber-800 uppercase tracking-widest block mb-1">
                        {{ $pick->category->name ?? 'CERITA' }}
                    </span>
                    <h4 class="font-serif-title font-bold text-sm leading-snug text-zinc-900 group-hover:text-amber-800 transition-colors line-clamp-2">
                        {{ $pick->title }}
                    </h4>
                    <p class="text-xs text-zinc-500 mt-1">
                        {{ $pick->city->name ?? 'Indonesia' }}
                    </p>
                </div>
                @if ($pick->featured_image_url)
                    <div class="w-16 h-16 rounded-md overflow-hidden bg-zinc-100 flex-shrink-0">
                        <img src="{{ $pick->featured_image_url }}" 
                             alt="{{ $pick->title }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                @endif
            </a>
        </article>
        @endforeach
    @else
        <p class="text-xs text-zinc-400 py-4 text-center">Belum ada cerita pilihan.</p>
    @endif
</div>

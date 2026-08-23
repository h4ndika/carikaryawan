<div class="divide-y divide-zinc-100 dark:divide-zinc-800">
    @if (isset($posts) && $posts->count() > 0)
        @foreach ($posts as $pick)
        @php
            $pickThumb = $pick->primary_image_url;
            $fallbackPickUrls = $pick->fallback_image_urls;
        @endphp
        <article class="py-4 first:pt-0 last:pb-0 group">
            <a href="{{ route('posts.show', ['category' => $pick->category->slug, 'city' => $pick->city->slug, 'slug' => $pick->slug]) }}" 
               wire:navigate class="flex items-start gap-4">
                <span class="font-serif-title text-2xl font-bold text-zinc-300 dark:text-zinc-700 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors leading-none pt-0.5">
                    {{ sprintf('%02d', $loop->iteration) }}
                </span>
                <div class="flex-grow">
                    <span class="text-[10px] font-bold text-amber-800 dark:text-amber-400 uppercase tracking-widest block mb-1">
                        {{ $pick->category->name ?? 'CERITA' }}
                    </span>
                    <h4 class="font-serif-title font-bold text-sm leading-snug text-zinc-900 dark:text-zinc-100 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors line-clamp-2">
                        {{ $pick->title }}
                    </h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                        {{ $pick->city->name ?? 'Indonesia' }}
                    </p>
                </div>
                <div class="w-16 h-16 rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 flex-shrink-0 border border-zinc-200/60 dark:border-zinc-800 flex items-center justify-center">
                    @if ($pickThumb)
                        <img src="{{ $pickThumb }}" 
                             alt="{{ $pick->title }}" 
                             data-fallbacks='@json($fallbackPickUrls)'
                             onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             loading="lazy">
                    @else
                        <div class="w-full h-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 dark:text-zinc-600">
                            <i class="fa-solid fa-camera text-sm"></i>
                        </div>
                    @endif
                </div>
            </a>
        </article>
        @endforeach
    @else
        <p class="text-xs text-zinc-400 dark:text-zinc-500 py-4 text-center">Belum ada cerita pilihan.</p>
    @endif
</div>

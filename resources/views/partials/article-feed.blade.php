@php
    $thumb = $post->primary_image_url;
    $fallbackThumbUrls = $post->fallback_image_urls;
@endphp

<article class="group pb-10 border-b border-zinc-200/80 dark:border-zinc-800/80 last:border-0 last:pb-0">
    <div class="flex flex-col sm:flex-row gap-6 md:gap-8 items-start">
        <div class="w-full sm:w-5/12 flex-shrink-0">
            <a href="{{ $post->url() }}" 
               wire:navigate class="block overflow-hidden rounded-xl aspect-[16/10] bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/60 dark:border-zinc-800 relative">
                @if ($thumb)
                    <img src="{{ $thumb }}" 
                         alt="{{ $post->title }}" 
                         data-fallbacks='@json($fallbackThumbUrls)'
                         onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                         loading="lazy">
                    @if ($source = $post->primary_image_source)
                        <span class="absolute bottom-2 right-2 px-1.5 py-0.5 rounded bg-black/60 backdrop-blur-xs text-[9px] font-medium text-white/90 tracking-wide pointer-events-none">
                            Foto: {{ $source }}
                        </span>
                    @endif
                @else
                    <div class="w-full h-full bg-zinc-100 dark:bg-zinc-800 flex flex-col items-center justify-center text-zinc-400 dark:text-zinc-500 p-4">
                        <i class="fa-solid fa-camera text-2xl mb-1.5 text-zinc-300 dark:text-zinc-600"></i>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400 dark:text-zinc-500">DISEKITAR</span>
                    </div>
                @endif
            </a>
        </div>
        
        <div class="w-full sm:w-7/12 flex flex-col justify-between space-y-3">
            <div>
                <div class="flex items-center gap-2 text-[11px] uppercase tracking-wider text-amber-800 dark:text-amber-400 font-bold mb-2">
                    <span>{{ $post->category->name ?? 'CERITA' }}</span>
                    <span class="text-zinc-300 dark:text-zinc-700">•</span>
                    <span class="text-zinc-500 dark:text-zinc-400 font-medium">{{ max(1, round(str_word_count(strip_tags($post->content ?? '')) / 200)) }} min baca</span>
                </div>
                
                <h3 class="font-serif-title text-xl sm:text-2xl font-bold leading-snug text-zinc-950 dark:text-zinc-100 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors">
                    <a href="{{ $post->url() }}" 
                       wire:navigate>
                        {{ $post->title }}
                    </a>
                </h3>
                
                <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed line-clamp-2 mt-2">
                    {{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 130) }}
                </p>
            </div>
            
            <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400 pt-3 border-t border-zinc-100 dark:border-zinc-800 font-medium">
                <div class="flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                    <i class="fa-solid fa-location-dot text-amber-800 dark:text-amber-400 text-[11px]"></i>
                    <span>{{ $post->city->name ?? 'Indonesia' }}</span>
                </div>
                <a href="{{ $post->url() }}" 
                   wire:navigate
                   class="font-semibold text-zinc-900 dark:text-zinc-200 hover:text-amber-800 dark:hover:text-amber-400 flex items-center gap-1.5 transition-colors">
                    Baca Catatan <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>
</article>

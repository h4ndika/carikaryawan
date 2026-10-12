@php
    $thumb = $post->primary_image_url;
    $fallbackThumbUrls = $post->fallback_image_urls;
    $isJob = $post->isJob();
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
                        <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400 dark:text-zinc-500">CARIKARYAWAN</span>
                    </div>
                @endif

                @if ($post->isExpired())
                    <span class="absolute top-2.5 left-2.5 bg-red-600/90 text-white backdrop-blur-xs px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider shadow-xs flex items-center gap-1 z-10">
                        <i class="fa-solid fa-clock-rotate-left text-[8px]"></i> Ditutup
                    </span>
                @endif
            </a>
        </div>
        
        <div class="w-full sm:w-7/12 flex flex-col justify-between space-y-3">
            <div>
                <div class="flex items-center gap-2 text-[11px] uppercase tracking-wider text-amber-800 dark:text-amber-400 font-bold mb-2">
                    @if ($isJob)
                        <i class="fa-solid fa-briefcase text-[10px]"></i>
                    @endif
                    <span>{{ $post->category->name ?? 'LOWONGAN' }}</span>
                    @if ($post->isJob() && $post->isExpired())
                        <span class="text-red-600 dark:text-red-400 font-bold text-[10px] bg-red-50 dark:bg-red-950/50 px-1.5 py-0.5 rounded border border-red-200 dark:border-red-900">Ditutup</span>
                    @endif
                    <span class="text-zinc-300 dark:text-zinc-700">•</span>
                    <span class="text-zinc-500 dark:text-zinc-400 font-medium">{{ $post->published_at?->diffForHumans() }}</span>
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
                    Lihat Lowongan <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>
</article>

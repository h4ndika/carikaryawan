<article class="group pb-10 border-b border-zinc-200/80 last:border-0 last:pb-0">
    <div class="flex flex-col sm:flex-row gap-6 md:gap-8 items-start">
        <div class="w-full sm:w-5/12 flex-shrink-0">
            <a href="{{ route('posts.show', ['category' => $post->category->slug, 'city' => $post->city->slug, 'slug' => $post->slug]) }}" 
               wire:navigate class="block overflow-hidden rounded-lg aspect-[16/10] bg-zinc-100">
                @if ($post->featured_image_url)
                    <img src="{{ $post->featured_image_url }}" 
                         alt="{{ $post->title }}" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                         loading="lazy">
                @else
                    <div class="w-full h-full bg-zinc-100 flex items-center justify-center text-zinc-400">
                        <i class="fa-solid fa-image text-3xl"></i>
                    </div>
                @endif
            </a>
        </div>
        
        <div class="w-full sm:w-7/12 flex flex-col justify-between space-y-3">
            <div>
                <div class="flex items-center gap-2 text-[11px] uppercase tracking-wider text-amber-800 font-bold mb-2">
                    <span>{{ $post->category->name ?? 'CERITA' }}</span>
                    <span class="text-zinc-300">•</span>
                    <span class="text-zinc-500 font-medium">{{ max(1, round(str_word_count(strip_tags($post->content ?? '')) / 200)) }} min baca</span>
                </div>
                
                <h3 class="font-serif-title text-xl sm:text-2xl font-bold leading-snug text-zinc-950 group-hover:text-amber-800 transition-colors">
                    <a href="{{ route('posts.show', ['category' => $post->category->slug, 'city' => $post->city->slug, 'slug' => $post->slug]) }}" 
                       wire:navigate>
                        {{ $post->title }}
                    </a>
                </h3>
                
                <p class="text-zinc-600 text-sm leading-relaxed line-clamp-2 mt-2">
                    {{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 130) }}
                </p>
            </div>
            
            <div class="flex items-center justify-between text-xs text-zinc-500 pt-3 border-t border-zinc-100 font-medium">
                <div class="flex items-center gap-1.5 text-zinc-700">
                    <i class="fa-solid fa-location-dot text-amber-800 text-[11px]"></i>
                    <span>{{ $post->city->name ?? 'Indonesia' }}</span>
                </div>
                <a href="{{ route('posts.show', ['category' => $post->category->slug, 'city' => $post->city->slug, 'slug' => $post->slug]) }}" 
                   wire:navigate
                   class="font-semibold text-zinc-900 hover:text-amber-800 flex items-center gap-1.5 transition-colors">
                    Baca Catatan <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>
</article>

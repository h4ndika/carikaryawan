<article class="group pb-10 border-b border-[#e8d9c0] last:border-0 last:pb-0">
    <div class="flex flex-col sm:flex-row gap-6 items-start">
        <div class="w-full sm:w-5/12 flex-shrink-0">
            <a href="{{ route('posts.show', ['category' => $post->category->slug, 'city' => $post->city->slug, 'slug' => $post->slug]) }}" 
               wire:navigate class="block overflow-hidden rounded-xl aspect-[16/10] bg-[#e8d9c0]">
                @if ($post->featured_image_url)
                    <img src="{{ $post->featured_image_url }}" 
                         alt="{{ $post->title }}" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                @else
                    <div class="w-full h-full bg-[#f0e4d2] flex items-center justify-center text-[#8d6e5e]/40">
                        <i class="fa-solid fa-image text-3xl"></i>
                    </div>
                @endif
            </a>
        </div>
        
        <div class="w-full sm:w-7/12 flex flex-col justify-between space-y-3">
            <div>
                <div class="flex items-center gap-2 text-xs uppercase tracking-wider text-[#8d6e5e] font-semibold mb-2">
                    <span>{{ $post->category->name ?? 'CERITA' }}</span>
                    <span>•</span>
                    <span>{{ max(1, round(str_word_count(strip_tags($post->content ?? '')) / 200)) }} min baca</span>
                </div>
                
                <h3 class="text-xl sm:text-2xl heading font-bold leading-snug text-[#2c1810] group-hover:text-[#8d6e5e] transition-colors">
                    <a href="{{ route('posts.show', ['category' => $post->category->slug, 'city' => $post->city->slug, 'slug' => $post->slug]) }}" 
                       wire:navigate>
                        {{ $post->title }}
                    </a>
                </h3>
                
                <p class="text-[#2c1810]/70 text-sm leading-relaxed line-clamp-2 mt-2">
                    {{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 130) }}
                </p>
            </div>
            
            <div class="flex items-center justify-between text-xs text-[#8d6e5e] pt-3 border-t border-[#e8d9c0]/50 font-medium">
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>{{ $post->city->name ?? 'Indonesia' }}</span>
                </div>
                <a href="{{ route('posts.show', ['category' => $post->category->slug, 'city' => $post->city->slug, 'slug' => $post->slug]) }}" 
                   wire:navigate
                   class="font-bold flex items-center gap-1 hover:gap-2 transition-all">
                    Baca Selengkapnya <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>
</article>

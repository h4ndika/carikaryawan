<div class="group">
    <div class="flex flex-col md:flex-row gap-8 md:gap-12">
        <div class="md:w-5/12 flex-shrink-0">
            <a href="{{ route('posts.show', ['category' => $post->category->slug, 'city' => $post->city->slug, 'slug' => $post->slug]) }}" 
               wire:navigate class="block overflow-hidden rounded-lg">
                @if ($post->featured_image_url)
                    <img src="{{ $post->featured_image_url }}" 
                         alt="{{ $post->title }}" 
                         class="w-full aspect-[4/3] object-cover transition-transform group-hover:scale-105 duration-700">
                @else
                    <div class="w-full aspect-[4/3] bg-gradient-to-br from-[#e8d9c0] to-[#d4c4ae] flex items-center justify-center">
                        <i class="fa-solid fa-image text-3xl text-[#8d6e5e]/40"></i>
                    </div>
                @endif
            </a>
        </div>
        
        <div class="md:w-7/12 pt-2 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-x-5 text-xs uppercase tracking-widest text-[#8d6e5e] mb-4">
                    <span class="font-medium">{{ $post->category->name }}</span>
                    <span class="w-1 h-px bg-[#8d6e5e]/30"></span>
                    <span class="text-[#8d6e5e]/70">{{ max(1, round(str_word_count($post->content) / 200)) }} min baca</span>
                </div>
                
                <h3 class="text-3xl heading font-light leading-tight mb-3 group-hover:text-[#8d6e5e] transition-colors">
                    <a href="{{ route('posts.show', ['category' => $post->category->slug, 'city' => $post->city->slug, 'slug' => $post->slug]) }}" 
                       wire:navigate class="no-underline">
                        {{ $post->title }}
                    </a>
                </h3>
                
                <p class="text-[#2c1810]/70 leading-relaxed line-clamp-2">
                    {{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->content), 150) }}
                </p>
            </div>
            
            <div class="mt-6 flex items-center justify-between text-sm">
                <div class="flex items-center gap-x-2 text-[#8d6e5e]">
                    <i class="fa-solid fa-location-dot text-xs"></i>
                    <span>{{ $post->city->name }}</span>
                </div>
                <a href="{{ route('posts.show', ['category' => $post->category->slug, 'city' => $post->city->slug, 'slug' => $post->slug]) }}" 
                   wire:navigate
                   class="font-medium flex items-center gap-x-2 hover:gap-x-3 transition-all text-[#8d6e5e]">
                    Baca selengkapnya 
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</div>

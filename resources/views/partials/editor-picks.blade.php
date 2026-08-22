<div class="space-y-6">
    @if (isset($posts) && $posts->count() > 0)
        @foreach ($posts as $pick)
        <article class="group">
            <a href="{{ route('posts.show', ['category' => $pick->category->slug, 'city' => $pick->city->slug, 'slug' => $pick->slug]) }}" 
               wire:navigate class="block space-y-2">
                <div class="aspect-[16/9] bg-[#e8d9c0] overflow-hidden rounded-xl">
                    @if ($pick->featured_image_url)
                        <img src="{{ $pick->featured_image_url }}" 
                             alt="{{ $pick->title }}" 
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="w-full h-full bg-[#f0e4d2] flex items-center justify-center text-[#8d6e5e]/40">
                            <i class="fa-solid fa-image text-2xl"></i>
                        </div>
                    @endif
                </div>
                <div>
                    <span class="text-[11px] font-bold text-[#8d6e5e] uppercase tracking-wider">
                        {{ $pick->category->name ?? 'CERITA' }}
                    </span>
                    <h4 class="heading font-bold text-sm leading-snug group-hover:text-[#8d6e5e] transition-colors line-clamp-2 text-[#2c1810] mt-0.5">
                        {{ $pick->title }}
                    </h4>
                </div>
            </a>
        </article>
        @endforeach
    @else
        <p class="text-xs text-[#8d6e5e]/60 py-4 text-center">Belum ada cerita pilihan.</p>
    @endif
</div>

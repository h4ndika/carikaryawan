<div class="space-y-10">
    @if ($posts && $posts->count() > 0)
        @foreach ($posts as $pick)
        <div class="group">
            <a href="{{ route('posts.show', ['category' => $pick->category->slug, 'city' => $pick->city->slug, 'slug' => $pick->slug]) }}" 
               wire:navigate class="block">
                <div class="aspect-video bg-[#e8d9c0] mb-4 overflow-hidden rounded-lg">
                    @if ($pick->featured_image_url)
                        <img src="{{ $pick->featured_image_url }}" 
                             alt="{{ $pick->title }}" 
                             class="w-full h-full object-cover transition-transform group-hover:scale-110 duration-500">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-[#e8d9c0] to-[#d4c4ae] flex items-center justify-center">
                            <i class="fa-solid fa-image text-2xl text-[#8d6e5e]/40"></i>
                        </div>
                    @endif
                </div>
                <h4 class="heading font-light text-lg leading-tight mb-1 group-hover:text-[#8d6e5e] transition-colors">
                    {{ $loop->iteration }}. {{ $pick->title }}
                </h4>
                <p class="text-xs text-[#8d6e5e] uppercase tracking-widest">
                    {{ $pick->category->name }} — {{ max(1, round(str_word_count($pick->content) / 200)) }} min baca
                </p>
            </a>
        </div>
        @endforeach
    @else
        <div class="text-center py-8 text-[#8d6e5e]/60">
            <p class="text-sm">Tidak ada artikel tersedia saat ini.</p>
        </div>
    @endif
</div>

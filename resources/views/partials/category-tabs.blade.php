<div class="border-b border-[#e8d9c0] mb-12">
    <div class="flex items-center gap-6 overflow-x-auto pb-3 text-sm font-medium scrollbar-none">
        <a href="{{ route('home') }}" 
           wire:navigate
           class="pb-2 {{ empty($selectedCategory) ? 'border-b-2 border-[#8d6e5e] text-[#2c1810] font-bold' : 'text-[#8d6e5e]/80 hover:text-[#2c1810]' }} whitespace-nowrap transition-colors">
            Semua
        </a>
        @if (isset($categories) && $categories->count() > 0)
            @foreach ($categories as $category)
            <a href="{{ route('home', ['category' => $category->slug]) }}" 
               wire:navigate
               class="pb-2 {{ ($selectedCategory ?? '') === $category->slug ? 'border-b-2 border-[#8d6e5e] text-[#2c1810] font-bold' : 'text-[#8d6e5e]/80 hover:text-[#2c1810]' }} whitespace-nowrap transition-colors font-medium">
                {{ $category->name }}
            </a>
            @endforeach
        @endif
    </div>
</div>

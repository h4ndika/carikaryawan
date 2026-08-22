<div class="border-b border-zinc-200 mb-10">
    <div class="flex items-center gap-6 overflow-x-auto pb-3 text-xs font-semibold uppercase tracking-wider no-scrollbar">
        <a href="{{ route('home') }}" 
           wire:navigate
           class="pb-2 {{ empty($selectedCategory) ? 'border-b-2 border-zinc-950 text-zinc-950 font-bold' : 'text-zinc-500 hover:text-zinc-900' }} whitespace-nowrap transition-colors">
            Semua Cerita
        </a>
        @if (isset($categories) && $categories->count() > 0)
            @foreach ($categories as $category)
            <a href="{{ route('home', ['category' => $category->slug]) }}" 
               wire:navigate
               class="pb-2 {{ ($selectedCategory ?? '') === $category->slug ? 'border-b-2 border-zinc-950 text-zinc-950 font-bold' : 'text-zinc-500 hover:text-zinc-900' }} whitespace-nowrap transition-colors">
                {{ $category->name }}
            </a>
            @endforeach
        @endif
    </div>
</div>

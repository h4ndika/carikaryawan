<div class="border-b border-zinc-200 dark:border-zinc-800 mb-10">
    <div class="flex items-center gap-6 overflow-x-auto pb-3 text-xs font-semibold uppercase tracking-wider no-scrollbar">
        <a href="{{ route('home') }}" 
           wire:navigate
           class="pb-2 {{ empty($selectedCategory) ? 'border-b-2 border-zinc-950 dark:border-zinc-100 text-zinc-950 dark:text-zinc-50 font-bold' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100' }} whitespace-nowrap transition-colors">
            Semua Lowongan
        </a>
        @if (isset($categories) && $categories->count() > 0)
            @foreach ($categories as $category)
            <a href="{{ $category->url() }}" 
               wire:navigate
               class="pb-2 {{ ($selectedCategory ?? '') === $category->slug ? 'border-b-2 border-zinc-950 dark:border-zinc-100 text-zinc-950 dark:text-zinc-50 font-bold' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100' }} whitespace-nowrap transition-colors">
                {{ $category->name }}
            </a>
            @endforeach
        @endif
    </div>
</div>

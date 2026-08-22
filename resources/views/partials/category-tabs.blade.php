<div class="border-b border-[#e8d9c0] mb-12">
    <div class="flex gap-x-8 px-6 sm:px-8 text-sm font-medium overflow-x-auto pb-px">
        <button onclick="document.querySelectorAll('[data-category-tab]').forEach(b => b.classList.remove('border-b-2', 'border-[#8d6e5e]')); this.classList.add('border-b-2', 'border-[#8d6e5e]')" 
                data-category-tab
                class="pb-4 border-b-2 border-[#8d6e5e] whitespace-nowrap transition-colors">
                Semua
        </button>
        @if ($categories && $categories->count() > 0)
            @foreach ($categories as $category)
            <button onclick="document.querySelectorAll('[data-category-tab]').forEach(b => b.classList.remove('border-b-2', 'border-[#8d6e5e]')); this.classList.add('border-b-2', 'border-[#8d6e5e]')" 
                    data-category-tab
                    class="pb-4 text-[#8d6e5e]/60 hover:text-[#8d6e5e] whitespace-nowrap transition-colors">
                    {{ $category->name }}
            </button>
            @endforeach
        @endif
    </div>
</div>

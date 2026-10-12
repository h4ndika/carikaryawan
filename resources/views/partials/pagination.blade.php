@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="border-t border-zinc-200/80 dark:border-zinc-800 pt-8 pb-6 flex flex-col sm:flex-row items-center justify-between gap-5">
        {{-- Status Counter (Menampilkan X - Y dari Z lowongan) --}}
        <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium order-2 sm:order-1 flex items-center gap-1.5">
            <span>Menampilkan</span>
            <span class="font-bold text-zinc-900 dark:text-zinc-100 bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded text-xs">{{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }}</span>
            <span>dari</span>
            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $paginator->total() }}</span>
            <span>lowongan</span>
        </div>

        {{-- Page Numbers & Nav Controls --}}
        <div class="flex items-center gap-1 sm:gap-1.5 order-1 sm:order-2">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-zinc-300 dark:text-zinc-700 select-none cursor-not-allowed">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </span>
            @else
                <button type="button" 
                        wire:click="previousPage('{{ $paginator->getPageName() }}')" 
                        onclick="const target = document.getElementById('posts-feed') || document.getElementById('main-content') || document.querySelector('main'); if(target){ target.scrollIntoView({ behavior: 'smooth', block: 'start' }); } else { window.scrollTo({ top: 0, behavior: 'smooth' }); }"
                        wire:loading.attr="disabled"
                        style="cursor: pointer !important;"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-amber-800 dark:hover:text-amber-400 hover:bg-zinc-100/80 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer select-none"
                        aria-label="Halaman Sebelumnya">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </button>
            @endif

            {{-- Separator line between prev and numbers on mobile --}}
            <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-800 mx-1 hidden sm:block"></div>

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-zinc-400 dark:text-zinc-600 select-none">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-zinc-950 dark:bg-zinc-100 text-white dark:text-zinc-950 font-bold text-xs sm:text-sm shadow-2xs select-none">
                                {{ $page }}
                            </span>
                        @else
                            <button type="button" 
                                    wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                    onclick="const target = document.getElementById('posts-feed') || document.getElementById('main-content') || document.querySelector('main'); if(target){ target.scrollIntoView({ behavior: 'smooth', block: 'start' }); } else { window.scrollTo({ top: 0, behavior: 'smooth' }); }"
                                    wire:loading.attr="disabled"
                                    style="cursor: pointer !important;"
                                    class="inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-full text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 font-semibold text-xs sm:text-sm transition-colors cursor-pointer select-none">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Separator line between numbers and next on mobile --}}
            <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-800 mx-1 hidden sm:block"></div>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button type="button" 
                        wire:click="nextPage('{{ $paginator->getPageName() }}')" 
                        onclick="const target = document.getElementById('posts-feed') || document.getElementById('main-content') || document.querySelector('main'); if(target){ target.scrollIntoView({ behavior: 'smooth', block: 'start' }); } else { window.scrollTo({ top: 0, behavior: 'smooth' }); }"
                        wire:loading.attr="disabled"
                        style="cursor: pointer !important;"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-amber-800 dark:hover:text-amber-400 hover:bg-zinc-100/80 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer select-none"
                        aria-label="Halaman Berikutnya">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-zinc-300 dark:text-zinc-700 select-none cursor-not-allowed">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
            @endif
        </div>
    </nav>
@endif

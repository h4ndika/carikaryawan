@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-6">
        {{-- Status Counter (Mobile / Desktop) --}}
        <div class="text-sm text-zinc-600 font-medium order-2 sm:order-1">
            Menampilkan <span class="font-bold text-zinc-950">{{ $paginator->firstItem() ?? 0 }}</span> - <span class="font-bold text-zinc-950">{{ $paginator->lastItem() ?? 0 }}</span> dari <span class="font-bold text-zinc-950">{{ $paginator->total() }}</span> tempat
        </div>

        {{-- Page Numbers & Nav Controls --}}
        <div class="flex items-center gap-2 order-1 sm:order-2">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-zinc-200 bg-zinc-50 text-zinc-300 cursor-not-allowed">
                    <i class="fa-solid fa-chevron-left text-sm"></i>
                </span>
            @else
                <button type="button" 
                        wire:click="previousPage('{{ $paginator->getPageName() }}')" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-900 hover:text-white hover:border-zinc-900 transition-colors shadow-2xs cursor-pointer"
                        aria-label="Halaman Sebelumnya">
                    <i class="fa-solid fa-chevron-left text-sm"></i>
                </button>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center w-10 h-10 text-sm font-bold text-zinc-400">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-zinc-950 text-white font-bold text-sm shadow-xs">
                                {{ $page }}
                            </span>
                        @else
                            <button type="button" 
                                    wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-100 hover:border-zinc-300 font-semibold text-sm transition-colors shadow-2xs cursor-pointer">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button type="button" 
                        wire:click="nextPage('{{ $paginator->getPageName() }}')" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-900 hover:text-white hover:border-zinc-900 transition-colors shadow-2xs cursor-pointer"
                        aria-label="Halaman Berikutnya">
                    <i class="fa-solid fa-chevron-right text-sm"></i>
                </button>
            @else
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-zinc-200 bg-zinc-50 text-zinc-300 cursor-not-allowed">
                    <i class="fa-solid fa-chevron-right text-sm"></i>
                </span>
            @endif
        </div>
    </nav>
@endif

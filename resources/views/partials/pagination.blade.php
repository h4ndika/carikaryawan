@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-4">
        {{-- Status Counter (Mobile / Desktop) --}}
        <div class="text-xs text-zinc-500 font-medium order-2 sm:order-1">
            Menampilkan <span class="font-bold text-zinc-900">{{ $paginator->firstItem() ?? 0 }}</span> - <span class="font-bold text-zinc-900">{{ $paginator->lastItem() ?? 0 }}</span> dari <span class="font-bold text-zinc-900">{{ $paginator->total() }}</span> tempat
        </div>

        {{-- Page Numbers & Nav Controls --}}
        <div class="flex items-center gap-1.5 order-1 sm:order-2">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-zinc-200 bg-zinc-50 text-zinc-300 cursor-not-allowed">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </span>
            @else
                <button type="button" 
                        wire:click="previousPage('{{ $paginator->getPageName() }}')" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-900 hover:text-white hover:border-zinc-900 transition-colors shadow-2xs"
                        aria-label="Halaman Sebelumnya">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center w-9 h-9 text-xs font-bold text-zinc-400">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-zinc-950 text-white font-bold text-xs shadow-xs">
                                {{ $page }}
                            </span>
                        @else
                            <button type="button" 
                                    wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-100 hover:border-zinc-300 font-semibold text-xs transition-colors shadow-2xs">
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
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-900 hover:text-white hover:border-zinc-900 transition-colors shadow-2xs"
                        aria-label="Halaman Berikutnya">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
            @else
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-zinc-200 bg-zinc-50 text-zinc-300 cursor-not-allowed">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </span>
            @endif
        </div>
    </nav>
@endif

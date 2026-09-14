@if (!empty($drafts))
    <div class="mt-6 pt-5 border-t border-gray-200 dark:border-gray-700/80 space-y-4">
        <div class="flex items-center justify-between pb-1">
            <div class="flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-500/10 text-amber-500 dark:bg-amber-400/15 dark:text-amber-400 text-sm">
                    ✨
                </span>
                <span class="text-sm font-bold text-gray-900 dark:text-gray-100 tracking-tight">
                    Rekomendasi Draft Balasan AI
                </span>
            </div>
            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                Klik tombol untuk menyalin teks
            </span>
        </div>

        <div class="grid grid-cols-1 gap-3.5">
            @foreach ($drafts as $index => $draft)
                <div 
                    x-data="{ copied: false }" 
                    class="group relative rounded-xl border border-gray-200 bg-white p-4 shadow-xs transition-all hover:border-amber-400/80 hover:shadow-md dark:border-gray-700/70 dark:bg-gray-800/90 dark:hover:border-amber-500/80"
                >
                    <div class="flex items-center justify-between gap-3 mb-2.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200/80 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Opsi {{ $index + 1 }}
                            </span>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                {{ $draft['style'] ?? 'Draft' }}
                            </span>
                        </div>

                        <button 
                            type="button"
                            x-on:click="navigator.clipboard.writeText(@js($draft['text'] ?? '')); copied = true; setTimeout(() => copied = false, 2000)"
                            :class="copied ? 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-700' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-amber-50 hover:text-amber-800 hover:border-amber-300 dark:bg-gray-700/80 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-amber-500/70'"
                            class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-lg border transition-all cursor-pointer shadow-2xs select-none active:scale-95"
                        >
                            <span x-show="!copied" class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-amber-600 dark:group-hover:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span>Salin</span>
                            </span>
                            <span x-show="copied" x-cloak class="flex items-center gap-1 font-bold">
                                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Tersalin!</span>
                            </span>
                        </button>
                    </div>

                    <div class="rounded-lg bg-gray-50/90 p-3 border border-gray-100 dark:bg-gray-900/60 dark:border-gray-800/80">
                        <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-wrap select-all font-sans antialiased">
                            {{ $draft['text'] ?? '' }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

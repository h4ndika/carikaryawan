@if (!empty($drafts))
    <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                <span>✨</span> Rekomendasi Draft Balasan AI
            </h4>
            <span class="text-xs text-gray-500 dark:text-gray-400">Klik tombol salin di setiap opsi</span>
        </div>

        @foreach ($drafts as $index => $draft)
            <div 
                x-data="{ copied: false }" 
                class="p-3.5 bg-gray-50 dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700 transition hover:border-amber-400 dark:hover:border-amber-500"
            >
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                        Opsi {{ $index + 1 }}: {{ $draft['style'] ?? 'Draft' }}
                    </span>
                    <button 
                        type="button"
                        x-on:click="navigator.clipboard.writeText(@js($draft['text'] ?? '')); copied = true; setTimeout(() => copied = false, 2000)"
                        class="text-xs font-medium px-2.5 py-1 rounded-md bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 inline-flex items-center gap-1 transition"
                    >
                        <span x-show="!copied">📋 Salin Teks</span>
                        <span x-show="copied" x-cloak class="text-green-600 dark:text-green-400 font-semibold">✓ Tersalin!</span>
                    </button>
                </div>
                <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-wrap select-all font-sans">{{ $draft['text'] ?? '' }}</p>
            </div>
        @endforeach
    </div>
@endif

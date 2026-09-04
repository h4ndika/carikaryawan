<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Sidebar Panel: Konfigurasi Model & Instruksi --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-center gap-2 pb-4 mb-4 border-b border-gray-200 dark:border-white/10">
                    <x-heroicon-o-adjustments-horizontal class="w-5 h-5 text-amber-500" />
                    <h3 class="font-semibold text-gray-900 dark:text-white text-base">Konfigurasi Model</h3>
                </div>

                <div class="space-y-4 text-sm">
                    {{-- Provider Selection --}}
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">AI Provider</label>
                        <select wire:model.live="provider" class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-white/10 dark:bg-gray-800 dark:text-white">
                            <option value="openai">OpenAI / 9router (Default)</option>
                            <option value="gemini">Google Gemini</option>
                            <option value="anthropic">Anthropic (Claude)</option>
                            <option value="groq">Groq</option>
                            <option value="deepseek">DeepSeek</option>
                            <option value="ollama">Ollama (Local)</option>
                            <option value="openrouter">OpenRouter</option>
                        </select>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Menggunakan engine resmi Laravel AI SDK.</p>
                    </div>

                    {{-- Model Name Input --}}
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Model</label>
                        <input type="text" wire:model.live="model" placeholder="Contoh: Content-blog, gpt-4o, gemini-2.5-flash"
                            class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-white/10 dark:bg-gray-800 dark:text-white" />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sesuai model yang aktif pada endpoint provider.</p>
                    </div>

                    {{-- Quick Template --}}
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Template Instruksi Cepat</label>
                        <select wire:model.live="template" class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-white/10 dark:bg-gray-800 dark:text-white">
                            <option value="">-- Pilih Template --</option>
                            <option value="article">Artikel Ulasan & Wisata (SEO)</option>
                            <option value="seo">Meta Title & Description Generator</option>
                            <option value="summary">Ringkasan Tempat & Highlight</option>
                            <option value="general">Asisten Umum DiSekitar</option>
                        </select>
                    </div>

                    {{-- System Instructions --}}
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">System Prompt / Instruksi</label>
                        <textarea wire:model.defer="systemPrompt" rows="4"
                            class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-white/10 dark:bg-gray-800 dark:text-white font-mono text-xs"></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Aturan karakter atau kepribadian agen AI.</p>
                    </div>

                    <div class="pt-2">
                        <button type="button" wire:click="clearMessages"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-400 dark:hover:bg-red-900/50 transition">
                            <x-heroicon-o-trash class="w-4 h-4" />
                            Bersihkan Riwayat Chat
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Chat Area --}}
        <div class="lg:col-span-8 flex flex-col space-y-4">
            {{-- Message Feed --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900 min-h-[420px] max-h-[620px] overflow-y-auto flex flex-col space-y-4">
                @if (empty($messages))
                    <div class="flex-1 flex flex-col items-center justify-center text-center p-8 text-gray-500 dark:text-gray-400">
                        <div class="w-16 h-16 rounded-full bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-amber-500 mb-3">
                            <x-heroicon-o-sparkles class="w-8 h-8" />
                        </div>
                        <h4 class="font-semibold text-gray-900 dark:text-white text-base">AI Playground Siap</h4>
                        <p class="text-sm mt-1 max-w-sm">Tulis prompt di bawah atau pilih template untuk menguji performa dan respons model AI dengan Laravel AI SDK.</p>
                    </div>
                @else
                    @foreach ($messages as $index => $msg)
                        @if ($msg['role'] === 'user')
                            {{-- User message --}}
                            <div class="flex justify-end">
                                <div class="max-w-[85%] rounded-2xl rounded-tr-sm bg-amber-500 text-white px-4 py-3 text-sm shadow-sm">
                                    <div class="flex items-center justify-between gap-4 mb-1 text-xs text-amber-100 font-medium">
                                        <span>User</span>
                                        <span>{{ $msg['timestamp'] ?? '' }}</span>
                                    </div>
                                    <div class="whitespace-pre-wrap leading-relaxed">{{ $msg['content'] }}</div>
                                </div>
                            </div>
                        @else
                            {{-- Assistant message --}}
                            <div class="flex justify-start">
                                <div class="max-w-[90%] rounded-2xl rounded-tl-sm {{ !empty($msg['is_error']) ? 'bg-red-50 border border-red-200 text-red-900 dark:bg-red-950/40 dark:border-red-800 dark:text-red-200' : 'bg-gray-50 border border-gray-200 text-gray-900 dark:bg-gray-800/80 dark:border-white/10 dark:text-gray-100' }} p-4 text-sm shadow-sm">
                                    <div class="flex items-center justify-between gap-4 mb-2 pb-2 border-b border-gray-200/60 dark:border-white/10 text-xs text-gray-500 dark:text-gray-400">
                                        <div class="flex items-center gap-1.5 font-medium">
                                            <x-heroicon-s-sparkles class="w-4 h-4 {{ !empty($msg['is_error']) ? 'text-red-500' : 'text-amber-500' }}" />
                                            <span>AI Assistant</span>
                                            @if (!empty($msg['model']))
                                                <span class="px-1.5 py-0.5 rounded bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200 text-[11px] font-mono">
                                                    {{ $msg['model'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-3">
                                            @if (!empty($msg['duration']))
                                                <span title="Waktu respons">⚡ {{ $msg['duration'] }}</span>
                                            @endif
                                            <span>{{ $msg['timestamp'] ?? '' }}</span>
                                        </div>
                                    </div>

                                    <div class="whitespace-pre-wrap leading-relaxed select-text">{{ $msg['content'] }}</div>

                                    @if (!empty($msg['tokens']))
                                        <div class="mt-3 pt-2 border-t border-gray-200/60 dark:border-white/10 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                            <div class="flex items-center gap-3 font-mono text-[11px]">
                                                <span>Tokens: {{ $msg['tokens']['total'] ?? 0 }} (In: {{ $msg['tokens']['prompt'] ?? 0 }}, Out: {{ $msg['tokens']['completion'] ?? 0 }})</span>
                                            </div>
                                            <button type="button"
                                                x-data="{ copied: false }"
                                                x-on:click="navigator.clipboard.writeText(@js($msg['content'])); copied = true; setTimeout(() => copied = false, 2000)"
                                                class="inline-flex items-center gap-1 text-gray-600 hover:text-amber-600 dark:text-gray-300 dark:hover:text-amber-400 transition">
                                                <x-heroicon-o-clipboard-document class="w-3.5 h-3.5" />
                                                <span x-text="copied ? 'Disalin!' : 'Salin'">Salin</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            {{-- Input Area --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <form wire:submit="sendMessage" class="space-y-3">
                    <div>
                        <textarea
                            wire:model="userPrompt"
                            rows="3"
                            placeholder="Tulis prompt atau pertanyaan di sini... (Klik tombol 'Kirim Prompt' untuk menjalankan)"
                            class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-white/10 dark:bg-gray-800 dark:text-white"
                            required></textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Didukung resmi oleh <strong>Laravel AI SDK</strong>.
                        </div>

                        <button type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-lg text-sm transition shadow-sm disabled:opacity-50">
                            <span wire:loading.remove wire:target="sendMessage" class="inline-flex items-center gap-2">
                                <x-heroicon-m-paper-airplane class="w-4 h-4" />
                                Kirim Prompt
                            </span>
                            <span wire:loading wire:target="sendMessage" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Memproses AI...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>

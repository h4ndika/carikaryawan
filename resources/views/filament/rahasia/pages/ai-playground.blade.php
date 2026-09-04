<x-filament-panels::page>
    <style>
        .ai-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 1024px) {
            .ai-container {
                grid-template-columns: 360px 1fr;
            }
        }
        .ai-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .dark .ai-card {
            background: #111827;
            border-color: rgba(255, 255, 255, 0.1);
        }
        .ai-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.35rem;
        }
        .dark .ai-label {
            color: #d1d5db;
        }
        .ai-hint {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.3rem;
        }
        .dark .ai-hint {
            color: #9ca3af;
        }
        .ai-input, .ai-select, .ai-textarea {
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #ffffff;
            color: #111827;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            box-sizing: border-box;
        }
        .dark .ai-input, .dark .ai-select, .dark .ai-textarea {
            border-color: rgba(255, 255, 255, 0.15);
            background-color: #1f2937;
            color: #f9fafb;
        }
        .ai-input:focus, .ai-select:focus, .ai-textarea:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
        }
        .ai-chat-feed {
            min-height: 440px;
            max-height: 600px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 1rem;
        }
        .ai-bubble-user {
            align-self: flex-end;
            max-width: 85%;
            background-color: #f59e0b;
            color: #ffffff;
            border-radius: 1rem 1rem 0.25rem 1rem;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            line-height: 1.5;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .ai-bubble-assistant {
            align-self: flex-start;
            max-width: 90%;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            color: #111827;
            border-radius: 1rem 1rem 1rem 0.25rem;
            padding: 1rem;
            font-size: 0.875rem;
            line-height: 1.6;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .dark .ai-bubble-assistant {
            background-color: #1f2937;
            border-color: rgba(255, 255, 255, 0.1);
            color: #f3f4f6;
        }
        .ai-bubble-error {
            background-color: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }
        .dark .ai-bubble-error {
            background-color: rgba(127, 29, 29, 0.3);
            border-color: #991b1b;
            color: #fca5a5;
        }
        .ai-icon-sm {
            width: 18px !important;
            height: 18px !important;
            min-width: 18px !important;
            min-height: 18px !important;
            display: inline-block;
            vertical-align: middle;
        }
        .ai-icon-md {
            width: 24px !important;
            height: 24px !important;
            min-width: 24px !important;
            min-height: 24px !important;
            display: inline-block;
        }
        .ai-icon-lg {
            width: 48px !important;
            height: 48px !important;
            min-width: 48px !important;
            min-height: 48px !important;
            display: inline-block;
        }
        .ai-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #f59e0b;
            color: #ffffff;
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.6rem 1.25rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .ai-btn-primary:hover {
            background-color: #d97706;
        }
        .ai-btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .ai-btn-danger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            background-color: #fee2e2;
            color: #dc2626;
            font-weight: 500;
            font-size: 0.8125rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .dark .ai-btn-danger {
            background-color: rgba(153, 27, 27, 0.3);
            color: #f87171;
        }
        .ai-btn-danger:hover {
            background-color: #fecaca;
        }
        .dark .ai-btn-danger:hover {
            background-color: rgba(153, 27, 27, 0.5);
        }
        .ai-btn-copy {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            color: #6b7280;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
        }
        .dark .ai-btn-copy {
            color: #9ca3af;
        }
        .ai-btn-copy:hover {
            color: #f59e0b;
            background-color: rgba(245, 158, 11, 0.1);
        }
        .ai-spin {
            animation: ai-spin-anim 1s linear infinite;
        }
        @keyframes ai-spin-anim {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

    <div class="ai-container">
        {{-- Sidebar Panel: Konfigurasi Model & Instruksi --}}
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div class="ai-card">
                <div style="display: flex; align-items: center; gap: 0.5rem; padding-bottom: 0.75rem; margin-bottom: 1rem; border-bottom: 1px solid #e5e7eb;">
                    <div style="color: #f59e0b;">
                        <x-heroicon-o-adjustments-horizontal class="ai-icon-md" />
                    </div>
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 600; color: inherit;">Konfigurasi Model</h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    {{-- Provider Selection --}}
                    <div>
                        <label class="ai-label">AI Provider</label>
                        <select wire:model.live="provider" class="ai-select">
                            <option value="openai-compatible">9router / OpenAI Compatible (Default)</option>
                            <option value="openai">OpenAI Official (Responses API)</option>
                            <option value="gemini">Google Gemini</option>
                            <option value="anthropic">Anthropic (Claude)</option>
                            <option value="groq">Groq</option>
                            <option value="deepseek">DeepSeek</option>
                            <option value="ollama">Ollama (Local)</option>
                            <option value="openrouter">OpenRouter</option>
                        </select>
                        <div class="ai-hint">Engine resmi Laravel AI SDK.</div>
                    </div>

                    {{-- Model Name Input --}}
                    <div>
                        <label class="ai-label">Nama Model</label>
                        <input type="text" wire:model.live="model" placeholder="Contoh: Content-blog, gpt-4o, gemini-2.5-flash" class="ai-input" />
                        <div class="ai-hint">Model pada endpoint provider.</div>
                    </div>

                    {{-- Quick Template --}}
                    <div>
                        <label class="ai-label">Template Instruksi Cepat</label>
                        <select wire:model.live="template" class="ai-select">
                            <option value="">-- Pilih Template --</option>
                            <option value="article">Artikel Ulasan & Wisata (SEO)</option>
                            <option value="seo">Meta Title & Description Generator</option>
                            <option value="summary">Ringkasan Tempat & Highlight</option>
                            <option value="general">Asisten Umum DiSekitar</option>
                        </select>
                    </div>

                    {{-- System Instructions --}}
                    <div>
                        <label class="ai-label">System Prompt / Instruksi</label>
                        <textarea wire:model.defer="systemPrompt" rows="4" class="ai-textarea" style="font-family: monospace; font-size: 0.75rem;"></textarea>
                        <div class="ai-hint">Karakter atau aturan prompt model.</div>
                    </div>

                    <div style="padding-top: 0.5rem;">
                        <button type="button" wire:click="clearMessages" class="ai-btn-danger">
                            <x-heroicon-o-trash class="ai-icon-sm" />
                            Bersihkan Riwayat Chat
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Chat Area --}}
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            {{-- Message Feed --}}
            <div class="ai-card" style="padding: 0;">
                <div class="ai-chat-feed">
                    @if (empty($messages))
                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 2.5rem 1rem; color: #6b7280;">
                            <div style="width: 64px; height: 64px; border-radius: 9999px; background-color: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: #f59e0b; margin-bottom: 1rem;">
                                <x-heroicon-o-sparkles class="ai-icon-lg" />
                            </div>
                            <h4 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: 600; color: inherit;">AI Playground Siap</h4>
                            <p style="margin: 0; font-size: 0.875rem; max-width: 380px;">Tulis pertanyaan atau prompt di bawah untuk menguji respon model AI melalui Laravel AI SDK.</p>
                        </div>
                    @else
                        @foreach ($messages as $index => $msg)
                            @if ($msg['role'] === 'user')
                                {{-- User message --}}
                                <div style="display: flex; justify-content: flex-end;">
                                    <div class="ai-bubble-user">
                                        <div style="display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 0.35rem; font-size: 0.75rem; opacity: 0.9;">
                                            <span style="font-weight: 600;">User</span>
                                            <span>{{ $msg['timestamp'] ?? '' }}</span>
                                        </div>
                                        <div style="white-space: pre-wrap; word-break: break-word;">{{ $msg['content'] }}</div>
                                    </div>
                                </div>
                            @else
                                {{-- Assistant message --}}
                                <div style="display: flex; justify-content: flex-start;">
                                    <div class="ai-bubble-assistant {{ !empty($msg['is_error']) ? 'ai-bubble-error' : '' }}">
                                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 0.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(156, 163, 175, 0.2); font-size: 0.75rem; color: #6b7280;">
                                            <div style="display: flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                                                <x-heroicon-s-sparkles class="ai-icon-sm" style="color: {{ !empty($msg['is_error']) ? '#ef4444' : '#f59e0b' }};" />
                                                <span>AI Assistant</span>
                                                @if (!empty($msg['model']))
                                                    <span style="padding: 0.15rem 0.4rem; border-radius: 0.25rem; background: rgba(156, 163, 175, 0.2); font-family: monospace; font-size: 0.7rem;">
                                                        {{ $msg['model'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                @if (!empty($msg['duration']))
                                                    <span>⚡ {{ $msg['duration'] }}</span>
                                                @endif
                                                <span>{{ $msg['timestamp'] ?? '' }}</span>
                                            </div>
                                        </div>

                                        <div style="white-space: pre-wrap; word-break: break-word;">{{ $msg['content'] }}</div>

                                        @if (!empty($msg['tokens']))
                                            <div style="margin-top: 0.75rem; padding-top: 0.5rem; border-top: 1px solid rgba(156, 163, 175, 0.2); display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem;">
                                                <div style="font-family: monospace; font-size: 0.7rem; color: #6b7280;">
                                                    Tokens: {{ $msg['tokens']['total'] ?? 0 }} (In: {{ $msg['tokens']['prompt'] ?? 0 }}, Out: {{ $msg['tokens']['completion'] ?? 0 }})
                                                </div>
                                                <button type="button"
                                                    x-data="{ copied: false }"
                                                    x-on:click="navigator.clipboard.writeText(@js($msg['content'])); copied = true; setTimeout(() => copied = false, 2000)"
                                                    class="ai-btn-copy">
                                                    <x-heroicon-o-clipboard-document class="ai-icon-sm" />
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
            </div>

            {{-- Input Area --}}
            <div class="ai-card">
                <form wire:submit="sendMessage" style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div>
                        <textarea
                            wire:model="userPrompt"
                            rows="3"
                            placeholder="Tulis prompt atau pertanyaan di sini... Tekan tombol 'Kirim Prompt'"
                            class="ai-textarea"
                            required></textarea>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="ai-hint" style="margin: 0;">
                            Didukung resmi oleh <strong>Laravel AI SDK</strong>.
                        </div>

                        <button type="submit"
                            wire:loading.attr="disabled"
                            class="ai-btn-primary">
                            <span wire:loading.remove wire:target="sendMessage" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                <x-heroicon-m-paper-airplane class="ai-icon-sm" />
                                Kirim Prompt
                            </span>
                            <span wire:loading wire:target="sendMessage" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                <svg class="ai-icon-sm ai-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
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

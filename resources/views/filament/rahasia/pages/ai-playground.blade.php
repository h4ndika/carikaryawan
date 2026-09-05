<x-filament-panels::page>
    <style>
        .ai-container {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        @media (max-width: 1024px) {
            .ai-container {
                grid-template-columns: 1fr;
            }
        }
        .ai-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .dark .ai-card {
            background-color: #111827;
            border-color: #1f2937;
        }
        .ai-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.375rem;
        }
        .dark .ai-label {
            color: #d1d5db;
        }
        .ai-hint {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        .dark .ai-hint {
            color: #9ca3af;
        }
        .ai-input, .ai-select, .ai-textarea {
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            background-color: #ffffff;
            color: #111827;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .dark .ai-input, .dark .ai-select, .dark .ai-textarea {
            background-color: #1f2937;
            border-color: #374151;
            color: #f9fafb;
        }
        .ai-input:focus, .ai-select:focus, .ai-textarea:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 1px #f59e0b;
        }
        .ai-chat-feed {
            height: 540px;
            overflow-y: auto;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            scroll-behavior: smooth;
        }
        .ai-bubble-user {
            max-width: 82%;
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            border-radius: 1rem 1rem 0.25rem 1rem;
            padding: 0.875rem 1.125rem;
            font-size: 0.875rem;
            line-height: 1.5;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .dark .ai-bubble-user {
            background-color: rgba(245, 158, 11, 0.15);
            color: #fef3c7;
            border-color: rgba(245, 158, 11, 0.3);
        }
        .ai-bubble-assistant {
            max-width: 90%;
            background-color: #f9fafb;
            color: #1f2937;
            border: 1px solid #e5e7eb;
            border-radius: 1rem 1rem 1rem 0.25rem;
            padding: 1rem 1.25rem;
            font-size: 0.875rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .dark .ai-bubble-assistant {
            background-color: #1a2234;
            color: #e2e8f0;
            border-color: #2b354f;
        }
        .ai-bubble-thinking {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(245, 158, 11, 0.12) 100%);
            border: 1px dashed rgba(245, 158, 11, 0.4);
            border-radius: 1rem;
        }
        .dark .ai-bubble-thinking {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(245, 158, 11, 0.18) 100%);
            border-color: rgba(245, 158, 11, 0.5);
        }
        .ai-bubble-error {
            background-color: #fef2f2 !important;
            border-color: #fecaca !important;
            color: #991b1b !important;
        }
        .dark .ai-bubble-error {
            background-color: rgba(239, 68, 68, 0.1) !important;
            border-color: rgba(239, 68, 68, 0.3) !important;
            color: #fca5a5 !important;
        }
        /* Markdown Typography */
        .ai-markdown {
            line-height: 1.65;
            word-break: break-word;
        }
        .ai-markdown p {
            margin-top: 0.35rem;
            margin-bottom: 0.55rem;
        }
        .ai-markdown p:first-child {
            margin-top: 0;
        }
        .ai-markdown p:last-child {
            margin-bottom: 0;
        }
        .ai-markdown strong, .ai-markdown b {
            font-weight: 700;
            color: #111827;
        }
        .dark .ai-markdown strong, .dark .ai-markdown b {
            color: #ffffff;
        }
        .ai-markdown ul, .ai-markdown ol {
            margin: 0.5rem 0;
            padding-left: 1.5rem;
        }
        .ai-markdown ol {
            list-style-type: decimal;
        }
        .ai-markdown ul {
            list-style-type: disc;
        }
        .ai-markdown li {
            margin-bottom: 0.25rem;
        }
        .ai-markdown table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.75rem 0;
            font-size: 0.8125rem;
        }
        .ai-markdown th, .ai-markdown td {
            border: 1px solid rgba(156, 163, 175, 0.3);
            padding: 0.45rem 0.7rem;
            text-align: left;
        }
        .ai-markdown th {
            background: rgba(156, 163, 175, 0.12);
            font-weight: 600;
        }
        .ai-markdown code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85em;
            padding: 0.15rem 0.35rem;
            border-radius: 0.25rem;
            background: rgba(156, 163, 175, 0.15);
        }
        .ai-markdown pre {
            margin: 0.6rem 0;
            padding: 0.75rem;
            border-radius: 0.375rem;
            background: #0f172a;
            color: #f8fafc;
            overflow-x: auto;
        }
        .ai-markdown pre code {
            background: transparent;
            padding: 0;
            color: inherit;
        }
        .ai-markdown blockquote {
            border-left: 3px solid #f59e0b;
            padding-left: 0.75rem;
            margin: 0.5rem 0;
            color: #6b7280;
            font-style: italic;
        }
        .dark .ai-markdown blockquote {
            color: #9ca3af;
        }
        /* Buttons & Badges */
        .ai-icon-sm {
            width: 1rem;
            height: 1rem;
            display: inline-block;
        }
        .ai-icon-md {
            width: 1.25rem;
            height: 1.25rem;
            display: inline-block;
        }
        .ai-icon-lg {
            width: 2rem;
            height: 2rem;
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
        .ai-btn-primary:hover:not(:disabled) {
            background-color: #d97706;
        }
        .ai-btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .ai-btn-new-chat {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            width: 100%;
            background-color: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px dashed rgba(245, 158, 11, 0.4);
            font-weight: 600;
            font-size: 0.8125rem;
            padding: 0.55rem 0.75rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .ai-btn-new-chat:hover {
            background-color: rgba(245, 158, 11, 0.2);
            border-color: #f59e0b;
            color: #b45309;
        }
        .dark .ai-btn-new-chat {
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.35);
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
        .ai-chip {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.25);
            color: #d97706;
            font-size: 0.725rem;
            font-weight: 500;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .ai-chip:hover {
            background: rgba(245, 158, 11, 0.2);
            border-color: #f59e0b;
        }
        .dark .ai-chip {
            color: #fbbf24;
            background: rgba(245, 158, 11, 0.15);
        }
        .ai-spin {
            animation: ai-spin-anim 1s linear infinite;
        }
        @keyframes ai-spin-anim {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        /* Bouncing typing indicator */
        .ai-typing-dots {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .ai-typing-dots span {
            width: 6px;
            height: 6px;
            background-color: #f59e0b;
            border-radius: 50%;
            display: inline-block;
            animation: ai-dot-bounce 1.4s infinite ease-in-out both;
        }
        .ai-typing-dots span:nth-child(1) { animation-delay: -0.32s; }
        .ai-typing-dots span:nth-child(2) { animation-delay: -0.16s; }
        @keyframes ai-dot-bounce {
            0%, 80%, 100% { transform: scale(0); opacity: 0.4; }
            40% { transform: scale(1); opacity: 1; }
        }
        /* Session List */
        .ai-session-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.5rem 0.65rem;
            border-radius: 0.375rem;
            font-size: 0.8125rem;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }
        .ai-session-item:hover {
            background-color: rgba(156, 163, 175, 0.1);
        }
        .ai-session-item.active {
            background-color: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.35);
            font-weight: 600;
        }
        .ai-session-item .del-btn {
            opacity: 0;
            transition: opacity 0.15s ease;
            color: #9ca3af;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0.2rem;
            border-radius: 0.25rem;
        }
        .ai-session-item:hover .del-btn {
            opacity: 1;
        }
        .ai-session-item .del-btn:hover {
            color: #ef4444;
            background-color: rgba(239, 68, 68, 0.1);
        }
    </style>

    <div class="ai-container" x-data="{
        scrollToBottom() {
            $nextTick(() => {
                const feed = document.getElementById('ai-chat-feed');
                if (feed) {
                    feed.scrollTop = feed.scrollHeight;
                }
            });
        }
    }" x-init="scrollToBottom()" @chat-scrolled.window="scrollToBottom()">

        {{-- LEFT SIDEBAR: Sesi Chat & Konfigurasi --}}
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            
            {{-- Card 1: Sesi Percakapan (History) --}}
            <div class="ai-card">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.4rem; font-weight: 600; font-size: 0.9375rem;">
                        <x-heroicon-o-chat-bubble-left-right class="ai-icon-sm" style="color: #f59e0b;" />
                        <span>Sesi Chat</span>
                        <span style="font-size: 0.7rem; padding: 0.1rem 0.4rem; border-radius: 9999px; background: rgba(245, 158, 11, 0.15); color: #d97706;">
                            {{ count($sessionsList) }}
                        </span>
                    </div>
                </div>

                <div style="margin-bottom: 0.75rem;">
                    <button type="button" wire:click="newSession" class="ai-btn-new-chat">
                        <x-heroicon-m-plus class="ai-icon-sm" />
                        <span>Mulai Chat Baru</span>
                    </button>
                </div>

                <div style="max-height: 220px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.25rem;">
                    @forelse ($sessionsList as $s)
                        <div wire:click="selectSession({{ $s['id'] }})"
                            class="ai-session-item {{ $currentSessionId === $s['id'] ? 'active' : '' }}">
                            <div style="flex: 1; min-width: 0;">
                                <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.8125rem;">
                                    {{ $s['title'] }}
                                </div>
                                <div style="font-size: 0.7rem; color: #9ca3af;">
                                    {{ $s['updated_at'] }}
                                </div>
                            </div>
                            <button type="button"
                                wire:click.stop="deleteSession({{ $s['id'] }})"
                                wire:confirm="Hapus sesi chat ini?"
                                class="del-btn"
                                title="Hapus sesi">
                                <x-heroicon-o-trash class="ai-icon-sm" />
                            </button>
                        </div>
                    @empty
                        <div style="padding: 1.25rem 0.5rem; text-align: center; color: #9ca3af; font-size: 0.75rem;">
                            Belum ada riwayat sesi.<br>Mulai chat baru di samping!
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Card 2: Konfigurasi Model & Database --}}
            <div class="ai-card">
                <div style="display: flex; align-items: center; gap: 0.5rem; padding-bottom: 0.65rem; margin-bottom: 0.85rem; border-bottom: 1px solid #e5e7eb;">
                    <x-heroicon-o-adjustments-horizontal class="ai-icon-md" style="color: #f59e0b;" />
                    <h3 style="margin: 0; font-size: 0.9375rem; font-weight: 600;">Pengaturan Model</h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    {{-- Live Database Access Toggle --}}
                    <div style="padding: 0.65rem; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 0.5rem;">
                        <label class="ai-label" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; margin-bottom: 0.2rem;">
                            <span style="display: flex; align-items: center; gap: 0.4rem; font-weight: 600; color: #d97706;">
                                <x-heroicon-s-circle-stack class="ai-icon-sm" />
                                <span>Akses Database Live</span>
                            </span>
                            <input type="checkbox" wire:model.live="enableDatabaseAccess" style="width: 1.15rem; height: 1.15rem; accent-color: #f59e0b; cursor: pointer;">
                        </label>
                        <div class="ai-hint" style="margin: 0; font-size: 0.72rem; line-height: 1.35;">
                            AI membaca data real-time DiSekitar (jumlah post, kota, kategori, views, rating) via safe read-only SQL tool.
                        </div>
                    </div>

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
                    </div>

                    {{-- Model Name Input --}}
                    <div>
                        <label class="ai-label">Nama Model</label>
                        <input type="text" wire:model.live="model" placeholder="Contoh: Content-blog, gpt-4o, gemini-2.5-flash" class="ai-input" />
                    </div>

                    {{-- Quick Template --}}
                    <div>
                        <label class="ai-label">Template Prompt</label>
                        <select wire:model.live="template" class="ai-select">
                            <option value="">-- Pilih Template --</option>
                            <option value="analytics">📊 Tanya Data / Statistik Database (Live)</option>
                            <option value="article">Artikel Ulasan & Wisata (SEO)</option>
                            <option value="seo">Meta Title & Description Generator</option>
                            <option value="summary">Ringkasan Tempat & Highlight</option>
                            <option value="general">Asisten Umum DiSekitar</option>
                        </select>
                    </div>

                    {{-- System Instructions --}}
                    <div>
                        <label class="ai-label">System Prompt</label>
                        <textarea wire:model.defer="systemPrompt" rows="3" class="ai-textarea" style="font-family: monospace; font-size: 0.75rem;"></textarea>
                    </div>

                    <div style="padding-top: 0.25rem;">
                        <button type="button" wire:click="clearMessages" class="ai-btn-danger">
                            <x-heroicon-o-trash class="ai-icon-sm" />
                            <span>Bersihkan Chat Ini</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT MAIN CHAT AREA --}}
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            
            {{-- Message Feed Container --}}
            <div class="ai-card" style="padding: 0;">
                {{-- Header bar --}}
                <div style="padding: 0.75rem 1.25rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; font-size: 0.8125rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <x-heroicon-s-sparkles class="ai-icon-sm" style="color: #f59e0b;" />
                        <span style="font-weight: 600;">
                            @if ($currentSessionId)
                                Sesi Aktif: {{ collect($sessionsList)->firstWhere('id', $currentSessionId)['title'] ?? 'Percakapan' }}
                            @else
                                Sesi Chat Baru
                            @endif
                        </span>
                        @if ($model)
                            <span style="padding: 0.15rem 0.45rem; border-radius: 0.25rem; background: rgba(156, 163, 175, 0.15); font-family: monospace; font-size: 0.7rem;">
                                {{ $model }}
                            </span>
                        @endif
                    </div>
                    @if ($enableDatabaseAccess)
                        <span style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.72rem; color: #059669; font-weight: 500;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #10b981;"></span>
                            Live DB Aktif
                        </span>
                    @endif
                </div>

                {{-- Scrollable Chat Feed --}}
                <div id="ai-chat-feed" class="ai-chat-feed">
                    @if (empty($messages) && ! $isGenerating)
                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 2.5rem 1rem; color: #6b7280;">
                            <div style="width: 64px; height: 64px; border-radius: 9999px; background-color: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: #f59e0b; margin-bottom: 1rem;">
                                <x-heroicon-o-sparkles class="ai-icon-lg" />
                            </div>
                            <h4 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: 600; color: inherit;">AI Playground Siap</h4>
                            <p style="margin: 0; font-size: 0.875rem; max-width: 420px; line-height: 1.5;">
                                Tulis pertanyaan atau pilih contoh pertanyaan di bawah untuk berinteraksi dengan AI DiSekitar secara live.
                            </p>
                        </div>
                    @else
                        @foreach ($messages as $index => $msg)
                            @if (($msg['role'] ?? '') === 'user')
                                {{-- User Message Bubble --}}
                                <div style="display: flex; justify-content: flex-end;">
                                    <div class="ai-bubble-user">
                                        <div style="display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 0.35rem; font-size: 0.75rem; opacity: 0.85;">
                                            <span style="font-weight: 600;">Anda</span>
                                            <span>{{ $msg['timestamp'] ?? '' }}</span>
                                        </div>
                                        <div style="white-space: pre-wrap; word-break: break-word;">{{ $msg['content'] }}</div>
                                    </div>
                                </div>
                            @else
                                {{-- Assistant Message Bubble --}}
                                <div style="display: flex; justify-content: flex-start;">
                                    <div class="ai-bubble-assistant {{ !empty($msg['is_error']) ? 'ai-bubble-error' : '' }}">
                                        {{-- Header info --}}
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

                                        {{-- Database Tool Verification Badge --}}
                                        @if (!empty($msg['tool_calls']))
                                            <div style="margin-bottom: 0.75rem; padding: 0.45rem 0.65rem; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 0.375rem; font-size: 0.75rem; color: #059669;" x-data="{ showToolDetail: false }">
                                                <div style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;" x-on:click="showToolDetail = !showToolDetail">
                                                    <span style="display: flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                                                        <x-heroicon-m-circle-stack class="ai-icon-sm" style="color: #059669;" />
                                                        <span>Data diverifikasi live dari Database DiSekitar</span>
                                                    </span>
                                                    <span style="font-size: 0.7rem; text-decoration: underline; color: #047857;" x-text="showToolDetail ? 'Tutup Detail Query' : 'Lihat Query Database'"></span>
                                                </div>
                                                <div x-show="showToolDetail" x-cloak style="margin-top: 0.4rem; padding-top: 0.4rem; border-top: 1px dashed rgba(16, 185, 129, 0.2); font-family: monospace; font-size: 0.7rem; color: #065f46;">
                                                    @foreach ($msg['tool_calls'] as $tc)
                                                        <div style="margin-bottom: 0.25rem;">
                                                            <span style="font-weight: bold; color: #047857;">Query:</span> {{ $tc['arguments']['query'] ?? json_encode($tc['arguments']) }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Markdown Formatted Assistant Content --}}
                                        @if (!empty($msg['is_error']))
                                            <div style="white-space: pre-wrap; word-break: break-word; color: #ef4444;">{{ $msg['content'] }}</div>
                                        @else
                                            <div class="ai-markdown">
                                                {!! \Illuminate\Support\Str::markdown($msg['content'] ?? '') !!}
                                            </div>
                                        @endif

                                        {{-- Token Usage & Copy Bar --}}
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

                        {{-- LIVE TYPING / THINKING INDICATOR --}}
                        @if ($isGenerating)
                            <div style="display: flex; justify-content: flex-start;" x-init="scrollToBottom()">
                                <div class="ai-bubble-assistant ai-bubble-thinking" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.15rem;">
                                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                                        <svg class="ai-icon-sm ai-spin" style="color: #f59e0b;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <span style="font-size: 0.8125rem; font-weight: 500; color: #b45309;" class="dark:text-amber-300">
                                            @if ($enableDatabaseAccess)
                                                AI sedang mengecek database & menyusun jawaban...
                                            @else
                                                AI sedang merespon...
                                            @endif
                                        </span>
                                    </div>
                                    <div class="ai-typing-dots">
                                        <span></span><span></span><span></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Automatically dispatch processing to backend right after render --}}
                            <div x-data x-init="$nextTick(() => { $wire.processAiResponse(); })"></div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Input Card --}}
            <div class="ai-card">
                <form wire:submit.prevent="submitPrompt" style="display: flex; flex-direction: column; gap: 0.65rem;">
                    
                    {{-- Quick Prompt Chips --}}
                    @if ($enableDatabaseAccess)
                        <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 0.35rem;">
                            <span style="font-size: 0.72rem; color: #6b7280; font-weight: 500;">Contoh Tanya Data:</span>
                            <button type="button" wire:click="$set('userPrompt', 'Berapa jumlah post di kota Bandung?')" class="ai-chip">📍 Total post Bandung</button>
                            <button type="button" wire:click="$set('userPrompt', 'Tampilkan 5 kota dengan jumlah postingan terbanyak')" class="ai-chip">🏆 Top 5 Kota</button>
                            <button type="button" wire:click="$set('userPrompt', 'Berapa jumlah postingan berdasarkan kategori?')" class="ai-chip">📂 Post per Kategori</button>
                            <button type="button" wire:click="$set('userPrompt', 'Berapa total pengunjung halaman hari ini?')" class="ai-chip">📈 Views Hari Ini</button>
                        </div>
                    @endif

                    {{-- Textarea with Enter-to-send (Shift+Enter for newline) --}}
                    <div>
                        <textarea
                            wire:model="userPrompt"
                            rows="3"
                            placeholder="Tulis prompt atau pertanyaan di sini... (Tekan Enter untuk kirim, Shift+Enter untuk baris baru)"
                            class="ai-textarea"
                            x-on:keydown.enter="if(!$event.shiftKey) { $event.preventDefault(); $wire.submitPrompt(); scrollToBottom(); }"
                            :disabled="@js($isGenerating)"
                            required></textarea>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="ai-hint" style="margin: 0; font-size: 0.72rem;">
                            Tekan <strong>Enter ↵</strong> untuk kirim, <strong>Shift + Enter</strong> untuk baris baru.
                        </div>

                        <button type="submit"
                            @if ($isGenerating) disabled @endif
                            class="ai-btn-primary">
                            @if ($isGenerating)
                                <svg class="ai-icon-sm ai-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Menunggu AI...</span>
                            @else
                                <x-heroicon-m-paper-airplane class="ai-icon-sm" />
                                <span>Kirim Prompt</span>
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>

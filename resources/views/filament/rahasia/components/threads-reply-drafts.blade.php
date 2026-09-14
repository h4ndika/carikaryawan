@if (!empty($drafts))
    <style>
        .reply-draft-card {
            background-color: #1e1e24 !important;
            border: 1px solid #2e2e38 !important;
            border-radius: 10px !important;
            padding: 12px 14px !important;
            margin-bottom: 10px !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 8px !important;
        }
        .reply-draft-header {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 8px !important;
        }
        .reply-draft-badge {
            display: inline-flex !important;
            align-items: center !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            padding: 2px 8px !important;
            border-radius: 6px !important;
            background: rgba(245, 158, 11, 0.15) !important;
            color: #fbbf24 !important;
            border: 1px solid rgba(245, 158, 11, 0.3) !important;
        }
        .reply-draft-title {
            font-size: 11px !important;
            color: #94a3b8 !important;
            font-weight: 500 !important;
        }
        .reply-copy-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            padding: 3px 10px !important;
            border-radius: 6px !important;
            background: #27272a !important;
            color: #e4e4e7 !important;
            border: 1px solid #3f3f46 !important;
            cursor: pointer !important;
            transition: all 0.15s ease !important;
            white-space: nowrap !important;
            line-height: 1.3 !important;
            height: 26px !important;
        }
        .reply-copy-btn:hover {
            background: #3f3f46 !important;
            color: #ffffff !important;
            border-color: #71717a !important;
        }
        .reply-copy-btn.is-copied {
            background: rgba(16, 185, 129, 0.2) !important;
            color: #34d399 !important;
            border-color: #059669 !important;
        }
        .reply-draft-body {
            background: #141417 !important;
            border: 1px solid #27272a !important;
            border-radius: 8px !important;
            padding: 9px 12px !important;
            font-size: 13px !important;
            line-height: 1.5 !important;
            color: #f1f5f9 !important;
            font-family: inherit !important;
            white-space: pre-wrap !important;
            user-select: all !important;
            margin: 0 !important;
        }
    </style>

    <div style="width: 100% !important; padding: 14px 0 6px 0 !important; border-top: 1px solid #27272a !important;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 14px;">✨</span>
                <span style="font-size: 13px; font-weight: 700; color: #f8fafc; letter-spacing: -0.01em;">
                    Rekomendasi Draft Balasan AI
                </span>
            </div>
            <span style="font-size: 11px; color: #94a3b8;">
                Klik tombol Salin di tiap opsi
            </span>
        </div>

        <div>
            @foreach ($drafts as $index => $draft)
                <div x-data="{ copied: false }" class="reply-draft-card">
                    <div class="reply-draft-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="reply-draft-badge">
                                Opsi {{ $index + 1 }}
                            </span>
                            <span class="reply-draft-title">
                                {{ $draft['style'] ?? 'Draft Balasan' }}
                            </span>
                        </div>

                        <button 
                            type="button"
                            x-on:click="navigator.clipboard.writeText(@js($draft['text'] ?? '')); copied = true; setTimeout(() => copied = false, 2000)"
                            class="reply-copy-btn"
                            :class="{ 'is-copied': copied }"
                        >
                            <span x-show="!copied" style="display: inline-flex; align-items: center; gap: 4px;">
                                <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Salin
                            </span>
                            <span x-show="copied" x-cloak style="display: inline-flex; align-items: center; gap: 4px; font-weight: 700;">
                                <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                Tersalin!
                            </span>
                        </button>
                    </div>

                    <div class="reply-draft-body">
                        {{ $draft['text'] ?? '' }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

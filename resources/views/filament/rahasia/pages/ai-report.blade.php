<x-filament-panels::page>
    {{-- External Libraries for Charts & Markdown --}}
    @assets
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    @endassets

    <style>
        /* Scoped Vanilla CSS for AI Report Dashboard */
        .ai-report-wrap {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            font-family: inherit;
            color: #1f2937;
        }
        .dark .ai-report-wrap {
            color: #f3f4f6;
        }

        /* Top Prompt Section */
        .ai-box {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .dark .ai-box {
            background: #111827;
            border-color: #1f2937;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .ai-box-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .ai-box-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .ai-box-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            background: rgba(245, 158, 11, 0.15);
            color: #d97706;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .dark .ai-box-icon {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }
        .ai-title-text {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
        }
        .dark .ai-title-text {
            color: #ffffff;
        }
        .ai-subtitle-text {
            font-size: 0.75rem;
            color: #6b7280;
        }
        .dark .ai-subtitle-text {
            color: #9ca3af;
        }

        .ai-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
            background: #f3f4f6;
            color: #4b5563;
        }
        .dark .ai-badge {
            background: #1f2937;
            color: #d1d5db;
        }
        .ai-badge-success {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        .dark .ai-badge-success {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .ai-model-control {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #f3f4f6;
            padding: 0.2rem 0.5rem 0.2rem 0.65rem;
            border-radius: 9999px;
            border: 1px solid #e5e7eb;
        }
        .dark .ai-model-control {
            background: #1f2937;
            border-color: #374151;
        }
        .ai-model-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .dark .ai-model-label {
            color: #9ca3af;
        }
        .ai-model-input {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.15rem 0.45rem;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #111827;
            width: 140px;
            outline: none;
            transition: all 0.15s;
        }
        .dark .ai-model-input {
            background: #111827;
            border-color: #4b5563;
            color: #f9fafb;
        }
        .ai-model-input:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 1px #f59e0b;
        }

        /* Form Controls */
        .ai-prompt-area {
            position: relative;
            margin-bottom: 0.75rem;
        }
        .ai-textarea {
            width: 100%;
            border-radius: 0.75rem;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            color: #111827;
            padding: 0.85rem 1rem 3rem 1rem;
            font-size: 0.875rem;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.15s, box-shadow 0.15s;
            resize: vertical;
        }
        .dark .ai-textarea {
            background: #1f2937;
            border-color: #374151;
            color: #ffffff;
        }
        .ai-textarea:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
        }

        .ai-prompt-actions {
            position: absolute;
            right: 0.75rem;
            bottom: 0.75rem;
        }
        .ai-btn-submit {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #d97706;
            color: #ffffff;
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }
        .ai-btn-submit:hover {
            background: #b45309;
        }
        .ai-btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Prompt Suggestions Pills */
        .ai-pills-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.4rem;
            font-size: 0.75rem;
        }
        .ai-pill-btn {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.3rem 0.65rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.15s;
        }
        .dark .ai-pill-btn {
            background: #1f2937;
            color: #d1d5db;
            border-color: #374151;
        }
        .ai-pill-btn:hover {
            background: #e5e7eb;
            color: #111827;
        }
        .dark .ai-pill-btn:hover {
            background: #374151;
            color: #ffffff;
        }

        /* 2-Column Split: Sidebar History + Main Dashboard */
        .ai-dashboard-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        @media (max-width: 1024px) {
            .ai-dashboard-layout {
                grid-template-columns: 1fr;
            }
        }

        /* History Sidebar */
        .ai-history-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .dark .ai-history-card {
            background: #111827;
            border-color: #1f2937;
        }
        .ai-history-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .dark .ai-history-header {
            border-color: #1f2937;
        }
        .ai-history-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
        }
        .dark .ai-history-title {
            color: #9ca3af;
        }
        .ai-btn-new {
            font-size: 0.75rem;
            font-weight: 600;
            color: #d97706;
            background: transparent;
            border: none;
            cursor: pointer;
        }
        .dark .ai-btn-new {
            color: #fbbf24;
        }
        .ai-history-list {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            max-height: 520px;
            overflow-y: auto;
        }
        .ai-history-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            cursor: pointer;
            transition: all 0.15s;
            border: 1px solid transparent;
            color: #374151;
        }
        .dark .ai-history-item {
            color: #d1d5db;
        }
        .ai-history-item:hover {
            background: #f9fafb;
        }
        .dark .ai-history-item:hover {
            background: #1f2937;
        }
        .ai-history-item.active {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.3);
            color: #b45309;
            font-weight: 600;
        }
        .dark .ai-history-item.active {
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.4);
        }

        /* Main Report Area */
        .ai-report-main {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        /* Top Action Bar of the Report */
        .ai-report-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .dark .ai-report-topbar {
            background: #111827;
            border-color: #1f2937;
        }
        .ai-report-headings h2 {
            font-size: 1.25rem;
            font-weight: 800;
            margin: 0;
            color: #111827;
        }
        .dark .ai-report-headings h2 {
            color: #ffffff;
        }
        .ai-report-headings p {
            font-size: 0.8125rem;
            color: #6b7280;
            margin: 0.25rem 0 0 0;
        }
        .dark .ai-report-headings p {
            color: #9ca3af;
        }

        .ai-report-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .ai-btn-print {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #0f172a;
            color: #ffffff;
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 0.85rem;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }
        .dark .ai-btn-print {
            background: #f8fafc;
            color: #0f172a;
        }
        .ai-btn-print:hover {
            background: #334155;
        }
        .dark .ai-btn-print:hover {
            background: #e2e8f0;
        }

        /* Tabs */
        .ai-tab-group {
            display: inline-flex;
            background: #f3f4f6;
            border-radius: 0.5rem;
            padding: 0.25rem;
            gap: 0.25rem;
        }
        .dark .ai-tab-group {
            background: #1f2937;
        }
        .ai-tab-btn {
            border: none;
            background: transparent;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.375rem;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.15s;
        }
        .dark .ai-tab-btn {
            color: #9ca3af;
        }
        .ai-tab-btn.active {
            background: #ffffff;
            color: #111827;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .dark .ai-tab-btn.active {
            background: #374151;
            color: #ffffff;
        }

        /* Executive Summary Card */
        .ai-summary-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .dark .ai-summary-card {
            background: #111827;
            border-color: #1f2937;
        }
        .ai-card-title {
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #374151;
            margin-bottom: 0.85rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .ai-card-title {
            color: #e5e7eb;
            border-color: #1f2937;
        }
        .ai-summary-content {
            font-size: 0.875rem;
            line-height: 1.65;
            color: #374151;
        }
        .dark .ai-summary-content {
            color: #d1d5db;
        }
        .ai-summary-content p {
            margin-bottom: 0.85rem;
        }
        .ai-summary-content ul, .ai-summary-content ol {
            margin: 0.5rem 0 0.85rem 1.25rem;
        }
        .ai-summary-content li {
            margin-bottom: 0.25rem;
        }

        /* KPI Stat Cards Grid */
        .ai-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .ai-kpi-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.15rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        .dark .ai-kpi-card {
            background: #111827;
            border-color: #1f2937;
        }
        .ai-kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
        }
        .dark .ai-kpi-top {
            color: #9ca3af;
        }
        .ai-kpi-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.15rem 0.45rem;
            border-radius: 9999px;
            background: #f3f4f6;
            color: #374151;
        }
        .ai-kpi-badge.positive {
            background: #dcfce7;
            color: #15803d;
        }
        .dark .ai-kpi-badge.positive {
            background: rgba(22, 101, 52, 0.3);
            color: #4ade80;
        }
        .ai-kpi-badge.negative {
            background: #fee2e2;
            color: #b91c1c;
        }
        .dark .ai-kpi-badge.negative {
            background: rgba(153, 27, 27, 0.3);
            color: #f87171;
        }
        .ai-kpi-val {
            font-size: 1.75rem;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.02em;
        }
        .dark .ai-kpi-val {
            color: #ffffff;
        }
        .ai-kpi-desc {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        /* Dynamic Charts Grid */
        .ai-chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 1.25rem;
        }
        .ai-chart-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .dark .ai-chart-card {
            background: #111827;
            border-color: #1f2937;
        }
        .ai-chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .ai-chart-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }
        .dark .ai-chart-title {
            color: #ffffff;
        }
        .ai-chart-canvas-box {
            position: relative;
            height: 260px;
            width: 100%;
        }

        /* Dynamic Table Container */
        .ai-table-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .dark .ai-table-card {
            background: #111827;
            border-color: #1f2937;
        }
        .ai-table-scroll {
            overflow-x: auto;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            margin-top: 0.75rem;
        }
        .dark .ai-table-scroll {
            border-color: #1f2937;
        }
        .ai-data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            text-align: left;
        }
        .ai-data-table th {
            background: #f9fafb;
            color: #374151;
            font-weight: 700;
            padding: 0.65rem 0.85rem;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }
        .dark .ai-data-table th {
            background: #1f2937;
            color: #e5e7eb;
            border-color: #374151;
        }
        .ai-data-table td {
            padding: 0.6rem 0.85rem;
            border-bottom: 1px solid #f3f4f6;
            color: #1f2937;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .dark .ai-data-table td {
            border-color: #1f2937;
            color: #d1d5db;
        }
        .ai-data-table tr:hover {
            background: #f9fafb;
        }
        .dark .ai-data-table tr:hover {
            background: #1a2234;
        }

        /* Recommendations Action Plan */
        .ai-rec-card {
            background: rgba(245, 158, 11, 0.05);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 1rem;
            padding: 1.25rem;
        }
        .dark .ai-rec-card {
            background: rgba(245, 158, 11, 0.08);
            border-color: rgba(245, 158, 11, 0.25);
        }
        .ai-rec-list {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            margin-top: 0.75rem;
        }
        .ai-rec-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            background: #ffffff;
            border: 1px solid #fde68a;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 0.8125rem;
            color: #1f2937;
        }
        .dark .ai-rec-item {
            background: #111827;
            border-color: rgba(245, 158, 11, 0.2);
            color: #f3f4f6;
        }
        .ai-rec-num {
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 9999px;
            background: #d97706;
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }

        /* Loading Animation */
        .ai-loading-box {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 2.5rem 1.5rem;
            text-align: center;
        }
        .dark .ai-loading-box {
            background: #111827;
            border-color: #1f2937;
        }
        .ai-spin-icon {
            width: 2.5rem;
            height: 2.5rem;
            margin: 0 auto 1rem auto;
            color: #d97706;
            animation: ai-spin 1s linear infinite;
        }
        @keyframes ai-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Print Specific CSS */
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            .fi-sidebar, .fi-topbar, .fi-header, .no-print, nav, button, aside {
                display: none !important;
            }
            .fi-main, .fi-content {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            .print-only {
                display: block !important;
            }
            .ai-summary-card, .ai-kpi-card, .ai-chart-card, .ai-table-card, .ai-rec-card {
                break-inside: avoid !important;
                box-shadow: none !important;
                border: 1px solid #cccccc !important;
                margin-bottom: 1rem !important;
                background: #ffffff !important;
                color: #000000 !important;
            }
            .ai-chart-canvas-box {
                max-height: 240px !important;
            }
        }
        @media screen {
            .print-only {
                display: none !important;
            }
        }
    </style>

    <div 
        x-data="aiReportManager(@js($report))"
        x-init="initCharts()"
        @report-loaded.window="updateReport($event.detail.report)"
        class="ai-report-wrap"
    >
        {{-- Official Print Header (Visible ONLY when printing / PDF) --}}
        <div class="print-only" style="border-bottom: 2px solid #000; padding-bottom: 0.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 900; margin: 0; text-transform: uppercase;">DiSekitar Intelligence Report</h1>
                    <p style="font-size: 0.75rem; color: #555; margin: 0.25rem 0 0 0;">Dokumen Laporan Eksekutif & Intelijen Data Platform</p>
                </div>
                <div style="text-align: right; font-size: 0.75rem; color: #555;">
                    <p style="margin: 0; font-weight: 700; color: #000;">Tanggal: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
                    <p style="margin: 0.15rem 0 0 0;">Model: {{ $model ?: 'Content-blog' }} ({{ $provider }})</p>
                </div>
            </div>
        </div>

        {{-- PROMPT INPUT BAR --}}
        <div class="no-print ai-box">
            <div class="ai-box-header">
                <div class="ai-box-title">
                    <div class="ai-box-icon">📊</div>
                    <div>
                        <div class="ai-title-text">AI Executive Report Generator</div>
                        <div class="ai-subtitle-text">Instruksikan analisis data, AI mengeksekusi SQL live & menyusun laporan dashboard eksekutif.</div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                    <span class="ai-badge ai-badge-success">⚡ Database Live</span>
                    <div class="ai-model-control">
                        <span class="ai-model-label">Model:</span>
                        <input 
                            type="text" 
                            wire:model="model" 
                            placeholder="Content-blog" 
                            class="ai-model-input"
                            title="Nama Model AI (default: Content-blog)"
                        />
                    </div>
                </div>
            </div>

            {{-- Form Input --}}
            <form wire:submit.prevent="submitPrompt">
                <div class="ai-prompt-area">
                    <textarea 
                        wire:model="userPrompt"
                        rows="3"
                        placeholder="Contoh: Buatkan laporan komparasi views dan sebaran postingan kota di Jawa Barat, tampilkan ranking kota terbaik dan rekomendasi strategi boosting..."
                        class="ai-textarea"
                        :disabled="$wire.isGenerating"
                    ></textarea>

                    <div class="ai-prompt-actions">
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            :disabled="$wire.isGenerating"
                            class="ai-btn-submit"
                        >
                            <span wire:loading.remove wire:target="submitPrompt, processAiReport">
                                🚀 Buat Laporan
                            </span>
                            <span wire:loading wire:target="submitPrompt, processAiReport">
                                ⏳ Sedang Menganalisis...
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Template Suggestions --}}
                <div class="ai-pills-row">
                    <span style="font-weight: 600; font-size: 0.75rem;">💡 Ide Prompt:</span>
                    <button 
                        type="button" 
                        wire:click="applyTemplate('Buatkan laporan performa views dan ranking 10 kota dengan postingan terbanyak di Indonesia')"
                        class="ai-pill-btn"
                    >
                        🏆 Ranking Kota Teratas
                    </button>
                    <button 
                        type="button" 
                        wire:click="applyTemplate('Analisis kota mana yang memiliki listing tinggi namun views masih rendah dan butuh strategi boosting')"
                        class="ai-pill-btn"
                    >
                        📈 Analisis Prioritas Boosting
                    </button>
                    <button 
                        type="button" 
                        wire:click="applyTemplate('Laporan audit sebaran landmarks kota dan korelasi rating listing disekitarnya')"
                        class="ai-pill-btn"
                    >
                        🏛️ Audit Landmarks & Rating
                    </button>
                    <button 
                        type="button" 
                        wire:click="applyTemplate('Ringkasan statistik kunjungan (page_views) hari ini dan postingan terpopuler')"
                        class="ai-pill-btn"
                    >
                        👀 Traffic Hari Ini
                    </button>
                </div>
            </form>
        </div>

        {{-- MAIN DASHBOARD LAYOUT: SIDEBAR + CONTENT --}}
        <div class="ai-dashboard-layout">

            {{-- SIDEBAR: RIWAYAT LAPORAN --}}
            <div class="no-print">
                <div class="ai-history-card">
                    <div class="ai-history-header">
                        <span class="ai-history-title">📁 Riwayat Laporan ({{ count($reportsList) }})</span>
                        <button type="button" wire:click="newReport" class="ai-btn-new">+ Baru</button>
                    </div>

                    @if(empty($reportsList))
                        <div style="text-align: center; padding: 2rem 0; font-size: 0.75rem; color: #9ca3af;">
                            Belum ada laporan tersimpan.
                        </div>
                    @else
                        <div class="ai-history-list">
                            @foreach($reportsList as $r)
                                <div 
                                    class="ai-history-item {{ $currentReportId === $r['id'] ? 'active' : '' }}"
                                    wire:click="selectReport({{ $r['id'] }})"
                                >
                                    <div style="min-width: 0; flex: 1; padding-right: 0.5rem;">
                                        <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.8125rem;">
                                            {{ $r['title'] }}
                                        </div>
                                        <div style="font-size: 0.68rem; color: #9ca3af; margin-top: 0.15rem;">
                                            {{ $r['created_at'] }}
                                        </div>
                                    </div>
                                    <button 
                                        type="button"
                                        wire:click.stop="deleteReport({{ $r['id'] }})"
                                        wire:confirm="Yakin ingin menghapus laporan ini?"
                                        style="background: transparent; border: none; color: #9ca3af; cursor: pointer; padding: 0.2rem;"
                                        title="Hapus"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($report)
                    <div class="ai-box" style="margin-top: 1rem; font-size: 0.75rem; display: flex; flex-direction: column; gap: 0.4rem;">
                        <div style="font-weight: 700;">ℹ️ Metadata Eksekusi</div>
                        <div style="display: flex; justify-content: space-between; color: #6b7280;">
                            <span>Durasi:</span>
                            <span style="font-family: monospace; font-weight: 600;">{{ $duration ? $duration . 's' : '-' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; color: #6b7280;">
                            <span>Total Tokens:</span>
                            <span style="font-family: monospace; font-weight: 600;">{{ $tokens['total'] ?? '-' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; color: #6b7280;">
                            <span>Tool SQL Queries:</span>
                            <span style="font-family: monospace; font-weight: 600;">{{ count($toolCalls) }} query</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- MAIN REPORT CONTENT AREA --}}
            <div class="ai-report-main">

                {{-- LOADING / GENERATING STATE --}}
                <div x-show="$wire.isGenerating" x-cloak class="ai-loading-box">
                    <svg class="ai-spin-icon" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <div style="font-weight: 700; font-size: 1rem; margin-bottom: 0.35rem;">AI Sedang Menyusun Laporan Eksekutif</div>
                    <div style="font-size: 0.8125rem; color: #6b7280; max-width: 480px; margin: 0 auto;">
                        Menghubungi AI Agent, mengeksekusi SQL live database DiSekitar, dan merender visual cards, charts, & data tables...
                    </div>

                    {{-- Automatically dispatch processing to backend right after render --}}
                    <div x-data x-init="$nextTick(() => { $wire.processAiReport(); })"></div>
                </div>

                {{-- ERROR BANNER --}}
                @if($errorMessage)
                    <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; border-radius: 0.75rem; padding: 1rem; font-size: 0.8125rem;">
                        <strong>Terjadi Kesalahan:</strong> {{ $errorMessage }}
                    </div>
                @endif

                {{-- EMPTY STATE --}}
                @if(! $report && ! $isGenerating)
                    <div class="ai-box" style="text-align: center; padding: 3.5rem 1.5rem; border-style: dashed;">
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">📑</div>
                        <div style="font-weight: 700; font-size: 1.1rem; margin-bottom: 0.35rem;">Belum Ada Laporan Aktif</div>
                        <div style="font-size: 0.8125rem; color: #6b7280; max-width: 420px; margin: 0 auto;">
                            Pilih laporan dari daftar riwayat di sebelah kiri, atau ketik pertanyaan analisis di formulir atas untuk membuat laporan baru.
                        </div>
                    </div>
                @endif

                {{-- LOADED REPORT DASHBOARD --}}
                @if($report && ! $isGenerating)
                    {{-- Report Top Bar --}}
                    <div class="ai-report-topbar">
                        <div class="ai-report-headings">
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                <h2>{{ $report['title'] ?? 'Laporan Eksekutif DiSekitar' }}</h2>
                                @if(!empty($report['period_or_scope']))
                                    <span class="ai-badge">📍 {{ $report['period_or_scope'] }}</span>
                                @endif
                            </div>
                            <p>{{ $report['subtitle'] ?? 'Analisis intelijen platform berbasis data riil DiSekitar' }}</p>
                        </div>

                        <div class="no-print ai-report-actions">
                            {{-- Print / Export PDF Button --}}
                            <button type="button" onclick="window.print()" class="ai-btn-print">
                                <span>🖨️</span>
                                <span>Cetak / Export PDF</span>
                            </button>

                            {{-- Tab Switcher --}}
                            <div class="ai-tab-group">
                                <button 
                                    type="button" 
                                    wire:click="$set('activeTab', 'dashboard')" 
                                    class="ai-tab-btn {{ $activeTab === 'dashboard' ? 'active' : '' }}"
                                >
                                    📊 Dashboard
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="$set('activeTab', 'raw')" 
                                    class="ai-tab-btn {{ $activeTab === 'raw' ? 'active' : '' }}"
                                >
                                    📝 Markdown
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="$set('activeTab', 'tools')" 
                                    class="ai-tab-btn {{ $activeTab === 'tools' ? 'active' : '' }}"
                                >
                                    🛠️ SQL ({{ count($toolCalls) }})
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 1: DASHBOARD --}}
                    <div x-show="$wire.activeTab === 'dashboard'" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        
                        {{-- 1. Executive Summary --}}
                        @if(!empty($report['executive_summary']))
                            <div class="ai-summary-card">
                                <div class="ai-card-title">
                                    <span>📋</span>
                                    <span>Executive Summary & Temuan Kunci</span>
                                </div>
                                <div class="ai-summary-content">
                                    {!! \Illuminate\Support\Str::markdown($report['executive_summary']) !!}
                                </div>
                            </div>
                        @endif

                        {{-- 2. KPI Stat Cards --}}
                        @if(!empty($report['cards']) && count($report['cards']) > 0)
                            <div class="ai-kpi-grid">
                                @foreach($report['cards'] as $card)
                                    @php
                                        $badgeType = $card['badge_type'] ?? 'neutral';
                                    @endphp
                                    <div class="ai-kpi-card">
                                        <div class="ai-kpi-top">
                                            <span>{{ $card['label'] ?? 'Metrik' }}</span>
                                            @if(!empty($card['badge']))
                                                <span class="ai-kpi-badge {{ $badgeType }}">
                                                    {{ $card['badge'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="ai-kpi-val">{{ $card['value'] ?? '0' }}</div>
                                        @if(!empty($card['description']))
                                            <div class="ai-kpi-desc">{{ $card['description'] }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- 3. Dynamic Charts --}}
                        @if(!empty($report['charts']) && count($report['charts']) > 0)
                            <div class="ai-chart-grid">
                                @foreach($report['charts'] as $index => $chart)
                                    <div class="ai-chart-card">
                                        <div class="ai-chart-header">
                                            <h4 class="ai-chart-title">{{ $chart['title'] ?? 'Grafik Analisis' }}</h4>
                                            <span class="ai-badge" style="text-transform: uppercase;">{{ $chart['type'] ?? 'bar' }}</span>
                                        </div>
                                        @if(!empty($chart['description']))
                                            <div style="font-size: 0.75rem; color: #9ca3af;">{{ $chart['description'] }}</div>
                                        @endif
                                        <div class="ai-chart-canvas-box">
                                            <canvas id="report-chart-{{ $index }}"></canvas>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- 4. Dynamic Tables --}}
                        @if(!empty($report['tables']) && count($report['tables']) > 0)
                            @foreach($report['tables'] as $table)
                                <div class="ai-table-card">
                                    <div class="ai-chart-header">
                                        <h4 class="ai-chart-title">{{ $table['title'] ?? 'Tabel Data' }}</h4>
                                    </div>
                                    @if(!empty($table['description']))
                                        <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem;">{{ $table['description'] }}</div>
                                    @endif

                                    <div class="ai-table-scroll">
                                        <table class="ai-data-table">
                                            @if(!empty($table['headers']))
                                                <thead>
                                                    <tr>
                                                        @foreach($table['headers'] as $header)
                                                            <th>{{ $header }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                            @endif
                                            <tbody>
                                                @foreach($table['rows'] ?? [] as $row)
                                                    <tr>
                                                        @foreach($row as $cell)
                                                            <td>{{ $cell }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- 5. Recommendations --}}
                        @if(!empty($report['recommendations']) && count($report['recommendations']) > 0)
                            <div class="ai-rec-card">
                                <div style="display: flex; align-items: center; gap: 0.4rem; font-weight: 700; font-size: 0.875rem;">
                                    <span>🎯</span>
                                    <span>Rekomendasi Strategis & Action Plan</span>
                                </div>
                                <div class="ai-rec-list">
                                    @foreach($report['recommendations'] as $idx => $rec)
                                        <div class="ai-rec-item">
                                            <div class="ai-rec-num">{{ $idx + 1 }}</div>
                                            <div style="line-height: 1.5;">{{ $rec }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- TAB 2: RAW TEXT / MARKDOWN --}}
                    <div x-show="$wire.activeTab === 'raw'" x-cloak>
                        <div class="ai-box">
                            <div style="font-weight: 700; font-size: 0.8125rem; margin-bottom: 0.75rem; text-transform: uppercase;">
                                Teks Mentah Hasil Generasi AI
                            </div>
                            <pre style="background: #f3f4f6; color: #111827; padding: 1rem; border-radius: 0.5rem; font-size: 0.75rem; font-family: monospace; white-space: pre-wrap; overflow-x: auto;" class="dark:bg-gray-800 dark:text-gray-100">{{ $rawText }}</pre>
                        </div>
                    </div>

                    {{-- TAB 3: SQL TOOL CALLS --}}
                    <div x-show="$wire.activeTab === 'tools'" x-cloak>
                        <div class="ai-box">
                            <div style="font-weight: 700; font-size: 0.8125rem; margin-bottom: 0.75rem; text-transform: uppercase;">
                                Query SQL yang Dieksekusi Pada Database Live
                            </div>
                            @if(empty($toolCalls))
                                <div style="font-size: 0.75rem; color: #9ca3af;">Tidak ada query yang dieksekusi.</div>
                            @else
                                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                    @foreach($toolCalls as $idx => $tc)
                                        <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.75rem; font-size: 0.75rem;" class="dark:border-gray-800">
                                            <div style="font-weight: 700; color: #d97706; margin-bottom: 0.35rem; font-family: monospace;">
                                                Query #{{ $idx + 1 }} ({{ $tc['name'] }})
                                            </div>
                                            <pre style="background: #111827; color: #34d399; padding: 0.65rem; border-radius: 0.375rem; font-family: monospace; font-size: 0.75rem; white-space: pre-wrap; margin: 0; overflow-x: auto;">{{ $tc['arguments']['query'] ?? json_encode($tc['arguments']) }}</pre>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

            </div>

        </div>

    </div>

    {{-- Script Engine for Dynamic Chart.js and Alpine --}}
    <script>
        function aiReportManager(initialReport) {
            return {
                chartInstances: [],
                currentReport: initialReport,

                updateReport(newReport) {
                    this.currentReport = newReport;
                    this.$nextTick(() => {
                        this.initCharts();
                    });
                },

                initCharts() {
                    // Destroy previous chart instances to avoid canvas overlap
                    this.chartInstances.forEach(chart => chart.destroy());
                    this.chartInstances = [];

                    if (!this.currentReport || !this.currentReport.charts || !this.currentReport.charts.length) {
                        return;
                    }

                    if (typeof Chart === 'undefined') {
                        setTimeout(() => this.initCharts(), 300);
                        return;
                    }

                    const palette = [
                        '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#6366f1', '#f43f5e'
                    ];

                    this.currentReport.charts.forEach((chartConfig, idx) => {
                        const canvas = document.getElementById(`report-chart-${idx}`);
                        if (!canvas) return;

                        const ctx = canvas.getContext('2d');
                        const type = chartConfig.type || 'bar';

                        const datasets = (chartConfig.datasets || []).map((ds, dsIdx) => {
                            let bg = ds.backgroundColor;
                            let border = ds.borderColor;

                            if (!bg) {
                                if (type === 'doughnut' || type === 'pie') {
                                    bg = palette.slice(0, chartConfig.labels ? chartConfig.labels.length : 6);
                                } else {
                                    bg = palette[dsIdx % palette.length];
                                }
                            }

                            return {
                                label: ds.label || 'Data',
                                data: ds.data || [],
                                backgroundColor: bg,
                                borderColor: border || bg,
                                borderWidth: 1,
                                borderRadius: type === 'bar' ? 6 : 0,
                            };
                        });

                        const isDark = document.documentElement.classList.contains('dark');
                        const textColor = isDark ? '#9ca3af' : '#6b7280';
                        const gridColor = isDark ? 'rgba(75, 85, 99, 0.2)' : 'rgba(229, 231, 235, 0.8)';

                        const chartInstance = new Chart(ctx, {
                            type: type,
                            data: {
                                labels: chartConfig.labels || [],
                                datasets: datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: type === 'doughnut' || type === 'pie' || datasets.length > 1,
                                        position: 'bottom',
                                        labels: {
                                            boxWidth: 12,
                                            color: textColor,
                                            font: { size: 11 }
                                        }
                                    },
                                    tooltip: {
                                        padding: 8,
                                        cornerRadius: 6
                                    }
                                },
                                scales: (type === 'doughnut' || type === 'pie') ? {} : {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { color: textColor },
                                        grid: { color: gridColor }
                                    },
                                    x: {
                                        ticks: { color: textColor },
                                        grid: { display: false }
                                    }
                                }
                            }
                        });

                        this.chartInstances.push(chartInstance);
                    });
                }
            };
        }
    </script>
</x-filament-panels::page>

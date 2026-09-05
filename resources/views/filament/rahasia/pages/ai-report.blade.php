<x-filament-panels::page>
    {{-- External Libraries for Charts & Markdown --}}
    @assets
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    @endassets

    <style>
        /* Print Stylesheet for Professional PDF / Print Export */
        @media print {
            body {
                background: #ffffff !important;
                color: #0f172a !important;
                font-size: 11pt !important;
            }
            .fi-sidebar,
            .fi-topbar,
            .fi-header,
            .no-print,
            button,
            nav,
            aside,
            .fi-modal {
                display: none !important;
            }
            .fi-main,
            .fi-page,
            .fi-content {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .print-only {
                display: block !important;
            }
            .report-card,
            .report-chart-container,
            .report-table-container {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
                margin-bottom: 1.5rem !important;
            }
            .chart-canvas {
                max-height: 280px !important;
                width: 100% !important;
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
        class="space-y-6"
    >
        {{-- Print Official Header (Visible ONLY when printing to PDF / Paper) --}}
        <div class="print-only border-b-2 border-slate-900 pb-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase">DiSekitar Intelligence Report</h1>
                    <p class="text-xs text-slate-600">Dokumen Analisis Eksekutif & Laporan Intelijen Data Platform DiSekitar</p>
                </div>
                <div class="text-right text-xs text-slate-600">
                    <p class="font-semibold text-slate-900">Tanggal Cetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
                    <p>Model AI: {{ $model ?: 'Content-blog' }} ({{ $provider }})</p>
                </div>
            </div>
        </div>

        {{-- PROMPT INPUT BAR (Screen Only) --}}
        <div class="no-print bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary-500/10 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-lg">
                        📊
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">AI Executive Report Generator</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Ketik instruksi analisis, AI akan query database live & merender dashboard laporan lengkap.</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                        ⚡ Database Connected
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 font-mono">
                        {{ $model ?: 'Default Model' }}
                    </span>
                </div>
            </div>

            {{-- Prompt Form --}}
            <form wire:submit.prevent="submitPrompt" class="space-y-3">
                <div class="relative">
                    <textarea 
                        wire:model="userPrompt"
                        rows="3"
                        placeholder="Contoh: Buatkan laporan komparasi views dan sebaran postingan kota di Jawa Barat, tampilkan ranking kota terbaik dan rekomendasi strategi boosting..."
                        class="w-full text-sm rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-gray-900 dark:text-white p-3.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition placeholder-gray-400 dark:placeholder-gray-500 resize-y"
                        :disabled="$wire.isGenerating"
                    ></textarea>

                    <div class="absolute right-3 bottom-3 flex items-center gap-2">
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            :disabled="$wire.isGenerating"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-500 active:bg-primary-700 text-white text-xs font-semibold rounded-lg shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="submitPrompt, processAiReport">
                                🚀 Generate Report
                            </span>
                            <span wire:loading wire:target="submitPrompt, processAiReport" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Sedang Analisis...
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Template Suggestions --}}
                <div class="flex items-center gap-2 flex-wrap text-xs text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-700 dark:text-gray-300">💡 Ide Prompt:</span>
                    <button 
                        type="button" 
                        wire:click="applyTemplate('Buatkan laporan performa views dan ranking 10 kota dengan postingan terbanyak di Indonesia')"
                        class="px-2.5 py-1 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition"
                    >
                        🏆 Ranking Kota Teratas
                    </button>
                    <button 
                        type="button" 
                        wire:click="applyTemplate('Analisis kota mana yang memiliki listing tinggi namun views masih rendah dan butuh strategi boosting')"
                        class="px-2.5 py-1 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition"
                    >
                        📈 Analisis Prioritas Boosting
                    </button>
                    <button 
                        type="button" 
                        wire:click="applyTemplate('Laporan audit sebaran landmarks kota dan korelasi rating listing disekitarnya')"
                        class="px-2.5 py-1 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition"
                    >
                        🏛️ Audit Landmarks & Rating
                    </button>
                    <button 
                        type="button" 
                        wire:click="applyTemplate('Ringkasan statistik kunjungan (page_views) hari ini dan postingan terpopuler')"
                        class="px-2.5 py-1 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition"
                    >
                        👀 Traffic Hari Ini
                    </button>
                </div>
            </form>
        </div>

        {{-- MAIN LAYOUT: SIDEBAR HISTORY + DASHBOARD CONTENT --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            {{-- LEFT SIDEBAR: RIWAYAT LAPORAN (Screen Only) --}}
            <div class="no-print lg:col-span-1 space-y-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            📁 Riwayat Laporan ({{ count($reportsList) }})
                        </h3>
                        <button 
                            type="button"
                            wire:click="newReport"
                            class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium"
                        >
                            + Baru
                        </button>
                    </div>

                    @if(empty($reportsList))
                        <div class="text-center py-6 text-xs text-gray-400">
                            Belum ada laporan tersimpan.
                        </div>
                    @else
                        <div class="space-y-1.5 max-h-[580px] overflow-y-auto pr-1">
                            @foreach($reportsList as $r)
                                <div 
                                    class="group flex items-center justify-between p-2.5 rounded-xl text-xs transition cursor-pointer {{ $currentReportId === $r['id'] ? 'bg-primary-50 dark:bg-primary-950/40 border border-primary-200 dark:border-primary-800 font-semibold text-primary-700 dark:text-primary-300' : 'hover:bg-gray-100 dark:hover:bg-gray-800/60 text-gray-700 dark:text-gray-300 border border-transparent' }}"
                                    wire:click="selectReport({{ $r['id'] }})"
                                >
                                    <div class="min-w-0 flex-1 pr-2">
                                        <p class="truncate">{{ $r['title'] }}</p>
                                        <div class="flex items-center gap-1.5 text-[10px] text-gray-400 mt-0.5">
                                            <span>{{ $r['created_at'] }}</span>
                                            <span>•</span>
                                            <span class="truncate">{{ $r['model'] }}</span>
                                        </div>
                                    </div>
                                    <button 
                                        type="button"
                                        wire:click.stop="deleteReport({{ $r['id'] }})"
                                        wire:confirm="Yakin ingin menghapus laporan ini?"
                                        class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition p-1"
                                        title="Hapus laporan"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Quick Metadata Widget if report loaded --}}
                @if($report)
                    <div class="bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 text-xs text-gray-600 dark:text-gray-400 space-y-2">
                        <div class="font-semibold text-gray-800 dark:text-gray-200">ℹ️ Info Eksekusi</div>
                        <div class="flex justify-between">
                            <span>Waktu Generate:</span>
                            <span class="font-mono text-gray-900 dark:text-white">{{ $duration ? $duration . 's' : '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total Tokens:</span>
                            <span class="font-mono text-gray-900 dark:text-white">{{ $tokens['total'] ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>SQL Queries:</span>
                            <span class="font-mono text-gray-900 dark:text-white">{{ count($toolCalls) }} query</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- RIGHT MAIN AREA: REPORT DASHBOARD & COMPONENTS --}}
            <div class="lg:col-span-3 space-y-6">

                {{-- LOADING GENERATING STATE --}}
                <div 
                    x-show="$wire.isGenerating"
                    x-cloak
                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-8 text-center space-y-4 shadow-sm"
                >
                    <div class="inline-flex p-4 rounded-full bg-primary-500/10 text-primary-600 dark:text-primary-400 animate-pulse">
                        <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">AI Sedang Menyusun Laporan Eksekutif</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Menghubungi AI Agent, mengeksekusi multi-step query database live DiSekitar, dan merender komponen visual...
                        </p>
                    </div>

                    {{-- Automatically dispatch processing to backend right after render --}}
                    <div x-data x-init="$nextTick(() => { $wire.processAiReport(); })"></div>
                </div>

                {{-- ERROR MESSAGE --}}
                @if($errorMessage)
                    <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-300 text-xs">
                        <p class="font-semibold">Terjadi Kesalahan:</p>
                        <p>{{ $errorMessage }}</p>
                    </div>
                @endif

                {{-- EMPTY STATE IF NO REPORT --}}
                @if(! $report && ! $isGenerating)
                    <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-12 text-center space-y-3">
                        <div class="text-4xl">📑</div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Belum Ada Laporan Aktif</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                            Pilih laporan dari riwayat di sebelah kiri, atau masukkan prompt di atas untuk membuat analisis intelijen data baru secara instan.
                        </p>
                    </div>
                @endif

                {{-- REPORT DASHBOARD CONTAINER --}}
                @if($report && ! $isGenerating)
                    <div class="space-y-6">

                        {{-- REPORT HEADER & ACTION BAR --}}
                        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">
                                        {{ $report['title'] ?? 'Laporan Eksekutif DiSekitar' }}
                                    </h2>
                                    @if(!empty($report['period_or_scope']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            📍 {{ $report['period_or_scope'] }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $report['subtitle'] ?? 'Analisis intelijen data platform berbasis data riil DiSekitar' }}
                                </p>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="no-print flex items-center gap-2 flex-wrap">
                                {{-- PRINT / PDF EXPORT BUTTON --}}
                                <button 
                                    type="button" 
                                    onclick="window.print()"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-100 dark:hover:bg-white text-white dark:text-slate-900 text-xs font-semibold rounded-xl shadow-sm transition cursor-pointer"
                                    title="Cetak atau Ekspor sebagai PDF"
                                >
                                    <span>🖨️</span>
                                    <span>Cetak / Export PDF</span>
                                </button>

                                {{-- TAB CONTROLS --}}
                                <div class="inline-flex rounded-xl bg-gray-100 dark:bg-gray-800 p-1 text-xs">
                                    <button 
                                        type="button"
                                        wire:click="$set('activeTab', 'dashboard')"
                                        class="px-3 py-1.5 rounded-lg font-medium transition {{ $activeTab === 'dashboard' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                                    >
                                        📊 Dashboard
                                    </button>
                                    <button 
                                        type="button"
                                        wire:click="$set('activeTab', 'raw')"
                                        class="px-3 py-1.5 rounded-lg font-medium transition {{ $activeTab === 'raw' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                                    >
                                        📝 Markdown
                                    </button>
                                    <button 
                                        type="button"
                                        wire:click="$set('activeTab', 'tools')"
                                        class="px-3 py-1.5 rounded-lg font-medium transition {{ $activeTab === 'tools' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                                    >
                                        🛠️ Query SQL ({{ count($toolCalls) }})
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 1: DASHBOARD VIEW --}}
                        <div x-show="$wire.activeTab === 'dashboard'" class="space-y-6">

                            {{-- 1. EXECUTIVE SUMMARY --}}
                            @if(!empty($report['executive_summary']))
                                <div class="report-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm space-y-3">
                                    <div class="flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
                                        <span class="text-lg">📋</span>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                            Executive Summary & Temuan Kunci
                                        </h3>
                                    </div>
                                    <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed">
                                        {!! \Illuminate\Support\Str::markdown($report['executive_summary']) !!}
                                    </div>
                                </div>
                            @endif

                            {{-- 2. DYNAMIC STAT CARDS GRID (If Present) --}}
                            @if(!empty($report['cards']) && count($report['cards']) > 0)
                                <div class="report-card grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ min(4, count($report['cards'])) }} gap-4">
                                    @foreach($report['cards'] as $card)
                                        @php
                                            $color = $card['color'] ?? 'blue';
                                            $badgeType = $card['badge_type'] ?? 'neutral';
                                        @endphp
                                        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm space-y-2">
                                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                                <span class="font-medium truncate">{{ $card['label'] ?? 'Metrik' }}</span>
                                                @if(!empty($card['badge']))
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $badgeType === 'positive' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : ($badgeType === 'negative' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400') }}">
                                                        {{ $card['badge'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">
                                                {{ $card['value'] ?? '0' }}
                                            </div>
                                            @if(!empty($card['description']))
                                                <p class="text-[11px] text-gray-400 leading-tight">
                                                    {{ $card['description'] }}
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- 3. DYNAMIC CHARTS (If Present) --}}
                            @if(!empty($report['charts']) && count($report['charts']) > 0)
                                <div class="grid grid-cols-1 {{ count($report['charts']) > 1 ? 'lg:grid-cols-2' : '' }} gap-6">
                                    @foreach($report['charts'] as $index => $chart)
                                        <div class="report-chart-container bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm space-y-3">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                                                        {{ $chart['title'] ?? 'Grafik Analisis' }}
                                                    </h4>
                                                    @if(!empty($chart['description']))
                                                        <p class="text-xs text-gray-400">{{ $chart['description'] }}</p>
                                                    @endif
                                                </div>
                                                <span class="text-xs uppercase font-mono px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500">
                                                    {{ $chart['type'] ?? 'bar' }}
                                                </span>
                                            </div>
                                            <div class="relative h-64 w-full">
                                                <canvas id="report-chart-{{ $index }}" class="chart-canvas"></canvas>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- 4. DYNAMIC DATA TABLES (If Present) --}}
                            @if(!empty($report['tables']) && count($report['tables']) > 0)
                                @foreach($report['tables'] as $table)
                                    <div class="report-table-container bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm space-y-3 overflow-hidden">
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                                                {{ $table['title'] ?? 'Tabel Data' }}
                                            </h4>
                                            @if(!empty($table['description']))
                                                <p class="text-xs text-gray-400">{{ $table['description'] }}</p>
                                            @endif
                                        </div>

                                        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                                            <table class="w-full text-left text-xs">
                                                @if(!empty($table['headers']))
                                                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-600 dark:text-gray-300 font-semibold border-b border-gray-200 dark:border-gray-700">
                                                        <tr>
                                                            @foreach($table['headers'] as $header)
                                                                <th class="py-3 px-4">{{ $header }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                @endif
                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                                    @foreach($table['rows'] ?? [] as $row)
                                                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                                                            @foreach($row as $cell)
                                                                <td class="py-3 px-4 text-gray-800 dark:text-gray-200 font-mono">{{ $cell }}</td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            {{-- 5. STRATEGIC RECOMMENDATIONS / ACTION PLAN (If Present) --}}
                            @if(!empty($report['recommendations']) && count($report['recommendations']) > 0)
                                <div class="report-card bg-gradient-to-br from-primary-500/5 to-indigo-500/5 border border-primary-200 dark:border-primary-900/40 rounded-2xl p-6 shadow-sm space-y-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">🎯</span>
                                        <h3 class="text-sm font-bold text-primary-900 dark:text-primary-300 uppercase tracking-wider">
                                            Rekomendasi Strategis & Action Plan
                                        </h3>
                                    </div>
                                    <div class="space-y-2.5">
                                        @foreach($report['recommendations'] as $idx => $rec)
                                            <div class="flex items-start gap-3 p-3 rounded-xl bg-white/80 dark:bg-gray-900/80 border border-primary-100 dark:border-primary-900/30 text-xs text-gray-800 dark:text-gray-200">
                                                <div class="w-5 h-5 rounded-full bg-primary-600 text-white font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">
                                                    {{ $idx + 1 }}
                                                </div>
                                                <div class="leading-relaxed">
                                                    {{ $rec }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>

                        {{-- TAB 2: RAW TEXT / MARKDOWN VIEW --}}
                        <div x-show="$wire.activeTab === 'raw'" x-cloak class="space-y-4">
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Teks Mentah Hasil Generasi AI</h4>
                                <pre class="text-xs font-mono p-4 rounded-xl bg-gray-50 dark:bg-gray-800 overflow-x-auto text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $rawText }}</pre>
                            </div>
                        </div>

                        {{-- TAB 3: SQL TOOL CALLS VIEW --}}
                        <div x-show="$wire.activeTab === 'tools'" x-cloak class="space-y-4">
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm space-y-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">Query SQL yang Dieksekusi Pada Database Live</h4>
                                @if(empty($toolCalls))
                                    <p class="text-xs text-gray-400">Tidak ada query yang dieksekusi.</p>
                                @else
                                    <div class="space-y-3">
                                        @foreach($toolCalls as $idx => $tc)
                                            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 text-xs space-y-1.5">
                                                <div class="font-semibold text-primary-600 dark:text-primary-400 font-mono">
                                                    Query #{{ $idx + 1 }} ({{ $tc['name'] }})
                                                </div>
                                                <pre class="text-[11px] font-mono p-2.5 rounded bg-gray-900 text-emerald-400 overflow-x-auto whitespace-pre-wrap">{{ $tc['arguments']['query'] ?? json_encode($tc['arguments']) }}</pre>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                @endif

            </div>

        </div>

    </div>

    {{-- Script Engine for Dynamic Chart.js and Markdown rendering --}}
    <script>
        function aiReportManager(initialReport) {
            return {
                chartInstances: [],
                currentReport: initialReport,

                renderMarkdown(text) {
                    if (!text) return '';
                    if (typeof marked !== 'undefined') {
                        return marked.parse(text);
                    }
                    return text.replace(/\n/g, '<br>');
                },

                updateReport(newReport) {
                    this.currentReport = newReport;
                    this.$nextTick(() => {
                        this.initCharts();
                    });
                },

                initCharts() {
                    // Destroy previous chart instances
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

                        // Prepare datasets
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
                                            font: { size: 11 }
                                        }
                                    },
                                    tooltip: {
                                        padding: 10,
                                        cornerRadius: 8
                                    }
                                },
                                scales: (type === 'doughnut' || type === 'pie') ? {} : {
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: 'rgba(156, 163, 175, 0.15)' }
                                    },
                                    x: {
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

        // Handle auto phase-2 processing right after form submit starts
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('report-generation-started', () => {
                setTimeout(() => {
                    Livewire.dispatch('processAiReport');
                }, 100);
            });
        });
    </script>
</x-filament-panels::page>

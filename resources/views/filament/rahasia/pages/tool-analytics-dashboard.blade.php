<x-filament-panels::page>
    <style>
        .tools-analytics-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            font-family: inherit;
        }
        .tools-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
        }
        .dark .tools-card {
            background: #18181b;
            border-color: #27272a;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        .tools-header-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .period-btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            background: #f4f4f5;
            padding: 0.25rem;
            border-radius: 0.75rem;
        }
        .dark .period-btn-group {
            background: #27272a;
        }
        .period-btn {
            padding: 0.4rem 0.85rem;
            font-size: 0.8125rem;
            font-weight: 500;
            border-radius: 0.5rem;
            border: none;
            background: transparent;
            color: #52525b;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .dark .period-btn {
            color: #a1a1aa;
        }
        .period-btn:hover {
            color: #09090b;
        }
        .dark .period-btn:hover {
            color: #fafafa;
        }
        .period-btn.active {
            background: #ffffff;
            color: #d97706;
            font-weight: 700;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .dark .period-btn.active {
            background: #18181b;
            color: #f59e0b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
        }
        .tool-select {
            padding: 0.4rem 0.85rem;
            font-size: 0.8125rem;
            border: 1px solid #d4d4d8;
            border-radius: 0.5rem;
            background: #ffffff;
            color: #18181b;
            cursor: pointer;
        }
        .dark .tool-select {
            background: #27272a;
            border-color: #3f3f46;
            color: #fafafa;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }
        .stat-card-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
        }
        .stat-val {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.2;
            color: #18181b;
        }
        .dark .stat-val {
            color: #fafafa;
        }
        .stat-lbl {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #71717a;
            margin-bottom: 0.35rem;
        }
        .dark .stat-lbl {
            color: #a1a1aa;
        }
        .stat-sub {
            font-size: 0.75rem;
            color: #a1a1aa;
            margin-top: 0.25rem;
        }
        .dark .stat-sub {
            color: #71717a;
        }
        .stat-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .icon-box-amber { background: #fef3c7; color: #d97706; }
        .dark .icon-box-amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .icon-box-blue { background: #dbeafe; color: #2563eb; }
        .dark .icon-box-blue { background: rgba(37, 99, 235, 0.15); color: #60a5fa; }
        .icon-box-emerald { background: #d1fae5; color: #059669; }
        .dark .icon-box-emerald { background: rgba(5, 150, 105, 0.15); color: #34d399; }
        .icon-box-purple { background: #ede9fe; color: #7c3aed; }
        .dark .icon-box-purple { background: rgba(124, 58, 237, 0.15); color: #a78bfa; }

        .tools-breakdown-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
        }
        .tool-bar-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
        }
        .progress-bar-wrap {
            width: 100%;
            height: 8px;
            background: #f4f4f5;
            border-radius: 9999px;
            overflow: hidden;
            margin-top: 1rem;
        }
        .dark .progress-bar-wrap {
            background: #27272a;
        }
        .progress-bar-inner {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.4s ease;
        }

        .two-cols-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }
        @media (max-width: 1024px) {
            .two-cols-grid {
                grid-template-columns: 1fr;
            }
        }
        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }
        .data-tbl {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.8125rem;
        }
        .data-tbl th {
            padding: 0.75rem 0.85rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #71717a;
            border-bottom: 1px solid #e5e7eb;
            background: #fcfcfc;
        }
        .dark .data-tbl th {
            background: #1f1f23;
            color: #a1a1aa;
            border-bottom-color: #27272a;
        }
        .data-tbl td {
            padding: 0.75rem 0.85rem;
            border-bottom: 1px solid #f4f4f5;
            vertical-align: middle;
        }
        .dark .data-tbl td {
            border-bottom-color: #27272a;
        }
        .badge-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.6rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .dark .badge-success { background: rgba(5, 150, 105, 0.2); color: #34d399; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .dark .badge-danger { background: rgba(220, 38, 38, 0.2); color: #f87171; }
        .code-tag {
            font-family: monospace;
            padding: 0.15rem 0.45rem;
            border-radius: 0.35rem;
            background: #f4f4f5;
            font-size: 0.75rem;
            color: #3f3f46;
        }
        .dark .code-tag {
            background: #27272a;
            color: #d4d4d8;
        }
    </style>

    @php
        $data = $this->getViewData();
        $toolNames = [
            'cover_letter' => ['name' => 'Cover Letter AI', 'color' => '#6366f1', 'icon' => 'fa-solid fa-file-pen'],
            'cv_builder' => ['name' => 'ATS CV Builder', 'color' => '#10b981', 'icon' => 'fa-solid fa-file-lines'],
            'interview_prep' => ['name' => 'Interview Prep AI', 'color' => '#8b5cf6', 'icon' => 'fa-solid fa-microphone'],
        ];
    @endphp

    <div class="tools-analytics-container">

        {{-- Filter & Control Header --}}
        <div class="tools-card tools-header-bar">
            <div style="display: flex; align-items: center; gap: 0.85rem;">
                <div style="width: 44px; height: 44px; border-radius: 0.75rem; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: inherit;">
                        Monitoring & Tracking Tools AI
                    </h3>
                    <p style="margin: 0.15rem 0 0; font-size: 0.75rem; color: #71717a;">
                        Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</strong>
                    </p>
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;">
                <div class="period-btn-group">
                    <button type="button" wire:click="setPeriod('today')" class="period-btn {{ $period === 'today' ? 'active' : '' }}">Hari Ini</button>
                    <button type="button" wire:click="setPeriod('yesterday')" class="period-btn {{ $period === 'yesterday' ? 'active' : '' }}">Kemarin</button>
                    <button type="button" wire:click="setPeriod('7days')" class="period-btn {{ $period === '7days' ? 'active' : '' }}">7 Hari</button>
                    <button type="button" wire:click="setPeriod('30days')" class="period-btn {{ $period === '30days' ? 'active' : '' }}">30 Hari</button>
                    <button type="button" wire:click="setPeriod('this_month')" class="period-btn {{ $period === 'this_month' ? 'active' : '' }}">Bulan Ini</button>
                    <button type="button" wire:click="setPeriod('all_time')" class="period-btn {{ $period === 'all_time' ? 'active' : '' }}">Semua</button>
                </div>

                <select wire:model.live="selectedTool" class="tool-select">
                    <option value="all">🔍 Semua Tool</option>
                    <option value="cover_letter">Cover Letter AI</option>
                    <option value="cv_builder">ATS CV Builder</option>
                    <option value="interview_prep">Interview Prep AI</option>
                </select>
            </div>
        </div>

        {{-- Top KPI Cards --}}
        <div class="stats-grid">
            {{-- KPI 1 --}}
            <div class="tools-card stat-card-item">
                <div>
                    <div class="stat-lbl">Total Eksekusi</div>
                    <div class="stat-val">{{ number_format($data['totalExecutions']) }}</div>
                    <div class="stat-sub">Permintaan generate / polish</div>
                </div>
                <div class="stat-icon-box icon-box-amber">
                    <i class="fa-solid fa-bolt"></i>
                </div>
            </div>

            {{-- KPI 2 --}}
            <div class="tools-card stat-card-item">
                <div>
                    <div class="stat-lbl">Pengguna Unik</div>
                    <div class="stat-val">{{ number_format($data['uniqueUsers']) }}</div>
                    <div class="stat-sub">Berdasarkan IP unik</div>
                </div>
                <div class="stat-icon-box icon-box-blue">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            {{-- KPI 3 --}}
            <div class="tools-card stat-card-item">
                <div>
                    <div class="stat-lbl">Tingkat Keberhasilan</div>
                    <div class="stat-val" style="color: #059669;">{{ $data['successRate'] }}%</div>
                    <div class="stat-sub">{{ number_format($data['successfulExecutions']) }} sukses diproses</div>
                </div>
                <div class="stat-icon-box icon-box-emerald">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>

            {{-- KPI 4 --}}
            <div class="tools-card stat-card-item">
                <div>
                    <div class="stat-lbl">Tools Aktif</div>
                    <div class="stat-val" style="color: #7c3aed;">3 Tools</div>
                    <div class="stat-sub">Cover Letter, CV, Interview</div>
                </div>
                <div class="stat-icon-box icon-box-purple">
                    <i class="fa-solid fa-cubes"></i>
                </div>
            </div>
        </div>

        {{-- Tool Breakdown Cards --}}
        <div class="tools-breakdown-grid">
            @forelse($data['toolBreakdown'] as $tb)
                @php
                    $info = $toolNames[$tb->tool_name] ?? ['name' => ucfirst($tb->tool_name), 'color' => '#71717a', 'icon' => 'fa-solid fa-wrench'];
                    $pct = $data['totalExecutions'] > 0 ? round(($tb->count / $data['totalExecutions']) * 100, 1) : 0;
                @endphp
                <div class="tools-card tool-bar-card">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                            <span style="font-weight: 700; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="{{ $info['icon'] }}" style="color: {{ $info['color'] }};"></i>
                                {{ $info['name'] }}
                            </span>
                            <span style="font-size: 0.75rem; font-weight: 700; color: #71717a;">{{ $pct }}%</span>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: inherit;">
                            {{ number_format($tb->count) }} <span style="font-size: 0.75rem; font-weight: 500; color: #71717a;">eksekusi</span>
                        </div>
                        <div style="font-size: 0.75rem; color: #71717a; margin-top: 0.25rem;">
                            Digunakan oleh <strong>{{ number_format($tb->unique_users) }}</strong> pengguna unik
                        </div>
                    </div>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-inner" style="width: {{ $pct }}%; background-color: {{ $info['color'] }};"></div>
                    </div>
                </div>
            @empty
                <div class="tools-card" style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #71717a;">
                    Belum ada data eksekusi tool pada rentang tanggal ini.
                </div>
            @endforelse
        </div>

        {{-- 2 Tables: Actions & Target Roles --}}
        <div class="two-cols-grid">
            {{-- Table 1: Actions breakdown --}}
            <div class="tools-card" style="padding: 1.25rem;">
                <h4 style="margin: 0 0 1rem; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-chart-pie" style="color: #d97706;"></i>
                    Distribusi Aksi / Fitur Spesifik
                </h4>
                <div class="table-wrap">
                    <table class="data-tbl">
                        <thead>
                            <tr>
                                <th>Tool</th>
                                <th>Aksi</th>
                                <th style="text-align: right;">Frekuensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['actionBreakdown'] as $ab)
                                <tr>
                                    <td style="font-weight: 600;">
                                        {{ $toolNames[$ab->tool_name]['name'] ?? $ab->tool_name }}
                                    </td>
                                    <td>
                                        <span class="code-tag">{{ $ab->action }}</span>
                                    </td>
                                    <td style="text-align: right; font-weight: 700;">
                                        {{ number_format($ab->count) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #a1a1aa; padding: 1.5rem;">Belum ada aktivitas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Table 2: Top Roles --}}
            <div class="tools-card" style="padding: 1.25rem;">
                <h4 style="margin: 0 0 1rem; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-briefcase" style="color: #d97706;"></i>
                    Lowongan & Posisi Paling Sering Dilatih
                </h4>
                <div class="table-wrap">
                    <table class="data-tbl">
                        <thead>
                            <tr>
                                <th>Target Posisi</th>
                                <th>Perusahaan</th>
                                <th style="text-align: right;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['topRoles'] as $tr)
                                <tr>
                                    <td style="font-weight: 600;">
                                        {{ $tr->target_position }}
                                    </td>
                                    <td style="color: #71717a;">
                                        {{ $tr->company_name ?: '-' }}
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #d97706;">
                                        {{ number_format($tr->count) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #a1a1aa; padding: 1.5rem;">Belum ada posisi tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Table 3: Recent Activity Logs --}}
        <div class="tools-card" style="padding: 1.25rem;">
            <h4 style="margin: 0 0 1rem; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-clock-rotate-left" style="color: #d97706;"></i>
                Log Aktivitas Penggunaan Realtime
            </h4>
            <div class="table-wrap">
                <table class="data-tbl">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Tool</th>
                            <th>Aksi</th>
                            <th>Posisi / Loker Terkait</th>
                            <th>IP Address</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['recentLogs'] as $log)
                            <tr>
                                <td style="color: #71717a; white-space: nowrap;">
                                    {{ $log->created_at->format('d M Y, H:i:s') }}
                                </td>
                                <td style="font-weight: 600; white-space: nowrap;">
                                    <i class="{{ $toolNames[$log->tool_name]['icon'] ?? 'fa-solid fa-wrench' }}" style="color: {{ $toolNames[$log->tool_name]['color'] ?? '#71717a' }}; margin-right: 0.35rem;"></i>
                                    {{ $toolNames[$log->tool_name]['name'] ?? $log->tool_name }}
                                </td>
                                <td style="white-space: nowrap;">
                                    <span class="code-tag">{{ $log->action }}</span>
                                </td>
                                <td>
                                    @if($log->target_position)
                                        <strong>{{ $log->target_position }}</strong>
                                        @if($log->company_name)
                                            <span style="color: #a1a1aa; font-size: 0.75rem;"> · {{ $log->company_name }}</span>
                                        @endif
                                    @elseif($log->post)
                                        <strong>{{ $log->post->title }}</strong>
                                    @else
                                        <span style="color: #a1a1aa;">-</span>
                                    @endif
                                </td>
                                <td style="font-family: monospace; font-size: 0.75rem; color: #a1a1aa; white-space: nowrap;">
                                    {{ $log->ip_address ?: '-' }}
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    @if($log->status === 'success')
                                        <span class="badge-pill badge-success">Sukses</span>
                                    @else
                                        <span class="badge-pill badge-danger">Gagal</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: #a1a1aa; padding: 2rem;">Belum ada riwayat eksekusi tool.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-filament-panels::page>

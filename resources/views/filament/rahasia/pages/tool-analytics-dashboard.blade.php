<x-filament-panels::page>
    @php
        $data = $this->getViewData();
        $toolNames = [
            'cover_letter' => ['name' => 'Cover Letter AI', 'color' => '#6366f1', 'badge' => 'indigo', 'icon' => 'heroicon-o-document-text'],
            'cv_builder' => ['name' => 'ATS CV Builder', 'color' => '#10b981', 'badge' => 'emerald', 'icon' => 'heroicon-o-document-duplicate'],
            'interview_prep' => ['name' => 'Interview Prep AI', 'color' => '#8b5cf6', 'badge' => 'purple', 'icon' => 'heroicon-o-chat-bubble-left-right'],
        ];
    @endphp

    <div class="space-y-6">
        {{-- Header & Period Filter Toolbar --}}
        <div class="p-4 sm:p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-sparkles" class="w-6 h-6 text-amber-500" />
                    Monitoring & Tracking Tools AI
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    Pantau volume penggunaan, frekuensi per fitur, dan performa tools karir yang aktif.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Period buttons --}}
                <div class="inline-flex bg-zinc-100 dark:bg-zinc-800 p-1 rounded-xl text-xs font-medium">
                    @foreach([
                        'today' => 'Hari Ini',
                        'yesterday' => 'Kemarin',
                        '7days' => '7 Hari',
                        '30days' => '30 Hari',
                        'this_month' => 'Bulan Ini',
                        'all_time' => 'Semua',
                    ] as $key => $label)
                        <button type="button"
                                wire:click="setPeriod('{{ $key }}')"
                                class="px-3 py-1.5 rounded-lg transition-all {{ $period === $key ? 'bg-white dark:bg-zinc-900 text-amber-600 dark:text-amber-400 font-bold shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Tool Filter dropdown --}}
                <select wire:model.live="selectedTool"
                        class="text-xs font-medium rounded-xl border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 px-3 py-2 shadow-sm focus:ring-amber-500 focus:border-amber-500">
                    <option value="all">Semua Tool</option>
                    <option value="cover_letter">Cover Letter AI</option>
                    <option value="cv_builder">ATS CV Builder</option>
                    <option value="interview_prep">Interview Prep AI</option>
                </select>
            </div>
        </div>

        {{-- Top Summary KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- KPI 1: Total Generates --}}
            <div class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Eksekusi</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-arrow-trending-up" class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-50">
                        {{ number_format($data['totalExecutions']) }}
                    </div>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">Total permintaan generate / polish</p>
                </div>
            </div>

            {{-- KPI 2: Unique Users / IP --}}
            <div class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pengguna Unik</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-users" class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-50">
                        {{ number_format($data['uniqueUsers']) }}
                    </div>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">Berdasarkan IP unik pengunjung</p>
                </div>
            </div>

            {{-- KPI 3: Success Rate --}}
            <div class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tingkat Keberhasilan</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">
                        {{ $data['successRate'] }}%
                    </div>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">{{ number_format($data['successfulExecutions']) }} berhasil diproses</p>
                </div>
            </div>

            {{-- KPI 4: Active Tools Count --}}
            <div class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tools Terpantau</span>
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-squares-2x2" class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">
                        3 Tools
                    </div>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">Cover Letter, CV Builder, Interview Prep</p>
                </div>
            </div>
        </div>

        {{-- Breakdown per Tool Card --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @forelse($data['toolBreakdown'] as $tb)
                @php
                    $info = $toolNames[$tb->tool_name] ?? ['name' => ucfirst($tb->tool_name), 'color' => '#71717a', 'badge' => 'gray', 'icon' => 'heroicon-o-wrench'];
                    $pct = $data['totalExecutions'] > 0 ? round(($tb->count / $data['totalExecutions']) * 100, 1) : 0;
                @endphp
                <div class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                <x-filament::icon :icon="$info['icon']" class="w-4 h-4" />
                                {{ $info['name'] }}
                            </span>
                            <span class="text-xs font-bold text-zinc-500">{{ $pct }}%</span>
                        </div>
                        <div class="text-2xl font-black text-zinc-900 dark:text-zinc-50">
                            {{ number_format($tb->count) }} <span class="text-xs font-normal text-zinc-500">kali digunakan</span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                            Digunakan oleh <strong class="text-zinc-800 dark:text-zinc-200">{{ number_format($tb->unique_users) }}</strong> pengguna unik
                        </p>
                    </div>

                    {{-- Progress bar --}}
                    <div class="mt-4 w-full bg-zinc-100 dark:bg-zinc-800 h-2 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%; background-color: {{ $info['color'] }};"></div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 p-8 text-center bg-white dark:bg-zinc-900 border border-dashed border-zinc-300 dark:border-zinc-800 rounded-2xl">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Belum ada data eksekusi tool pada rentang tanggal ini.</p>
                </div>
            @endforelse
        </div>

        {{-- 2 Columns: Action breakdown & Top Target Roles --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Table: Actions breakdown --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm">
                <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-4 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-chart-pie" class="w-5 h-5 text-amber-500" />
                    Distribusi Aksi / Fitur
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-zinc-500 font-semibold border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="p-3">Tool</th>
                                <th class="p-3">Aksi / Fitur</th>
                                <th class="p-3 text-right">Frekuensi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                            @forelse($data['actionBreakdown'] as $ab)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                    <td class="p-3 font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ $toolNames[$ab->tool_name]['name'] ?? $ab->tool_name }}
                                    </td>
                                    <td class="p-3">
                                        <code class="px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-[11px] font-mono text-zinc-700 dark:text-zinc-300">
                                            {{ $ab->action }}
                                        </code>
                                    </td>
                                    <td class="p-3 text-right font-bold text-zinc-900 dark:text-zinc-50">
                                        {{ number_format($ab->count) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-4 text-center text-zinc-400">Tidak ada aksi tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Table: Top Target Roles --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm">
                <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-4 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-briefcase" class="w-5 h-5 text-amber-500" />
                    Posisi / Lowongan Paling Sering Dilatih
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-zinc-500 font-semibold border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="p-3">Target Posisi</th>
                                <th class="p-3">Perusahaan</th>
                                <th class="p-3 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                            @forelse($data['topRoles'] as $tr)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                    <td class="p-3 font-semibold text-zinc-900 dark:text-zinc-100 max-w-[200px] truncate" title="{{ $tr->target_position }}">
                                        {{ $tr->target_position }}
                                    </td>
                                    <td class="p-3 text-zinc-500 max-w-[150px] truncate" title="{{ $tr->company_name }}">
                                        {{ $tr->company_name ?: '-' }}
                                    </td>
                                    <td class="p-3 text-right font-bold text-amber-600 dark:text-amber-400">
                                        {{ number_format($tr->count) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-4 text-center text-zinc-400">Belum ada posisi tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Logs Table --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm">
            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-4 flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5 text-amber-500" />
                Riwayat Penggunaan Terbaru (Realtime Activity)
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-zinc-500 font-semibold border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="p-3">Waktu</th>
                            <th class="p-3">Tool</th>
                            <th class="p-3">Aksi</th>
                            <th class="p-3">Konteks Loker / Posisi</th>
                            <th class="p-3">IP Address</th>
                            <th class="p-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                        @forelse($data['recentLogs'] as $log)
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="p-3 text-zinc-500 whitespace-nowrap">
                                    {{ $log->created_at->format('d M H:i:s') }}
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ $toolNames[$log->tool_name]['name'] ?? $log->tool_name }}
                                    </span>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-[11px] font-mono">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="p-3 max-w-[240px] truncate" title="{{ $log->target_position }} {{ $log->company_name ? "({$log->company_name})" : '' }}">
                                    @if($log->target_position)
                                        <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $log->target_position }}</span>
                                        @if($log->company_name)
                                            <span class="text-zinc-400 text-[11px]">· {{ $log->company_name }}</span>
                                        @endif
                                    @elseif($log->post)
                                        <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $log->post->title }}</span>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="p-3 font-mono text-[11px] text-zinc-400 whitespace-nowrap">
                                    {{ $log->ip_address ?: '-' }}
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    @if($log->status === 'success')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300">
                                            Sukses
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300">
                                            Gagal
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-zinc-400">Belum ada riwayat aktivitas yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>

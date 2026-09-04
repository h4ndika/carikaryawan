<x-filament-panels::page>
    <div class="analytics-container" x-data="analyticsDashboard()" x-init="initChart()">
        {{-- Scoped Styles for Analytics Dashboard --}}
        <style>
            .analytics-container {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
                font-family: inherit;
            }
            .analytics-card {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 0.75rem;
                padding: 1.25rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                transition: all 0.2s ease;
            }
            .dark .analytics-card {
                background: #18181b;
                border-color: #27272a;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            }
            .analytics-header-card {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 1rem 1.25rem;
            }
            .period-btn-group {
                display: flex;
                flex-wrap: wrap;
                gap: 0.35rem;
                background: #f4f4f5;
                padding: 0.25rem;
                border-radius: 0.5rem;
            }
            .dark .period-btn-group {
                background: #27272a;
            }
            .period-btn {
                padding: 0.4rem 0.85rem;
                font-size: 0.8125rem;
                font-weight: 500;
                border-radius: 0.375rem;
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
                font-weight: 600;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
            }
            .dark .period-btn.active {
                background: #18181b;
                color: #f59e0b;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
            }
            .date-input {
                padding: 0.35rem 0.65rem;
                font-size: 0.8125rem;
                border: 1px solid #d4d4d8;
                border-radius: 0.375rem;
                background: #ffffff;
                color: #18181b;
            }
            .dark .date-input {
                background: #27272a;
                border-color: #3f3f46;
                color: #fafafa;
            }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 1rem;
            }
            .stat-card {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1.25rem;
            }
            .stat-value {
                font-size: 1.85rem;
                font-weight: 700;
                line-height: 1.2;
                color: #18181b;
            }
            .dark .stat-value {
                color: #fafafa;
            }
            .stat-label {
                font-size: 0.8125rem;
                font-weight: 500;
                color: #71717a;
                margin-top: 0.25rem;
            }
            .dark .stat-label {
                color: #a1a1aa;
            }
            .stat-icon-wrapper {
                width: 48px;
                height: 48px;
                border-radius: 0.75rem;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .stat-icon {
                width: 24px;
                height: 24px;
            }
            .icon-amber { background: #fef3c7; color: #d97706; }
            .dark .icon-amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
            .icon-blue { background: #dbeafe; color: #2563eb; }
            .dark .icon-blue { background: rgba(37, 99, 235, 0.15); color: #60a5fa; }
            .icon-emerald { background: #d1fae5; color: #059669; }
            .dark .icon-emerald { background: rgba(5, 150, 105, 0.15); color: #34d399; }
            .icon-purple { background: #ede9fe; color: #7c3aed; }
            .dark .icon-purple { background: rgba(124, 58, 237, 0.15); color: #a78bfa; }

            .dashboard-grid-2 {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 1.25rem;
            }
            @media (max-width: 1024px) {
                .dashboard-grid-2 {
                    grid-template-columns: 1fr;
                }
            }
            .table-responsive {
                width: 100%;
                overflow-x: auto;
            }
            .analytics-table {
                width: 100%;
                border-collapse: collapse;
                text-align: left;
                font-size: 0.875rem;
            }
            .analytics-table th {
                padding: 0.75rem 0.85rem;
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #71717a;
                border-bottom: 1px solid #e5e7eb;
            }
            .dark .analytics-table th {
                color: #a1a1aa;
                border-bottom-color: #27272a;
            }
            .analytics-table td {
                padding: 0.75rem 0.85rem;
                border-bottom: 1px solid #f4f4f5;
                vertical-align: middle;
            }
            .dark .analytics-table td {
                border-bottom-color: #27272a;
            }
            .rank-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 26px;
                height: 26px;
                border-radius: 9999px;
                font-weight: 700;
                font-size: 0.75rem;
            }
            .rank-1 { background: #fef08a; color: #854d0e; }
            .rank-2 { background: #e4e4e7; color: #3f3f46; }
            .rank-3 { background: #fed7aa; color: #9a3412; }
            .rank-other { background: #f4f4f5; color: #71717a; }
            .dark .rank-other { background: #27272a; color: #a1a1aa; }

            .post-thumb {
                width: 44px;
                height: 44px;
                border-radius: 0.5rem;
                object-fit: cover;
                background: #e4e4e7;
                flex-shrink: 0;
            }
            .pill-badge {
                display: inline-block;
                padding: 0.15rem 0.5rem;
                border-radius: 0.375rem;
                font-size: 0.75rem;
                font-weight: 500;
                background: #f4f4f5;
                color: #52525b;
            }
            .dark .pill-badge {
                background: #27272a;
                color: #d4d4d8;
            }
            .progress-bar-bg {
                background: #f4f4f5;
                border-radius: 9999px;
                height: 8px;
                overflow: hidden;
            }
            .dark .progress-bar-bg {
                background: #27272a;
            }
            .progress-bar-fill {
                height: 100%;
                border-radius: 9999px;
                background: #f59e0b;
                transition: width 0.3s ease;
            }
            .device-box {
                padding: 0.85rem 0.5rem;
                border-radius: 0.5rem;
                background: #f4f4f5;
                border: 1px solid #e4e4e7;
                text-align: center;
                transition: all 0.2s ease;
            }
            .dark .device-box {
                background: #27272a;
                border-color: #3f3f46;
            }
            .device-name {
                font-size: 0.75rem;
                font-weight: 500;
                color: #71717a;
            }
            .dark .device-name {
                color: #a1a1aa;
            }
            .device-percentage {
                font-size: 1.15rem;
                font-weight: 700;
                margin-top: 0.15rem;
                color: #18181b;
            }
            .dark .device-percentage {
                color: #fafafa;
            }
            .device-count {
                font-size: 0.7rem;
                color: #a1a1aa;
                margin-top: 0.1rem;
            }
            .dark .device-count {
                color: #71717a;
            }
        </style>

        @php
            $stats = $this->getSummaryStats();
            $chartData = $this->getDailyChartData();
            $topPosts = $this->getTopPosts(10);
            $referrers = $this->getTopReferrers(6);
            $devices = $this->getDeviceBreakdown();
        @endphp

        {{-- Filter & Date Toolbar --}}
        <div class="analytics-card analytics-header-card">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 10px; height: 10px; border-radius: 9999px; background: #10b981; animation: pulse 2s infinite;"></div>
                <div>
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 600;">Periode Analisis</h3>
                    <p style="margin: 0; font-size: 0.75rem; color: #71717a;">
                        Menampilkan data dari <strong>{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</strong>
                    </p>
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;">
                {{-- Period buttons --}}
                <div class="period-btn-group">
                    <button type="button" wire:click="setPeriod('today')" class="period-btn {{ $period === 'today' ? 'active' : '' }}">Hari Ini</button>
                    <button type="button" wire:click="setPeriod('yesterday')" class="period-btn {{ $period === 'yesterday' ? 'active' : '' }}">Kemarin</button>
                    <button type="button" wire:click="setPeriod('7days')" class="period-btn {{ $period === '7days' ? 'active' : '' }}">7 Hari</button>
                    <button type="button" wire:click="setPeriod('30days')" class="period-btn {{ $period === '30days' ? 'active' : '' }}">30 Hari</button>
                    <button type="button" wire:click="setPeriod('this_month')" class="period-btn {{ $period === 'this_month' ? 'active' : '' }}">Bulan Ini</button>
                    <button type="button" wire:click="setPeriod('last_month')" class="period-btn {{ $period === 'last_month' ? 'active' : '' }}">Bulan Lalu</button>
                </div>

                {{-- Custom Date Inputs --}}
                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <input type="date" wire:model.live="startDate" class="date-input" title="Tanggal Mulai" />
                    <span style="font-size: 0.75rem; color: #71717a;">s/d</span>
                    <input type="date" wire:model.live="endDate" class="date-input" title="Tanggal Selesai" />
                </div>
            </div>
        </div>

        {{-- Summary Stats Grid --}}
        <div class="stats-grid">
            {{-- Total Pageviews --}}
            <div class="analytics-card stat-card">
                <div>
                    <div class="stat-value">{{ number_format($stats['total_views']) }}</div>
                    <div class="stat-label">Total Pageviews</div>
                    <div style="font-size: 0.7rem; color: #10b981; margin-top: 0.25rem;">
                        Tayangan halaman dalam periode
                    </div>
                </div>
                <div class="stat-icon-wrapper icon-amber">
                    <x-heroicon-o-eye class="stat-icon" />
                </div>
            </div>

            {{-- Unique Visitors --}}
            <div class="analytics-card stat-card">
                <div>
                    <div class="stat-value">{{ number_format($stats['unique_visitors']) }}</div>
                    <div class="stat-label">Pengunjung Unik</div>
                    <div style="font-size: 0.7rem; color: #3b82f6; margin-top: 0.25rem;">
                        Berdasarkan IP unik (GDPR hashed)
                    </div>
                </div>
                <div class="stat-icon-wrapper icon-blue">
                    <x-heroicon-o-users class="stat-icon" />
                </div>
            </div>

            {{-- Article / Post Views --}}
            <div class="analytics-card stat-card">
                <div>
                    <div class="stat-value">{{ number_format($stats['post_views']) }}</div>
                    <div class="stat-label">Pembaca Artikel / Post</div>
                    <div style="font-size: 0.7rem; color: #059669; margin-top: 0.25rem;">
                        {{ $stats['total_views'] > 0 ? round(($stats['post_views'] / $stats['total_views']) * 100, 1) : 0 }}% dari total kunjungan
                    </div>
                </div>
                <div class="stat-icon-wrapper icon-emerald">
                    <x-heroicon-o-document-text class="stat-icon" />
                </div>
            </div>

            {{-- Views Hari Ini vs Kemarin --}}
            <div class="analytics-card stat-card">
                <div>
                    <div class="stat-value">{{ number_format($stats['today_views']) }}</div>
                    <div class="stat-label">Tayangan Hari Ini</div>
                    <div style="font-size: 0.7rem; color: #71717a; margin-top: 0.25rem;">
                        Kemarin: <strong>{{ number_format($stats['yesterday_views']) }}</strong> views
                    </div>
                </div>
                <div class="stat-icon-wrapper icon-purple">
                    <x-heroicon-o-chart-bar-square class="stat-icon" />
                </div>
            </div>
        </div>

        {{-- Interactive Chart Card --}}
        <div class="analytics-card" style="padding: 1.5rem;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Grafik Pengunjung Per Hari</h3>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.8125rem; color: #71717a;">Perbandingan jumlah pengunjung unik dan total pageviews harian.</p>
                </div>

                {{-- Chart legend labels --}}
                <div style="display: flex; align-items: center; gap: 1.25rem; font-size: 0.8125rem;">
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background: #f59e0b; display: inline-block;"></span>
                        <span>Total Pageviews</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background: #3b82f6; display: inline-block;"></span>
                        <span>Pengunjung Unik</span>
                    </div>
                </div>
            </div>

            <div style="position: relative; height: 320px; width: 100%;">
                <canvas id="dailyAnalyticsChart"></canvas>
            </div>
        </div>

        {{-- 2-Columns Bottom Section --}}
        <div class="dashboard-grid-2">
            {{-- Left: Top Viewed Posts --}}
            <div class="analytics-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div>
                        <h3 style="margin: 0; font-size: 1.05rem; font-weight: 600;">Post Paling Banyak Dilihat</h3>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.8125rem; color: #71717a;">Peringkat artikel dan tempat ulasan terpopuler.</p>
                    </div>
                    <span class="pill-badge">Top 10</span>
                </div>

                <div class="table-responsive">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th style="width: 45px;">No</th>
                                <th>Post / Tempat</th>
                                <th>Kategori</th>
                                <th style="text-align: right;">Tayangan Periode</th>
                                <th style="text-align: right;">Total Views</th>
                                <th style="text-align: center; width: 60px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topPosts as $post)
                                <tr>
                                    <td>
                                        <span class="rank-badge {{ $post['rank'] === 1 ? 'rank-1' : ($post['rank'] === 2 ? 'rank-2' : ($post['rank'] === 3 ? 'rank-3' : 'rank-other')) }}">
                                            {{ $post['rank'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            @if (!empty($post['thumbnail']))
                                                <img src="{{ $post['thumbnail'] }}" alt="{{ $post['title'] }}" class="post-thumb" loading="lazy" />
                                            @else
                                                <div class="post-thumb" style="display: flex; align-items: center; justify-content: center;">
                                                    <x-heroicon-o-photo style="width: 20px; height: 20px; color: #9ca3af;" />
                                                </div>
                                            @endif
                                            <div>
                                                <a href="{{ $post['url'] }}" target="_blank" style="font-weight: 600; color: inherit; text-decoration: none;" onmouseover="this.style.color='#f59e0b'" onmouseout="this.style.color='inherit'">
                                                    {{ Str::limit($post['title'], 45) }}
                                                </a>
                                                <div style="font-size: 0.75rem; color: #71717a;">
                                                    {{ $post['city'] }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="pill-badge">{{ $post['category'] }}</span>
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #f59e0b;">
                                        {{ number_format($post['range_views']) }}
                                    </td>
                                    <td style="text-align: right; font-size: 0.8125rem; color: #71717a;">
                                        {{ number_format($post['total_views']) }}
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="{{ route('filament.rahasia.resources.posts.edit', ['record' => $post['post_id']]) }}" target="_blank" title="Edit Post di Admin" style="color: #6b7280; display: inline-flex; align-items: center; padding: 0.25rem; border-radius: 0.25rem;">
                                            <x-heroicon-o-pencil-square style="width: 18px; height: 18px;" />
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2rem; color: #71717a;">
                                        Belum ada data kunjungan post pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Right: Traffic Sources & Devices --}}
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                {{-- Referrer Sources --}}
                <div class="analytics-card">
                    <h3 style="margin: 0 0 0.25rem 0; font-size: 1.05rem; font-weight: 600;">Sumber Trafik (Referrer)</h3>
                    <p style="margin: 0 0 1rem 0; font-size: 0.8125rem; color: #71717a;">Asal kedatangan pengunjung website.</p>

                    <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                        @forelse ($referrers as $ref)
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 0.25rem;">
                                    <span style="font-weight: 500;">{{ $ref['domain'] }}</span>
                                    <span style="color: #71717a;">{{ number_format($ref['count']) }} views ({{ $ref['percentage'] }}%)</span>
                                </div>
                                <div class="progress-bar-bg">
                                    <div class="progress-bar-fill" style="width: {{ min(100, $ref['percentage']) }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; padding: 1.5rem; color: #71717a; font-size: 0.875rem;">
                                Belum ada data sumber perujuk (referrer).
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Device Breakdown --}}
                <div class="analytics-card">
                    <h3 style="margin: 0 0 0.25rem 0; font-size: 1.05rem; font-weight: 600;">Perangkat Pengunjung</h3>
                    <p style="margin: 0 0 1rem 0; font-size: 0.8125rem; color: #71717a;">Distribusi perangkat yang digunakan pembaca.</p>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; text-align: center;">
                        <div class="device-box">
                            <x-heroicon-o-device-phone-mobile style="width: 24px; height: 24px; margin: 0 auto 0.35rem auto; color: #f59e0b;" />
                            <div class="device-name">Mobile</div>
                            <div class="device-percentage">{{ $devices['mobile']['percentage'] }}%</div>
                            <div class="device-count">{{ number_format($devices['mobile']['count']) }}</div>
                        </div>

                        <div class="device-box">
                            <x-heroicon-o-computer-desktop style="width: 24px; height: 24px; margin: 0 auto 0.35rem auto; color: #3b82f6;" />
                            <div class="device-name">Desktop</div>
                            <div class="device-percentage">{{ $devices['desktop']['percentage'] }}%</div>
                            <div class="device-count">{{ number_format($devices['desktop']['count']) }}</div>
                        </div>

                        <div class="device-box">
                            <x-heroicon-o-device-tablet style="width: 24px; height: 24px; margin: 0 auto 0.35rem auto; color: #10b981;" />
                            <div class="device-name">Tablet</div>
                            <div class="device-percentage">{{ $devices['tablet']['percentage'] }}%</div>
                            <div class="device-count">{{ number_format($devices['tablet']['count']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Load Chart.js and Alpine Component --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        function analyticsDashboard() {
            return {
                chart: null,
                initChart() {
                    const ctx = document.getElementById('dailyAnalyticsChart');
                    if (!ctx) return;

                    const chartData = @json($chartData);

                    // Check if dark mode
                    const isDark = document.documentElement.classList.contains('dark');
                    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
                    const textColor = isDark ? '#a1a1aa' : '#71717a';

                    if (this.chart) {
                        this.chart.destroy();
                    }

                    this.chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: chartData.labels,
                            datasets: [
                                {
                                    label: 'Total Pageviews',
                                    data: chartData.views,
                                    borderColor: '#f59e0b',
                                    backgroundColor: 'rgba(245, 158, 11, 0.12)',
                                    borderWidth: 2.5,
                                    pointRadius: 3.5,
                                    pointHoverRadius: 6,
                                    pointBackgroundColor: '#f59e0b',
                                    fill: true,
                                    tension: 0.35,
                                },
                                {
                                    label: 'Pengunjung Unik',
                                    data: chartData.visitors,
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.08)',
                                    borderWidth: 2,
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                    pointBackgroundColor: '#3b82f6',
                                    fill: true,
                                    tension: 0.35,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: isDark ? '#27272a' : '#18181b',
                                    titleColor: '#fafafa',
                                    bodyColor: '#fafafa',
                                    borderColor: isDark ? '#3f3f46' : '#e4e4e7',
                                    borderWidth: 1,
                                    padding: 10,
                                    cornerRadius: 8,
                                    boxPadding: 4,
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        color: gridColor,
                                        drawBorder: false,
                                    },
                                    ticks: {
                                        color: textColor,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: gridColor,
                                        drawBorder: false,
                                    },
                                    ticks: {
                                        color: textColor,
                                        precision: 0,
                                        font: {
                                            size: 11
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Listen for Livewire updates
                    window.addEventListener('analytics-updated', () => {
                        setTimeout(() => {
                            this.initChart();
                        }, 100);
                    });
                }
            };
        }
    </script>
</x-filament-panels::page>

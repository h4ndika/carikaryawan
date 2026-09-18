<?php

namespace App\Filament\Rahasia\Pages;

use App\Models\ToolUsage;
use BackedEnum;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class ToolAnalyticsDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Tracking Tools AI';

    protected static ?string $title = 'Statistik & Penggunaan Tools AI';

    protected static ?string $slug = 'tool-analytics';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.rahasia.pages.tool-analytics-dashboard';

    public string $period = '7days';

    public string $startDate = '';

    public string $endDate = '';

    public string $selectedTool = 'all';

    public function mount(): void
    {
        $this->setPeriod('7days');
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $now = now();

        switch ($period) {
            case 'today':
                $this->startDate = $now->toDateString();
                $this->endDate = $now->toDateString();
                break;
            case 'yesterday':
                $yesterday = $now->copy()->subDay();
                $this->startDate = $yesterday->toDateString();
                $this->endDate = $yesterday->toDateString();
                break;
            case '7days':
                $this->startDate = $now->copy()->subDays(6)->toDateString();
                $this->endDate = $now->toDateString();
                break;
            case '30days':
                $this->startDate = $now->copy()->subDays(29)->toDateString();
                $this->endDate = $now->toDateString();
                break;
            case 'this_month':
                $this->startDate = $now->copy()->startOfMonth()->toDateString();
                $this->endDate = $now->toDateString();
                break;
            case 'all_time':
                $this->startDate = '2025-01-01';
                $this->endDate = $now->toDateString();
                break;
        }
    }

    public function applyFilter(): void
    {
        Notification::make()
            ->title('Data diperbarui')
            ->success()
            ->send();
    }

    public function getViewData(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $baseQuery = ToolUsage::query()->whereBetween('created_at', [$start, $end]);

        if ($this->selectedTool !== 'all') {
            $baseQuery->where('tool_name', $this->selectedTool);
        }

        // Summary KPI
        $totalExecutions = (clone $baseQuery)->count();
        $uniqueUsers = (clone $baseQuery)->distinct('ip_address')->count('ip_address');
        $successfulExecutions = (clone $baseQuery)->where('status', 'success')->count();
        $successRate = $totalExecutions > 0 ? round(($successfulExecutions / $totalExecutions) * 100, 1) : 100;

        // Breakdown per Tool
        $toolBreakdown = (clone $baseQuery)
            ->select('tool_name', DB::raw('count(*) as count'), DB::raw('count(distinct ip_address) as unique_users'))
            ->groupBy('tool_name')
            ->orderByDesc('count')
            ->get();

        // Breakdown per Action
        $actionBreakdown = (clone $baseQuery)
            ->select('tool_name', 'action', DB::raw('count(*) as count'))
            ->groupBy('tool_name', 'action')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Top Targeted Roles / Positions
        $topRoles = (clone $baseQuery)
            ->whereNotNull('target_position')
            ->where('target_position', '!=', '')
            ->select('target_position', 'company_name', DB::raw('count(*) as count'))
            ->groupBy('target_position', 'company_name')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        // Daily Usage Trend Chart Data
        $dailyTrends = (clone $baseQuery)
            ->select(DB::raw('DATE(created_at) as date'), 'tool_name', DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'), 'tool_name')
            ->orderBy('date', 'asc')
            ->get();

        // Recent Logs
        $recentLogs = (clone $baseQuery)
            ->with('post:id,title,slug')
            ->latest()
            ->limit(15)
            ->get();

        return [
            'totalExecutions' => $totalExecutions,
            'uniqueUsers' => $uniqueUsers,
            'successfulExecutions' => $successfulExecutions,
            'successRate' => $successRate,
            'toolBreakdown' => $toolBreakdown,
            'actionBreakdown' => $actionBreakdown,
            'topRoles' => $topRoles,
            'dailyTrends' => $dailyTrends,
            'recentLogs' => $recentLogs,
        ];
    }
}

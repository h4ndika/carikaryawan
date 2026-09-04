<?php

namespace App\Filament\Rahasia\Pages;

use App\Models\PageView;
use App\Models\Post;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class AnalyticsDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Statistik Pengunjung';

    protected static ?string $title = 'Analitik & Statistik Pengunjung';

    protected static ?string $slug = 'analytics';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.rahasia.pages.analytics-dashboard';

    public string $period = '7days';

    public string $startDate = '';

    public string $endDate = '';

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
            case 'last_month':
                $lastMonth = $now->copy()->subMonth();
                $this->startDate = $lastMonth->copy()->startOfMonth()->toDateString();
                $this->endDate = $lastMonth->copy()->endOfMonth()->toDateString();
                break;
            case 'custom':
                if (empty($this->startDate)) {
                    $this->startDate = $now->copy()->subDays(6)->toDateString();
                }
                if (empty($this->endDate)) {
                    $this->endDate = $now->toDateString();
                }
                break;
        }

        $this->dispatch('analytics-updated');
    }

    public function updatedStartDate(): void
    {
        $this->period = 'custom';
        $this->dispatch('analytics-updated');
    }

    public function updatedEndDate(): void
    {
        $this->period = 'custom';
        $this->dispatch('analytics-updated');
    }

    /**
     * Get KPI summary cards
     */
    public function getSummaryStats(): array
    {
        $start = $this->startDate;
        $end = $this->endDate;

        // Total pageviews
        $totalViews = PageView::query()
            ->whereBetween('viewed_date', [$start, $end])
            ->count();

        // Unique visitors (distinct IP hash)
        $uniqueVisitors = PageView::query()
            ->whereBetween('viewed_date', [$start, $end])
            ->distinct('ip_hash')
            ->count('ip_hash');

        // Post views specifically
        $postViews = PageView::query()
            ->whereBetween('viewed_date', [$start, $end])
            ->whereNotNull('post_id')
            ->count();

        // Today's views vs yesterday
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $todayViews = PageView::query()
            ->where('viewed_date', $today)
            ->count();

        $yesterdayViews = PageView::query()
            ->where('viewed_date', $yesterday)
            ->count();

        // Average views per visitor
        $avgViewsPerVisitor = $uniqueVisitors > 0 ? round($totalViews / $uniqueVisitors, 1) : 0;

        return [
            'total_views' => $totalViews,
            'unique_visitors' => $uniqueVisitors,
            'post_views' => $postViews,
            'today_views' => $todayViews,
            'yesterday_views' => $yesterdayViews,
            'avg_views_per_visitor' => $avgViewsPerVisitor,
        ];
    }

    /**
     * Get daily visitor chart data (for Line Chart)
     */
    public function getDailyChartData(): array
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        $dbData = PageView::query()
            ->select(
                'viewed_date',
                DB::raw('COUNT(*) as total_views'),
                DB::raw('COUNT(DISTINCT ip_hash) as unique_visitors')
            )
            ->whereBetween('viewed_date', [$this->startDate, $this->endDate])
            ->groupBy('viewed_date')
            ->orderBy('viewed_date', 'asc')
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->viewed_date)->toDateString());

        $labels = [];
        $viewsSeries = [];
        $visitorsSeries = [];

        $current = $start->copy();
        while ($current->lte($end)) {
            $dateStr = $current->toDateString();
            $labels[] = $current->isoFormat('D MMM');

            $row = $dbData->get($dateStr);
            $viewsSeries[] = $row ? (int) $row->total_views : 0;
            $visitorsSeries[] = $row ? (int) $row->unique_visitors : 0;

            $current->addDay();
        }

        return [
            'labels' => $labels,
            'views' => $viewsSeries,
            'visitors' => $visitorsSeries,
        ];
    }

    /**
     * Get most viewed posts in selected date range
     */
    public function getTopPosts(int $limit = 10): array
    {
        $rows = PageView::query()
            ->select('post_id', DB::raw('COUNT(*) as range_views'), DB::raw('COUNT(DISTINCT ip_hash) as unique_readers'))
            ->whereBetween('viewed_date', [$this->startDate, $this->endDate])
            ->whereNotNull('post_id')
            ->groupBy('post_id')
            ->orderByDesc('range_views')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            // Fallback: If no page_views recorded yet, show top posts by views_count
            return Post::query()
                ->published()
                ->with(['category', 'city'])
                ->orderByDesc('views_count')
                ->limit($limit)
                ->get()
                ->map(fn ($post, $index) => [
                    'rank' => $index + 1,
                    'post_id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'category' => $post->category?->name ?? '-',
                    'city' => $post->city?->name ?? '-',
                    'range_views' => (int) $post->views_count,
                    'unique_readers' => (int) round($post->views_count * 0.75),
                    'total_views' => (int) $post->views_count,
                    'thumbnail' => $post->primary_image_url ?: $post->thumbnail,
                    'url' => $post->url(),
                ])
                ->all();
        }

        $postIds = $rows->pluck('post_id')->all();
        $posts = Post::query()
            ->with(['category', 'city'])
            ->whereIn('id', $postIds)
            ->get()
            ->keyBy('id');

        $result = [];
        $rank = 1;
        foreach ($rows as $row) {
            $post = $posts->get($row->post_id);
            if (!$post) {
                continue;
            }

            $result[] = [
                'rank' => $rank++,
                'post_id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'category' => $post->category?->name ?? '-',
                'city' => $post->city?->name ?? '-',
                'range_views' => (int) $row->range_views,
                'unique_readers' => (int) $row->unique_readers,
                'total_views' => (int) $post->views_count,
                'thumbnail' => $post->primary_image_url ?: $post->thumbnail,
                'url' => $post->url(),
            ];
        }

        return $result;
    }

    /**
     * Get top traffic referrers
     */
    public function getTopReferrers(int $limit = 6): array
    {
        $rows = PageView::query()
            ->select('referrer_domain', DB::raw('COUNT(*) as total'))
            ->whereBetween('viewed_date', [$this->startDate, $this->endDate])
            ->groupBy('referrer_domain')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        $totalViews = max(1, PageView::query()->whereBetween('viewed_date', [$this->startDate, $this->endDate])->count());

        return $rows->map(function ($row) use ($totalViews) {
            $domain = $row->referrer_domain ?: 'Direct / Langsung';
            $count = (int) $row->total;
            $percentage = round(($count / $totalViews) * 100, 1);

            return [
                'domain' => $domain,
                'count' => $count,
                'percentage' => $percentage,
            ];
        })->all();
    }

    /**
     * Get device breakdown
     */
    public function getDeviceBreakdown(): array
    {
        $rows = PageView::query()
            ->select('device', DB::raw('COUNT(*) as total'))
            ->whereBetween('viewed_date', [$this->startDate, $this->endDate])
            ->groupBy('device')
            ->pluck('total', 'device')
            ->all();

        $total = max(1, array_sum($rows));

        return [
            'mobile' => [
                'count' => $rows['mobile'] ?? 0,
                'percentage' => round((($rows['mobile'] ?? 0) / $total) * 100, 1),
            ],
            'desktop' => [
                'count' => $rows['desktop'] ?? 0,
                'percentage' => round((($rows['desktop'] ?? 0) / $total) * 100, 1),
            ],
            'tablet' => [
                'count' => $rows['tablet'] ?? 0,
                'percentage' => round((($rows['tablet'] ?? 0) / $total) * 100, 1),
            ],
        ];
    }
}

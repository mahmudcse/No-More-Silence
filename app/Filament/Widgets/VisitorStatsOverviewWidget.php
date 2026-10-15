<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\VisitTrackerService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VisitorStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $tracker = app(VisitTrackerService::class);
        $weekly = $tracker->getWeeklyTrend();

        $activeNow = $tracker->getActiveVisitorsCount();
        $todayPv = $tracker->getTodayPageviews();
        $todayUv = $tracker->getTodayUniques();
        $weeklyPv = $weekly['total_weekly_views'];

        return [
            Stat::make('Active Now', number_format($activeNow))
                ->description('Active in the last 15 mins')
                ->descriptionIcon('heroicon-m-signal')
                ->color($activeNow > 0 ? 'success' : 'gray'),

            Stat::make('Today Page Views', number_format($todayPv))
                ->description('Total hits today')
                ->descriptionIcon('heroicon-m-eye')
                ->color('warning'),

            Stat::make('Today Unique Visitors', number_format($todayUv))
                ->description('Distinct users today')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('This Week (7 Days)', number_format($weeklyPv))
                ->description('Total page views over 7 days')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart($weekly['pageviews'])
                ->color('primary'),
        ];
    }
}

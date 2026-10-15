<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\VisitTrackerService;
use Filament\Widgets\ChartWidget;

class VisitorChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Website Traffic Trend (Last 7 Days)';
    }

    protected function getData(): array
    {
        $tracker = app(VisitTrackerService::class);
        $weekly = $tracker->getWeeklyTrend();

        return [
            'datasets' => [
                [
                    'label' => 'Page Views',
                    'data' => $weekly['pageviews'],
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Unique Visitors',
                    'data' => $weekly['uniques'],
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $weekly['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

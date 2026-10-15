<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DailyVisitStat;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;
use Throwable;

class VisitTrackerService
{
    private const ACTIVE_TTL_MINUTES = 15;

    private const REDIS_STATS_TTL_DAYS = 14;

    /**
     * Record an incoming page view into Redis.
     */
    public function recordVisit(Request $request): void
    {
        if (! $request->isMethod('GET')) {
            return;
        }

        if ($this->isExcludedPath($request)) {
            return;
        }

        if ($this->isBot($request)) {
            return;
        }

        try {
            $today = Carbon::today()->toDateString();
            $visitorHash = $this->generateVisitorHash($request, $today);
            $path = $this->sanitizePath($request);
            $nowTs = Carbon::now()->timestamp;
            $ttlSeconds = self::REDIS_STATS_TTL_DAYS * 86400;

            // Pipeline commands for minimal network round-trips
            Redis::pipeline(function ($pipe) use ($today, $visitorHash, $path, $nowTs, $ttlSeconds): void {
                // 1. Active Visitors (Sorted Set with timestamp)
                $pipe->zadd('visits:active', $nowTs, $visitorHash);

                // 2. Daily Pageviews counter
                $pvKey = "visits:pv:{$today}";
                $pipe->incr($pvKey);
                $pipe->expire($pvKey, $ttlSeconds);

                // 3. Unique Visitors (HyperLogLog)
                $uvKey = "visits:uv:{$today}";
                $pipe->pfadd($uvKey, [$visitorHash]);
                $pipe->expire($uvKey, $ttlSeconds);

                // 4. Top Pages (Sorted Set)
                $pagesKey = "visits:pages:{$today}";
                $pipe->zincrby($pagesKey, 1, $path);
                $pipe->expire($pagesKey, $ttlSeconds);
            });
        } catch (Throwable) {
            // Fail gracefully if Redis is temporarily unreachable so visitors are unaffected
        }
    }

    /**
     * Get count of unique active visitors within the last 15 minutes.
     */
    public function getActiveVisitorsCount(): int
    {
        try {
            $cutoff = Carbon::now()->subMinutes(self::ACTIVE_TTL_MINUTES)->timestamp;
            Redis::zremrangebyscore('visits:active', '-inf', (string) $cutoff);

            return (int) Redis::zcard('visits:active');
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Get total page views recorded today.
     */
    public function getTodayPageviews(): int
    {
        try {
            $today = Carbon::today()->toDateString();

            return (int) Redis::get("visits:pv:{$today}");
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Get total unique visitors recorded today.
     */
    public function getTodayUniques(): int
    {
        try {
            $today = Carbon::today()->toDateString();

            return (int) Redis::pfcount("visits:uv:{$today}");
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Get the 7-day traffic trend combining database records with live Redis counts for today.
     *
     * @return array{
     *     labels: array<int, string>,
     *     pageviews: array<int, int>,
     *     uniques: array<int, int>,
     *     total_weekly_views: int,
     *     total_weekly_uniques: int
     * }
     */
    public function getWeeklyTrend(): array
    {
        $labels = [];
        $pageviews = [];
        $uniques = [];

        $today = Carbon::today();
        $startDate = $today->copy()->subDays(6);

        // Fetch existing historical records from PostgreSQL
        $dbStats = DailyVisitStat::query()
            ->whereBetween('date', [$startDate->toDateString(), $today->toDateString()])
            ->get()
            ->keyBy(fn (DailyVisitStat $stat): string => $stat->date->toDateString());

        for ($i = 6; $i >= 0; $i--) {
            $currentDay = $today->copy()->subDays($i);
            $dateString = $currentDay->toDateString();
            $labels[] = $currentDay->format('M d');

            if ($currentDay->isToday()) {
                // Use live Redis stats for today
                $pv = $this->getTodayPageviews();
                $uv = $this->getTodayUniques();
            } elseif ($dbStats->has($dateString)) {
                $stat = $dbStats->get($dateString);
                $pv = $stat->pageviews;
                $uv = $stat->unique_visitors;
            } else {
                // Fallback to Redis if still within TTL
                try {
                    $pv = (int) Redis::get("visits:pv:{$dateString}");
                    $uv = (int) Redis::pfcount("visits:uv:{$dateString}");
                } catch (Throwable) {
                    $pv = 0;
                    $uv = 0;
                }
            }

            $pageviews[] = $pv;
            $uniques[] = $uv;
        }

        return [
            'labels' => $labels,
            'pageviews' => $pageviews,
            'uniques' => $uniques,
            'total_weekly_views' => array_sum($pageviews),
            'total_weekly_uniques' => array_sum($uniques),
        ];
    }

    /**
     * Flush a specific day's Redis metrics into the PostgreSQL database.
     */
    public function flushToDatabase(?CarbonInterface $date = null): ?DailyVisitStat
    {
        $target = $date ?? Carbon::today()->subDay();
        $dateString = $target->toDateString();

        try {
            $pv = (int) Redis::get("visits:pv:{$dateString}");
            $uv = (int) Redis::pfcount("visits:uv:{$dateString}");
            /** @var array<string, int> $topPages */
            $topPages = Redis::zrevrange("visits:pages:{$dateString}", 0, 9, true) ?: [];

            // If nothing in Redis for that day and record exists in DB, keep it
            if ($pv === 0 && $uv === 0) {
                return DailyVisitStat::whereDate('date', $dateString)->first();
            }

            return DailyVisitStat::updateOrCreate(
                ['date' => $dateString],
                [
                    'pageviews' => $pv,
                    'unique_visitors' => $uv,
                    'top_pages' => $topPages,
                ]
            );
        } catch (Throwable) {
            return null;
        }
    }

    private function isExcludedPath(Request $request): bool
    {
        return $request->is([
            'admin*',
            'livewire*',
            'storage*',
            'up',
            '_debugbar*',
            'sanctum*',
            'api/*',
            'filament*',
            '*.js',
            '*.css',
            '*.png',
            '*.jpg',
            '*.jpeg',
            '*.svg',
            '*.ico',
            '*.woff*',
        ]);
    }

    private function isBot(Request $request): bool
    {
        $userAgent = (string) $request->userAgent();

        if (blank($userAgent)) {
            return true;
        }

        return (bool) preg_match(
            '/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|preview|curl|wget|lighthouse/i',
            $userAgent
        );
    }

    private function generateVisitorHash(Request $request, string $dateString): string
    {
        $ip = (string) $request->ip();
        $ua = (string) $request->userAgent();
        $key = (string) config('app.key');

        return hash('sha256', "{$ip}|{$ua}|{$key}|{$dateString}");
    }

    private function sanitizePath(Request $request): string
    {
        $path = '/'.ltrim($request->path(), '/');

        return strlen($path) > 100 ? substr($path, 0, 100) : $path;
    }
}

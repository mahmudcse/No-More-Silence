<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\VisitTrackerService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AggregateDailyVisitStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:aggregate-visits {--date= : The date to aggregate (YYYY-MM-DD), defaults to yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate Redis visitor metrics into daily database historical records';

    public function handle(VisitTrackerService $tracker): int
    {
        $dateOption = $this->option('date');
        $date = is_string($dateOption) ? Carbon::parse($dateOption) : Carbon::today()->subDay();

        $this->info("Aggregating visit stats for {$date->toDateString()}...");
        $stat = $tracker->flushToDatabase($date);

        if ($stat) {
            $this->info("Successfully aggregated: {$stat->pageviews} pageviews, {$stat->unique_visitors} unique visitors.");
        } else {
            $this->warn('No visit data found or error occurred.');
        }

        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StatsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateStatsSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:snapshot {--date= : The snapshot date (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily stats snapshots for national and district aggregates';

    /**
     * Execute the console command.
     */
    public function handle(StatsService $statsService): int
    {
        $dateOption = $this->option('date');
        $date = is_string($dateOption) ? Carbon::parse($dateOption) : Carbon::today();

        $this->info("Generating stats snapshot for {$date->toDateString()}...");
        $statsService->generateSnapshots($date);
        $this->info('Stats snapshots generated successfully.');

        return self::SUCCESS;
    }
}

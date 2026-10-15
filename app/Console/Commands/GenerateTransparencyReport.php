<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TransparencyService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateTransparencyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:transparency-report {--year= : Report year (default: current)} {--month= : Report month (default: current)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and output monthly transparency metrics for verification, rejection, and appeals';

    /**
     * Execute the console command.
     */
    public function handle(TransparencyService $transparencyService): int
    {
        $year = $this->option('year') !== null
            ? (int) $this->option('year')
            : (int) Carbon::now()->year;

        $month = $this->option('month') !== null
            ? (int) $this->option('month')
            : (int) Carbon::now()->month;

        $this->info("Generating Transparency Report for {$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT).'...');

        $metrics = $transparencyService->getMonthlyReport($year, $month);

        $this->newLine();
        $this->line("=== TRANSPARENCY REPORT: {$metrics['period']} ===");
        $this->newLine();

        $this->table(
            ['Metric Category', 'Detail', 'Count / Value'],
            [
                ['Submissions', 'Total Received', $metrics['submissions']['total']],
                ['Submissions', 'Anonymous Submissions', $metrics['submissions']['anonymous']],
                ['Submissions', 'Authenticated Reporters', $metrics['submissions']['authenticated']],
                ['Submissions', 'Web Share Target', $metrics['submissions']['web_share_target']],
                ['Outcomes', 'Verified & Published', $metrics['outcomes']['verified']],
                ['Outcomes', 'Rejected', $metrics['outcomes']['rejected']],
                ['Outcomes', 'Duplicates Merged', $metrics['outcomes']['duplicate']],
                ['Outcomes', 'Spam Filtered', $metrics['outcomes']['spam']],
                ['Outcomes', 'Retracted Reports', $metrics['outcomes']['retracted']],
                ['Turnaround', 'Avg Verification Duration', "{$metrics['turnaround']['avg_verification_hours']} hours"],
                ['Turnaround', 'Total Review Actions Logged', $metrics['turnaround']['total_reviews_logged']],
                ['Appeals (Right of Reply)', 'Appeals Filed', $metrics['appeals']['filed']],
                ['Appeals (Right of Reply)', 'Appeals Accepted', $metrics['appeals']['accepted']],
                ['Appeals (Right of Reply)', 'Appeals Rejected', $metrics['appeals']['rejected']],
                ['Appeals (Right of Reply)', 'Pending Adjudication', $metrics['appeals']['pending']],
                ['Appeals (Right of Reply)', 'Right of Reply Statements', $metrics['appeals']['right_of_reply_published']],
            ]
        );

        $this->newLine();
        $this->info('Transparency report generated successfully.');

        return self::SUCCESS;
    }
}

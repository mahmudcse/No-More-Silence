<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\WorkflowStatus;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

class CleanupRetentionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:cleanup-retention {--temp-hours=24 : Hours to retain temporary share target uploads} {--spam-days=90 : Days to retain spam reports}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired temporary uploads, stale spam reports, and expired authentication tokens';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tempHours = (int) $this->option('temp-hours');
        $spamDays = (int) $this->option('spam-days');

        $this->info('Starting retention cleanup...');

        // 1. Clean up temporary share-target uploads
        $deletedTempDirs = 0;
        $disk = Storage::disk('local');
        if ($disk->exists('temp-shares')) {
            $directories = $disk->directories('temp-shares');
            $cutoffTimestamp = Carbon::now()->subHours($tempHours)->timestamp;

            foreach ($directories as $dir) {
                $lastModified = $disk->lastModified($dir);
                if ($lastModified < $cutoffTimestamp) {
                    $disk->deleteDirectory($dir);
                    $deletedTempDirs++;
                }
            }
        }
        $this->line("• Removed {$deletedTempDirs} expired temporary share upload folder(s).");

        // 2. Prune old spam reports
        $spamCutoff = Carbon::now()->subDays($spamDays);
        $prunedSpamCount = Report::query()
            ->where('workflow_status', WorkflowStatus::Spam)
            ->where('updated_at', '<', $spamCutoff)
            ->delete();
        $this->line("• Pruned {$prunedSpamCount} stale spam report(s) older than {$spamDays} days.");

        // 3. Prune expired tokens
        $prunedTokens = PersonalAccessToken::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', Carbon::now())
            ->delete();
        $this->line("• Pruned {$prunedTokens} expired authentication token(s).");

        $this->info('Retention cleanup completed successfully.');

        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\WorkflowStatus;
use App\Events\ReportVerified;
use App\Models\Report;
use App\Models\User;
use App\Support\ReportWorkflow;

final class VerifyReport
{
    public function __construct(private readonly ReportWorkflow $workflow) {}

    /**
     * @param  array<string, bool>  $checklist
     */
    public function handle(Report $report, User $admin, array $checklist, ?string $note = null): Report
    {
        $report = $this->workflow->transition($report, WorkflowStatus::Verified, $admin, $checklist, $note);

        $report->forceFill([
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ])->save();

        ReportVerified::dispatch($report);

        return $report;
    }
}

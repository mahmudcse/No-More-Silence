<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\WorkflowStatus;
use App\Models\Report;
use App\Models\User;
use App\Support\ReportWorkflow;

final class RejectReport
{
    public function __construct(private readonly ReportWorkflow $workflow) {}

    public function handle(Report $report, User $admin, string $reason, ?string $note = null): Report
    {
        if ($report->workflow_status === WorkflowStatus::Submitted) {
            $report = $this->workflow->transition($report, WorkflowStatus::Triaged, $admin, note: 'Triaged during rejection');
        }

        $report = $this->workflow->transition($report, WorkflowStatus::Rejected, $admin, [], $note);
        $report->forceFill(['rejected_reason' => $reason])->save();

        return $report;
    }
}

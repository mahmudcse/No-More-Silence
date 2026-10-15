<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\WorkflowStatus;
use App\Models\Report;
use App\Models\User;
use App\Support\ReportWorkflow;

final class RequestInfo
{
    public function __construct(private readonly ReportWorkflow $workflow) {}

    public function handle(Report $report, User $admin, ?string $note = null): Report
    {
        if ($report->workflow_status === WorkflowStatus::Submitted) {
            $report = $this->workflow->transition($report, WorkflowStatus::Triaged, $admin, note: 'Triaged for info request');
        }

        if ($report->workflow_status === WorkflowStatus::Triaged) {
            $report = $this->workflow->transition($report, WorkflowStatus::InVerification, $admin, note: 'Started verification for info request');
        }

        return $this->workflow->transition($report, WorkflowStatus::NeedsInfo, $admin, [], $note);
    }
}

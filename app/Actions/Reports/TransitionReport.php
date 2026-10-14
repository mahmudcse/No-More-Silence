<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\WorkflowStatus;
use App\Models\Report;
use App\Models\User;
use App\Support\ReportWorkflow;

final class TransitionReport
{
    public function __construct(private readonly ReportWorkflow $workflow) {}

    public function handle(Report $report, User $admin, WorkflowStatus $to, ?string $note = null): Report
    {
        return $this->workflow->transition($report, $to, $admin, [], $note);
    }
}

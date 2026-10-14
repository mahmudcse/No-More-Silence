<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Events\ReportRetracted;
use App\Models\Report;
use App\Models\User;
use App\Support\ReportWorkflow;

final class RetractReport
{
    public function __construct(private readonly ReportWorkflow $workflow) {}

    public function handle(Report $report, User $admin, ?string $note = null): Report
    {
        $wasPublic = $report->visibility !== Visibility::Hidden;

        $report = $this->workflow->transition($report, WorkflowStatus::Retracted, $admin, [], $note);
        $report->forceFill(['visibility' => Visibility::Hidden])->save();

        ReportRetracted::dispatch($report, $wasPublic);

        return $report;
    }
}

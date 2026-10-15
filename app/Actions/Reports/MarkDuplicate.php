<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\WorkflowStatus;
use App\Models\Report;
use App\Models\User;
use App\Support\ReportWorkflow;
use DomainException;

final class MarkDuplicate
{
    public function __construct(private readonly ReportWorkflow $workflow) {}

    public function handle(Report $report, Report $original, User $admin, ?string $note = null): Report
    {
        if ($report->is($original)) {
            throw new DomainException('A report cannot be a duplicate of itself.');
        }

        $report = $this->workflow->transition($report, WorkflowStatus::Duplicate, $admin, [], $note);
        $report->forceFill(['duplicate_of_id' => $original->getKey()])->save();

        return $report;
    }
}

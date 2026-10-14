<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\WorkflowStatus;
use App\Models\Report;
use App\Models\User;

final class ReportWorkflow
{
    /**
     * @param  array<string, bool>  $checklist
     */
    public function transition(
        Report $report,
        WorkflowStatus $to,
        ?User $admin = null,
        array $checklist = [],
        ?string $note = null,
    ): Report {
        $from = $report->workflow_status;
        $from->assertCanTransitionTo($to);

        $report->workflow_status = $to;
        $report->save();

        $report->reviews()->create([
            'admin_id' => $admin?->id,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'checklist' => $checklist,
            'note' => $note,
        ]);

        return $report;
    }
}

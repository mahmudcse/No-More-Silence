<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Events\ReportPublished;
use App\Models\Report;
use App\Models\User;
use DomainException;

final class PublishReport
{
    public function handle(Report $report, User $admin, Visibility $visibility): Report
    {
        if ($report->workflow_status !== WorkflowStatus::Verified) {
            throw new DomainException('Only verified reports can be published.');
        }

        if ($report->verified_by === $admin->id) {
            throw new DomainException('The verifying admin cannot publish the same report.');
        }

        if ($visibility === Visibility::Hidden) {
            throw new DomainException('A report must be published as public or featured.');
        }

        $report->forceFill([
            'visibility' => $visibility,
            'published_at' => now(),
            'published_by' => $admin->id,
        ])->save();

        ReportPublished::dispatch($report);

        return $report;
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\VerificationCheck;
use App\Enums\WorkflowStatus;
use App\Events\ReportVerified;
use App\Models\Report;
use App\Models\User;
use App\Support\ReportWorkflow;
use DomainException;

final class VerifyReport
{
    public function __construct(private readonly ReportWorkflow $workflow) {}

    /**
     * @param  array<string, bool>  $checklist
     */
    public function handle(Report $report, User $admin, array $checklist, ?string $note = null): Report
    {
        if ($report->submitted_by === $admin->id) {
            throw new DomainException('An admin cannot verify a report they submitted.');
        }

        if ($report->facebook_profile_id === null) {
            throw new DomainException('The report must be linked to a profile before verification.');
        }

        if ($report->category === null) {
            throw new DomainException('The report must have a category before verification.');
        }

        $missing = VerificationCheck::missingFrom($checklist);

        if ($missing !== []) {
            throw new DomainException('Verification checklist incomplete: '.implode(', ', array_map(
                fn (VerificationCheck $check): string => $check->value,
                $missing,
            )).'.');
        }

        $report = $this->workflow->transition($report, WorkflowStatus::Verified, $admin, $checklist, $note);

        $report->forceFill([
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ])->save();

        ReportVerified::dispatch($report);

        return $report;
    }
}

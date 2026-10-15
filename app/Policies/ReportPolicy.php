<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Report;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Report');
    }

    public function view(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('View:Report');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Report');
    }

    public function update(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('Update:Report');
    }

    public function delete(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('Delete:Report');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Report');
    }

    public function restore(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('Restore:Report');
    }

    public function forceDelete(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('ForceDelete:Report');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Report');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Report');
    }

    public function replicate(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('Replicate:Report');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Report');
    }

    public function verify(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('verify_report') && $report->submitted_by !== $authUser->id;
    }

    public function publish(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('publish_report')
            && $report->verified_by !== $authUser->id
            && $report->submitted_by !== $authUser->id;
    }

    public function retract(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('retract_report');
    }

    public function reject(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('reject_report');
    }

    public function requestInfo(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('request_info_report');
    }

    /**
     * Triage covers: accept into the queue, mark spam, mark duplicate.
     */
    public function triage(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('triage_report') && $report->submitted_by !== $authUser->id;
    }

    public function redact(AuthUser $authUser, Report $report): bool
    {
        return $authUser->can('redact_evidence') && $report->submitted_by !== $authUser->id;
    }
}

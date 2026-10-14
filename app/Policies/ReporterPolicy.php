<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Reporter;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ReporterPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Reporter');
    }

    public function view(AuthUser $authUser, Reporter $reporter): bool
    {
        return $authUser->can('View:Reporter');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Reporter');
    }

    public function update(AuthUser $authUser, Reporter $reporter): bool
    {
        return $authUser->can('Update:Reporter');
    }

    public function delete(AuthUser $authUser, Reporter $reporter): bool
    {
        return $authUser->can('Delete:Reporter');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Reporter');
    }

    public function restore(AuthUser $authUser, Reporter $reporter): bool
    {
        return $authUser->can('Restore:Reporter');
    }

    public function forceDelete(AuthUser $authUser, Reporter $reporter): bool
    {
        return $authUser->can('ForceDelete:Reporter');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Reporter');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Reporter');
    }

    public function replicate(AuthUser $authUser, Reporter $reporter): bool
    {
        return $authUser->can('Replicate:Reporter');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Reporter');
    }

    public function viewReporterIdentity(AuthUser $authUser): bool
    {
        return $authUser->can('view_reporter_identity');
    }

    public function ban(AuthUser $authUser, Reporter $reporter): bool
    {
        return $authUser->can('ban_reporter');
    }
}

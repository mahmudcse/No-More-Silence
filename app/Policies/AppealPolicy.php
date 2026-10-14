<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Appeal;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AppealPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Appeal');
    }

    public function view(AuthUser $authUser, Appeal $appeal): bool
    {
        return $authUser->can('View:Appeal');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Appeal');
    }

    public function update(AuthUser $authUser, Appeal $appeal): bool
    {
        return $authUser->can('Update:Appeal');
    }

    public function delete(AuthUser $authUser, Appeal $appeal): bool
    {
        return $authUser->can('Delete:Appeal');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Appeal');
    }

    public function restore(AuthUser $authUser, Appeal $appeal): bool
    {
        return $authUser->can('Restore:Appeal');
    }

    public function forceDelete(AuthUser $authUser, Appeal $appeal): bool
    {
        return $authUser->can('ForceDelete:Appeal');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Appeal');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Appeal');
    }

    public function replicate(AuthUser $authUser, Appeal $appeal): bool
    {
        return $authUser->can('Replicate:Appeal');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Appeal');
    }

    public function handle(AuthUser $authUser, Appeal $appeal): bool
    {
        return $authUser->can('handle_appeal');
    }
}

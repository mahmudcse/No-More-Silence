<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FacebookProfile;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FacebookProfilePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FacebookProfile');
    }

    public function view(AuthUser $authUser, FacebookProfile $facebookProfile): bool
    {
        return $authUser->can('View:FacebookProfile');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FacebookProfile');
    }

    public function update(AuthUser $authUser, FacebookProfile $facebookProfile): bool
    {
        return $authUser->can('Update:FacebookProfile');
    }

    public function delete(AuthUser $authUser, FacebookProfile $facebookProfile): bool
    {
        return $authUser->can('Delete:FacebookProfile');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:FacebookProfile');
    }

    public function restore(AuthUser $authUser, FacebookProfile $facebookProfile): bool
    {
        return $authUser->can('Restore:FacebookProfile');
    }

    public function forceDelete(AuthUser $authUser, FacebookProfile $facebookProfile): bool
    {
        return $authUser->can('ForceDelete:FacebookProfile');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FacebookProfile');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FacebookProfile');
    }

    public function replicate(AuthUser $authUser, FacebookProfile $facebookProfile): bool
    {
        return $authUser->can('Replicate:FacebookProfile');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FacebookProfile');
    }

    public function merge(AuthUser $authUser, FacebookProfile $facebookProfile): bool
    {
        return $authUser->can('merge_profile');
    }
}

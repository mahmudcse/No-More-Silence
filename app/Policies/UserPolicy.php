<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:User') || $user->hasRole('super_admin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('View:User') || $user->hasRole('super_admin') || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->can('Create:User') || $user->hasRole('super_admin');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('Update:User') || $user->hasRole('super_admin') || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        // Cannot delete oneself
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('Delete:User') || $user->hasRole('super_admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('DeleteAny:User') || $user->hasRole('super_admin');
    }
}

<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Admin') || $user->hasRole('Super Admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage-users') || $user->hasRole(['Admin', 'Sales Manager']);
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasPermissionTo('manage-users') || $user->hasRole('Sales Manager');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage-users') || $user->hasRole('Admin');
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasPermissionTo('manage-users') || $user->hasRole('Admin');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->id !== $model->id && ($user->hasPermissionTo('manage-users') || $user->hasRole('Admin'));
    }
}

<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DealPolicy
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
        return $user->hasPermissionTo('view-deals') || $user->hasRole(['Sales Manager', 'Sales Representative']);
    }

    public function view(User $user, Deal $deal): bool
    {
        return $user->hasPermissionTo('view-deals') || $deal->owner_id === $user->id || $user->hasRole(['Sales Manager', 'Sales Representative']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-deals') || $user->hasRole(['Sales Manager', 'Sales Representative']);
    }

    public function update(User $user, Deal $deal): bool
    {
        return $user->hasPermissionTo('edit-deals') || $deal->owner_id === $user->id || $user->hasRole('Sales Manager');
    }

    public function delete(User $user, Deal $deal): bool
    {
        return $user->hasPermissionTo('delete-deals') || $user->hasRole('Sales Manager');
    }
}

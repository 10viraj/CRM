<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
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
        return $user->hasPermissionTo('view-leads') || $user->hasRole(['Sales Manager', 'Sales Representative']);
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->hasPermissionTo('view-leads') || $lead->owner_id === $user->id || $user->hasRole(['Sales Manager', 'Sales Representative']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-leads') || $user->hasRole(['Sales Manager', 'Sales Representative']);
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->hasPermissionTo('edit-leads') || $lead->owner_id === $user->id || $user->hasRole('Sales Manager');
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->hasPermissionTo('delete-leads') || $user->hasRole('Sales Manager');
    }
}

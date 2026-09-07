<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityPolicy
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
        return true;
    }

    public function view(User $user, Activity $activity): bool
    {
        return $activity->user_id === $user->id || $user->hasRole(['Sales Manager', 'Admin']);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Activity $activity): bool
    {
        return $activity->user_id === $user->id || $user->hasRole(['Sales Manager', 'Admin']);
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $activity->user_id === $user->id || $user->hasRole(['Sales Manager', 'Admin']);
    }
}

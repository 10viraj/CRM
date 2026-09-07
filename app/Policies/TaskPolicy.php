<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
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

    public function view(User $user, Task $task): bool
    {
        return $task->assign_to_id === $user->id || $task->creator_id === $user->id || $user->hasRole(['Sales Manager', 'Admin']);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return $task->assign_to_id === $user->id || $task->creator_id === $user->id || $user->hasRole(['Sales Manager', 'Admin']);
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->creator_id === $user->id || $user->hasRole(['Sales Manager', 'Admin']);
    }
}

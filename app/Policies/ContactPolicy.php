<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
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
        return $user->hasPermissionTo('view-contacts') || $user->hasRole(['Sales Manager', 'Sales Representative', 'Support Agent']);
    }

    public function view(User $user, Contact $contact): bool
    {
        return $user->hasPermissionTo('view-contacts') || $contact->owner_id === $user->id || $user->hasRole(['Sales Manager', 'Sales Representative', 'Support Agent']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-contacts') || $user->hasRole(['Sales Manager', 'Sales Representative']);
    }

    public function update(User $user, Contact $contact): bool
    {
        return $user->hasPermissionTo('edit-contacts') || $contact->owner_id === $user->id || $user->hasRole('Sales Manager');
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $user->hasPermissionTo('delete-contacts') || $user->hasRole('Sales Manager');
    }
}

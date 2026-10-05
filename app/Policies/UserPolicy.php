<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ChecksRelationships;

class UserPolicy
{
    use ChecksRelationships;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'users.manage');
    }

    public function view(User $user, User $target): bool
    {
        return ($user->is($target) && $this->hasPermission($user, 'profile.view_own'))
            || $this->hasPermission($user, 'users.manage');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'users.manage');
    }

    public function update(User $user, User $target): bool
    {
        return ($user->is($target) && $this->hasPermission($user, 'profile.update_own'))
            || $this->hasPermission($user, 'users.manage');
    }

    public function delete(User $user, User $target): bool
    {
        return ! $user->is($target) && $this->hasPermission($user, 'users.manage');
    }

    public function assignRole(User $user, ?User $target = null): bool
    {
        return $user->hasRole('super_admin')
            && $this->hasPermission($user, 'users.manage')
            && ($target === null || ! $user->is($target));
    }
}

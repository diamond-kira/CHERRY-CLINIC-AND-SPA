<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;
use App\Policies\Concerns\ChecksRelationships;

class StaffPolicy
{
    use ChecksRelationships;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'staff.view_all');
    }

    public function view(User $user, Staff $staff): bool
    {
        return $this->hasPermission($user, 'staff.view_all')
            || ($user->staff?->is($staff) && $this->hasPermission($user, 'profile.view_own'));
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'staff.manage');
    }

    public function update(User $user, Staff $staff): bool
    {
        return $this->hasPermission($user, 'staff.manage');
    }

    public function delete(User $user, Staff $staff): bool
    {
        return $this->hasPermission($user, 'staff.manage');
    }
}

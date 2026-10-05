<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;
use App\Policies\Concerns\ChecksRelationships;

class PatientPolicy
{
    use ChecksRelationships;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'patients.view_all')
            || $this->hasPermission($user, 'patients.view_basic')
            || $this->hasPermission($user, 'patients.view_assigned');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $this->hasPermission($user, 'patients.view_all')
            || ($this->hasPermission($user, 'patients.view_basic'))
            || ($this->hasPermission($user, 'patients.view_assigned') && $this->isAssignedToPatient($user, $patient))
            || ($this->hasPermission($user, 'patients.view_own') && $this->ownsPatient($user, $patient));
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'patients.create');
    }

    public function update(User $user, Patient $patient): bool
    {
        return ($this->hasPermission($user, 'patients.update_basic')
                && ($this->hasPermission($user, 'patients.view_all') || $this->hasPermission($user, 'patients.view_basic')))
            || ($this->hasPermission($user, 'patients.update_own') && $this->ownsPatient($user, $patient));
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $this->hasPermission($user, 'patients.view_all');
    }
}

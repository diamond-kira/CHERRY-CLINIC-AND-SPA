<?php

namespace App\Policies;

use App\Models\Prescription;
use App\Models\User;
use App\Policies\Concerns\ChecksRelationships;

class PrescriptionPolicy
{
    use ChecksRelationships;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'prescriptions.manage_assigned')
            || $this->hasPermission($user, 'prescriptions.view_own');
    }

    public function view(User $user, Prescription $prescription): bool
    {
        if ($this->hasPermission($user, 'prescriptions.manage_assigned')) {
            $appointment = $prescription->consultation?->appointment;

            return $this->isAdministrativeRole($user)
                || ($appointment && $this->isAssignedToAppointment($user, $appointment));
        }

        $appointment = $prescription->consultation?->appointment;

        return $this->hasPermission($user, 'prescriptions.view_own')
            && $prescription->status === 'issued'
            && $appointment !== null
            && $this->ownsAppointment($user, $appointment);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'prescriptions.create');
    }

    public function update(User $user, Prescription $prescription): bool
    {
        $appointment = $prescription->consultation?->appointment;

        return $prescription->status === 'draft'
            && $this->hasPermission($user, 'prescriptions.manage_assigned')
            && ($this->isAdministrativeRole($user)
                || ($appointment && $this->isAssignedToAppointment($user, $appointment)));
    }
}

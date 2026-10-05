<?php

namespace App\Policies\Concerns;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;

trait ChecksRelationships
{
    protected function hasPermission(User $user, string $permission): bool
    {
        return $user->hasPermission($permission);
    }

    protected function ownsPatient(User $user, Patient $patient): bool
    {
        return $user->patient()->whereKey($patient->getKey())->exists();
    }

    protected function isAssignedToPatient(User $user, Patient $patient): bool
    {
        $staffId = $user->staff?->getKey();

        return $staffId !== null
            && $patient->appointments()->where('staff_id', $staffId)->exists();
    }

    protected function ownsAppointment(User $user, Appointment $appointment): bool
    {
        return $user->patient()->whereKey($appointment->patient_id)->exists();
    }

    protected function isAssignedToAppointment(User $user, Appointment $appointment): bool
    {
        return $appointment->staff_id !== null
            && $user->staff()->whereKey($appointment->staff_id)->exists();
    }

    protected function isAdministrativeRole(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}

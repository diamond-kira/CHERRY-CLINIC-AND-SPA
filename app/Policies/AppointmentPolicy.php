<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use App\Policies\Concerns\ChecksRelationships;

class AppointmentPolicy
{
    use ChecksRelationships;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'appointments.view_all')
            || $this->hasPermission($user, 'appointments.view_operational')
            || $this->hasPermission($user, 'appointments.view_assigned')
            || $this->hasPermission($user, 'appointments.view_own');
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $this->hasPermission($user, 'appointments.view_all')
            || ($this->hasPermission($user, 'appointments.view_operational'))
            || ($this->hasPermission($user, 'appointments.view_assigned') && $this->isAssignedToAppointment($user, $appointment))
            || ($this->hasPermission($user, 'appointments.view_own') && $this->ownsAppointment($user, $appointment));
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'appointments.create');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $this->hasPermission($user, 'appointments.manage_all')
            || ($this->hasPermission($user, 'appointments.manage_operational')
                && $this->hasPermission($user, 'appointments.view_operational'));
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return ($this->hasPermission($user, 'appointments.cancel_own')
                && $this->ownsAppointment($user, $appointment)
                && in_array($appointment->status, ['pending', 'confirmed'], true))
            || $this->update($user, $appointment);
    }

    public function checkIn(User $user, Appointment $appointment): bool
    {
        return $this->hasPermission($user, 'appointments.check_in')
            && ($this->hasPermission($user, 'appointments.view_all') || $this->hasPermission($user, 'appointments.view_operational'));
    }
}

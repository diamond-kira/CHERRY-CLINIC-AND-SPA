<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\SpaServiceRecord;
use App\Models\User;
use App\Policies\Concerns\ChecksRelationships;

class SpaServiceRecordPolicy
{
    use ChecksRelationships;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'spa_records.view_assigned')
            || $this->hasPermission($user, 'spa_records.view_own');
    }

    public function view(User $user, SpaServiceRecord $record): bool
    {
        $appointment = $record->appointment;

        if ($this->hasPermission($user, 'spa_records.view_assigned')) {
            return $this->isAdministrativeRole($user)
                || ($appointment && $this->isAssignedToAppointment($user, $appointment));
        }

        return $this->hasPermission($user, 'spa_records.view_own')
            && $record->status === 'completed'
            && $appointment !== null
            && $this->ownsAppointment($user, $appointment);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'spa_records.create');
    }

    public function createForAppointment(User $user, Appointment $appointment): bool
    {
        return $this->hasPermission($user, 'spa_records.create')
            && $appointment->service?->service_type === 'spa'
            && ($this->isAdministrativeRole($user) || $this->isAssignedToAppointment($user, $appointment));
    }

    public function update(User $user, SpaServiceRecord $record): bool
    {
        $appointment = $record->appointment;

        return $this->hasPermission($user, 'spa_records.update_assigned')
            && ($this->isAdministrativeRole($user)
                || ($appointment && $this->isAssignedToAppointment($user, $appointment)));
    }

    public function complete(User $user, SpaServiceRecord $record): bool
    {
        $appointment = $record->appointment;

        return $this->hasPermission($user, 'spa_records.complete_assigned')
            && ($this->isAdministrativeRole($user)
                || ($appointment && $this->isAssignedToAppointment($user, $appointment)));
    }
}

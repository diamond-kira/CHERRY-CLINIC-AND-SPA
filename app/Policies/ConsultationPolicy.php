<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\User;
use App\Policies\Concerns\ChecksRelationships;

class ConsultationPolicy
{
    use ChecksRelationships;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'consultations.view_assigned')
            || $this->hasPermission($user, 'medical_records.view_own');
    }

    public function view(User $user, Consultation $consultation): bool
    {
        $appointment = $consultation->appointment;

        if ($this->hasPermission($user, 'consultations.view_assigned')) {
            return $this->isAdministrativeRole($user)
                || ($appointment && $this->isAssignedToAppointment($user, $appointment));
        }

        return $this->hasPermission($user, 'medical_records.view_own')
            && $consultation->status === 'completed'
            && $appointment !== null
            && $this->ownsAppointment($user, $appointment);
    }

    public function createForAppointment(User $user, Appointment $appointment): bool
    {
        return $this->hasPermission($user, 'consultations.create')
            && in_array($appointment->service?->service_type, ['clinic', 'clinical'], true)
            && ($this->isAdministrativeRole($user) || $this->isAssignedToAppointment($user, $appointment));
    }

    public function update(User $user, Consultation $consultation): bool
    {
        return $this->hasPermission($user, 'consultations.update_assigned')
            && ($this->isAdministrativeRole($user)
                || ($consultation->appointment && $this->isAssignedToAppointment($user, $consultation->appointment)));
    }

    public function complete(User $user, Consultation $consultation): bool
    {
        return $this->hasPermission($user, 'consultations.complete_assigned')
            && ($this->isAdministrativeRole($user)
                || ($consultation->appointment && $this->isAssignedToAppointment($user, $consultation->appointment)));
    }
}

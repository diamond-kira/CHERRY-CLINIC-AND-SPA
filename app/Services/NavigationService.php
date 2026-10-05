<?php

namespace App\Services;

use App\Models\User;

class NavigationService
{
    public function forUser(User $user): array
    {
        $items = match ($user->role?->slug) {
            'super_admin' => [
                ['Dashboard', 'admin.dashboard', null],
                ['Users', 'admin.users.index', 'users.manage'],
                ['Appointments', 'admin.appointments.index', 'appointments.view_all'],
                ['Patients / Clients', 'admin.patients.index', 'patients.view_all'],
                ['Services', 'admin.services.index', 'services.view'],
                ['Staff', 'admin.staff.index', 'staff.view_all'],
                ['Payments', 'admin.payments.index', 'payments.view_all'],
                ['Reports', 'admin.reports.index', 'reports.view_all'],
                ['Audit Logs', 'admin.audit-logs.index', 'audit_logs.view'],
                ['Settings', 'admin.settings.index', 'settings.manage'],
                ['Notifications', 'admin.notifications.index', 'notifications.view_all'],
                ['My Profile', 'profile', 'profile.view_own'],
            ],
            'receptionist' => [
                ['Dashboard', 'receptionist.dashboard', null],
                ['Appointments', 'receptionist.appointments.index', 'appointments.view_operational'],
                ['Patients / Clients', 'receptionist.patients.index', 'patients.view_basic'],
                ['Payments', 'receptionist.payments.index', 'payments.view_limited'],
                ['Services', 'receptionist.services.index', 'services.view'],
                ['Reports', 'receptionist.reports.index', 'reports.view_operational'],
                ['Notifications', 'receptionist.notifications.index', 'notifications.view_own'],
                ['My Profile', 'profile', 'profile.view_own'],
            ],
            'doctor' => [
                ['Dashboard', 'doctor.dashboard', null],
                ['My Appointments', 'doctor.appointments.index', 'appointments.view_assigned'],
                ['My Patients', 'doctor.patients.index', 'patients.view_assigned'],
                ['Consultations', 'doctor.consultations.index', 'consultations.view_assigned'],
                ['Prescriptions', 'doctor.prescriptions.index', 'prescriptions.manage_assigned'],
                ['Notifications', 'doctor.notifications.index', 'notifications.view_own'],
                ['My Profile', 'profile', 'profile.view_own'],
            ],
            'therapist' => [
                ['Dashboard', 'therapist.dashboard', null],
                ['My Appointments', 'therapist.appointments.index', 'appointments.view_assigned'],
                ['My Clients', 'therapist.patients.index', 'patients.view_assigned'],
                ['Spa Services', 'therapist.services.index', 'services.view'],
                ['Treatment Records', 'therapist.treatments.index', 'spa_records.view_assigned'],
                ['Notifications', 'therapist.notifications.index', 'notifications.view_own'],
                ['My Profile', 'profile', 'profile.view_own'],
            ],
            'patient' => [
                ['Dashboard', 'patient.dashboard', null],
                ['Book Appointment', 'patient.appointments.create', 'appointments.create'],
                ['My Appointments', 'patient.appointments.index', 'appointments.view_own'],
                ['My Treatments', 'patient.treatments.index', 'spa_records.view_own'],
                ['My Payments', 'patient.payments.index', 'payments.view_own'],
                ['My Records', 'patient.records', 'medical_records.view_own'],
                ['Notifications', 'patient.notifications.index', 'notifications.view_own'],
                ['My Profile', 'profile', 'profile.view_own'],
            ],
            default => [],
        };

        return array_values(array_filter($items, function (array $item) use ($user): bool {
            return $item[2] === null || $user->hasPermission($item[2]);
        }));
    }
}

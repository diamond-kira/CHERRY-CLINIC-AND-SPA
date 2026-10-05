<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin' => ['Super Admin', 'Full system administration.'],
            'receptionist' => ['Receptionist', 'Front-desk and operational access.'],
            'doctor' => ['Doctor / Clinician', 'Assigned clinical care access.'],
            'therapist' => ['Therapist / Spa Staff', 'Assigned spa treatment access.'],
            'patient' => ['Patient / Client', 'Own account and service access.'],
        ];

        $permissionDefinitions = [
            'dashboard.super_admin.view' => ['View super admin dashboard', 'dashboards'],
            'dashboard.receptionist.view' => ['View receptionist dashboard', 'dashboards'],
            'dashboard.doctor.view' => ['View doctor dashboard', 'dashboards'],
            'dashboard.therapist.view' => ['View therapist dashboard', 'dashboards'],
            'dashboard.patient.view' => ['View patient dashboard', 'dashboards'],
            'users.manage' => ['Manage user accounts', 'users'],
            'staff.view_all' => ['View all staff', 'staff'],
            'staff.manage' => ['Manage staff accounts and profiles', 'staff'],
            'services.view' => ['View active services', 'services'],
            'services.manage' => ['Manage services and categories', 'services'],
            'services.change_prices' => ['Change service prices', 'services'],
            'patients.create' => ['Register patients', 'patients'],
            'patients.view_all' => ['View all patient records', 'patients'],
            'patients.view_basic' => ['View basic patient information', 'patients'],
            'patients.view_assigned' => ['View assigned patients and clients', 'patients'],
            'patients.view_own' => ['View own patient profile', 'patients'],
            'patients.update_basic' => ['Update basic patient information', 'patients'],
            'patients.update_own' => ['Update own patient profile', 'patients'],
            'appointments.view_all' => ['View all appointments', 'appointments'],
            'appointments.view_operational' => ['View operational appointments', 'appointments'],
            'appointments.view_assigned' => ['View assigned appointments', 'appointments'],
            'appointments.view_own' => ['View own appointments', 'appointments'],
            'appointments.create' => ['Create appointments', 'appointments'],
            'appointments.manage_all' => ['Manage all appointments', 'appointments'],
            'appointments.manage_operational' => ['Manage operational appointments', 'appointments'],
            'appointments.manage_assigned' => ['Manage assigned appointments', 'appointments'],
            'appointments.cancel_own' => ['Cancel own eligible appointments', 'appointments'],
            'appointments.check_in' => ['Check patients in', 'appointments'],
            'consultations.view_assigned' => ['View assigned consultations', 'clinical'],
            'consultations.create' => ['Start assigned consultations', 'clinical'],
            'consultations.update_assigned' => ['Update assigned consultation notes', 'clinical'],
            'consultations.complete_assigned' => ['Complete assigned consultations', 'clinical'],
            'medical_records.view_assigned' => ['View assigned medical records', 'clinical'],
            'medical_records.view_own' => ['View own finalized medical records', 'clinical'],
            'prescriptions.create' => ['Create prescriptions for assigned consultations', 'clinical'],
            'prescriptions.manage_assigned' => ['Manage prescriptions for assigned consultations', 'clinical'],
            'prescriptions.view_own' => ['View own prescriptions', 'clinical'],
            'spa_records.view_assigned' => ['View assigned spa treatment records', 'spa'],
            'spa_records.create' => ['Start assigned spa treatment records', 'spa'],
            'spa_records.update_assigned' => ['Update assigned spa treatment records', 'spa'],
            'spa_records.complete_assigned' => ['Complete assigned spa treatments', 'spa'],
            'spa_records.view_own' => ['View own finalized spa treatment records', 'spa'],
            'payments.record' => ['Record patient payments', 'payments'],
            'payments.view_all' => ['View all payments', 'payments'],
            'payments.view_limited' => ['View operational payment history', 'payments'],
            'payments.view_own' => ['View own payments', 'payments'],
            'receipts.view_all' => ['View and issue receipts', 'payments'],
            'receipts.view_own' => ['View own receipts', 'payments'],
            'reports.view_all' => ['View administrative reports', 'reports'],
            'reports.view_operational' => ['View operational reports', 'reports'],
            'reports.view_clinical' => ['View assigned clinical reports', 'reports'],
            'reports.view_spa' => ['View assigned spa reports', 'reports'],
            'audit_logs.view' => ['View audit logs', 'administration'],
            'settings.manage' => ['Manage system settings', 'administration'],
            'notifications.view_all' => ['View all system notifications', 'notifications'],
            'notifications.view_own' => ['View own relevant notifications', 'notifications'],
            'profile.view_own' => ['View own profile', 'profile'],
            'profile.update_own' => ['Update own profile', 'profile'],
        ];

        $permissionRecords = [];

        foreach ($permissionDefinitions as $slug => [$name, $group]) {
            $permissionRecords[$slug] = Permission::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'group' => $group],
            );
        }

        $rolePermissions = [
            'super_admin' => [
                'dashboard.super_admin.view', 'dashboard.receptionist.view', 'dashboard.doctor.view', 'dashboard.therapist.view',
                'users.manage', 'staff.view_all', 'staff.manage', 'services.view', 'services.manage', 'services.change_prices',
                'patients.create', 'patients.view_all', 'patients.view_basic', 'patients.update_basic',
                'appointments.view_all', 'appointments.view_operational', 'appointments.view_assigned', 'appointments.create',
                'appointments.manage_all', 'appointments.manage_operational', 'appointments.manage_assigned', 'appointments.check_in',
                'consultations.view_assigned', 'consultations.create', 'consultations.update_assigned', 'consultations.complete_assigned',
                'medical_records.view_assigned', 'prescriptions.create', 'prescriptions.manage_assigned',
                'spa_records.view_assigned', 'spa_records.create', 'spa_records.update_assigned', 'spa_records.complete_assigned',
                'payments.record', 'payments.view_all', 'payments.view_limited', 'receipts.view_all',
                'reports.view_all', 'reports.view_operational', 'reports.view_clinical', 'reports.view_spa',
                'audit_logs.view', 'settings.manage', 'notifications.view_all', 'notifications.view_own',
                'profile.view_own', 'profile.update_own',
            ],
            'receptionist' => [
                'dashboard.receptionist.view', 'services.view', 'patients.create', 'patients.view_basic', 'patients.update_basic',
                'appointments.view_operational', 'appointments.create', 'appointments.manage_operational', 'appointments.check_in',
                'payments.record', 'payments.view_limited', 'receipts.view_all', 'reports.view_operational',
                'notifications.view_own', 'profile.view_own', 'profile.update_own',
            ],
            'doctor' => [
                'dashboard.doctor.view', 'services.view', 'patients.view_assigned',
                'appointments.view_assigned',
                'consultations.view_assigned', 'consultations.create', 'consultations.update_assigned', 'consultations.complete_assigned',
                'medical_records.view_assigned', 'prescriptions.create', 'prescriptions.manage_assigned', 'reports.view_clinical',
                'notifications.view_own', 'profile.view_own', 'profile.update_own',
            ],
            'therapist' => [
                'dashboard.therapist.view', 'services.view', 'patients.view_assigned',
                'appointments.view_assigned',
                'spa_records.view_assigned', 'spa_records.create', 'spa_records.update_assigned', 'spa_records.complete_assigned',
                'reports.view_spa', 'notifications.view_own', 'profile.view_own', 'profile.update_own',
            ],
            'patient' => [
                'dashboard.patient.view', 'services.view', 'patients.view_own', 'patients.update_own',
                'appointments.view_own', 'appointments.create', 'appointments.cancel_own',
                'medical_records.view_own', 'prescriptions.view_own', 'spa_records.view_own',
                'payments.view_own', 'receipts.view_own', 'notifications.view_own',
                'profile.view_own', 'profile.update_own',
            ],
        ];

        foreach ($roles as $slug => [$name, $description]) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description],
            );

            $role->permissions()->sync(array_map(
                fn (string $permission) => $permissionRecords[$permission]->getKey(),
                $rolePermissions[$slug],
            ));
        }
    }
}

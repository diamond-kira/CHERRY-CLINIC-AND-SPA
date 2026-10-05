<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoAccountsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $accounts = [
            ['Demo Super Admin', 'admin.demo@example.test', 'super_admin'],
            ['Demo Receptionist', 'receptionist.demo@example.test', 'receptionist'],
            ['Demo Doctor', 'doctor.demo@example.test', 'doctor'],
            ['Demo Therapist', 'therapist.demo@example.test', 'therapist'],
            ['Demo Patient', 'patient.demo@example.test', 'patient'],
        ];

        foreach ($accounts as [$name, $email, $roleSlug]) {
            $role = Role::query()->where('slug', $roleSlug)->firstOrFail();

            User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role_id' => $role->getKey(),
                    'password' => 'DemoPass123!',
                ],
            );
        }
    }
}

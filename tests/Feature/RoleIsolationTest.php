<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_patient_signup_always_creates_a_patient_role_and_profile(): void
    {
        $response = $this->post('/register', [
            'name' => 'Taylor Patient',
            'email' => 'taylor@example.test',
            'password' => 'PatientPass123!',
            'password_confirmation' => 'PatientPass123!',
            'role' => 'super_admin',
            'role_id' => Role::query()->where('slug', 'super_admin')->value('id'),
        ]);

        $response->assertSessionHasNoErrors();

        $user = User::query()->where('email', 'taylor@example.test')->firstOrFail();

        $response->assertRedirect(route('patient.dashboard'));
        $this->assertSame('patient', $user->role->slug);
        $this->assertNotNull($user->patient);
        $this->assertDatabaseCount('staff', 0);
    }

    public function test_each_role_can_reach_only_its_dashboard(): void
    {
        foreach ([
            'super_admin' => '/admin/dashboard',
            'receptionist' => '/receptionist/dashboard',
            'doctor' => '/doctor/dashboard',
            'therapist' => '/therapist/dashboard',
            'patient' => '/patient/dashboard',
        ] as $role => $path) {
            $response = $this->actingAs($this->userWithRole($role))->get($path)->assertOk();

            $response->assertSee('class="cz-nav-logout"', false);
            $response->assertSee('class="cz-topbar-logout"', false);
            $this->assertSame(2, substr_count($response->getContent(), 'method="POST" action="'.route('logout').'"'));
        }
    }

    public function test_authenticated_user_can_sign_out(): void
    {
        $this->actingAs($this->userWithRole('super_admin'))
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_patient_cannot_access_admin_routes_or_create_services(): void
    {
        $this->actingAs($this->userWithRole('patient'))
            ->get('/admin/dashboard')->assertForbidden();

        $this->post('/admin/services', [])->assertForbidden();
    }

    public function test_staff_role_namespaces_do_not_cross_authorize(): void
    {
        $this->actingAs($this->userWithRole('doctor'))
            ->get('/admin/staff')->assertForbidden();

        $this->actingAs($this->userWithRole('therapist'))
            ->get('/doctor/consultations')->assertForbidden();

        $this->actingAs($this->userWithRole('receptionist'))
            ->get('/doctor/prescriptions')->assertForbidden();

        $this->actingAs($this->userWithRole('therapist'))
            ->get('/doctor/prescriptions')->assertForbidden();
    }

    public function test_patient_cannot_read_another_patients_records_by_id(): void
    {
        $patientA = $this->userWithRole('patient');
        $patientB = $this->userWithRole('patient');

        $this->actingAs($patientA)
            ->get(route('patient.records.show', $patientB->patient))
            ->assertForbidden();
    }

    public function test_doctor_cannot_open_a_patient_assigned_to_another_doctor(): void
    {
        $doctorA = $this->userWithRole('doctor');
        $doctorB = $this->userWithRole('doctor');
        $patient = $this->userWithRole('patient');
        $this->createAppointment($patient->patient, $doctorB->staff);

        $this->actingAs($doctorA)
            ->get(route('doctor.patients.show', $patient->patient))
            ->assertForbidden();
    }

    public function test_doctor_cannot_read_or_update_another_doctors_consultation(): void
    {
        $doctorA = $this->userWithRole('doctor');
        $doctorB = $this->userWithRole('doctor');
        $patient = $this->userWithRole('patient');
        $appointment = $this->createAppointment($patient->patient, $doctorB->staff);
        $consultation = Consultation::query()->create([
            'appointment_id' => $appointment->getKey(),
            'doctor_id' => $doctorB->staff->getKey(),
            'status' => 'in_progress',
        ]);

        $this->actingAs($doctorA)
            ->get(route('doctor.consultations.edit', $consultation))
            ->assertForbidden();

        $this->patch(route('doctor.consultations.update', $consultation), ['diagnosis' => 'Unauthorized change'])
            ->assertForbidden();

        $this->assertNull($consultation->fresh()->diagnosis);
    }

    public function test_patient_cannot_cancel_another_patients_appointment_by_id(): void
    {
        $patientA = $this->userWithRole('patient');
        $patientB = $this->userWithRole('patient');
        $doctor = $this->userWithRole('doctor');
        $appointment = $this->createAppointment($patientB->patient, $doctor->staff);

        $this->actingAs($patientA)
            ->post(route('patient.appointments.cancel', $appointment))
            ->assertForbidden();

        $this->assertSame('pending', $appointment->fresh()->status);
    }

    public function test_assigned_doctor_cannot_cancel_appointment_as_an_operational_action(): void
    {
        $doctor = $this->userWithRole('doctor');
        $patient = $this->userWithRole('patient');
        $appointment = $this->createAppointment($patient->patient, $doctor->staff);

        $this->assertFalse(Gate::forUser($doctor)->allows('cancel', $appointment));

        $this->assertSame('pending', $appointment->fresh()->status);
    }

    public function test_super_admin_bootstrap_requires_confirmation_and_audits_promotion(): void
    {
        $user = $this->userWithRole('patient');

        $this->artisan('czcms:make-super-admin', ['email' => $user->email])
            ->expectsConfirmation("Grant the super-admin role to {$user->email}?", 'yes')
            ->expectsOutput('Super-admin access granted.')
            ->assertExitCode(0);

        $this->assertSame('super_admin', $user->fresh()->role->slug);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.super_admin_bootstrapped',
            'entity_id' => $user->getKey(),
        ]);
    }

    private function userWithRole(string $slug): User
    {
        $role = Role::query()->where('slug', $slug)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->getKey()]);

        if ($slug === 'patient') {
            $user->patient()->create();
        } elseif (in_array($slug, ['receptionist', 'doctor', 'therapist'], true)) {
            $user->staff()->create(['staff_code' => Str::upper(Str::random(12))]);
        }

        return $user->fresh(['role', 'patient', 'staff']);
    }

    private function createAppointment(Patient $patient, Staff $staff): Appointment
    {
        $category = ServiceCategory::query()->create([
            'name' => 'Clinic',
            'slug' => 'clinic-'.Str::lower(Str::random(8)),
            'service_type' => 'clinic',
            'is_active' => true,
        ]);
        $service = Service::query()->create([
            'service_category_id' => $category->getKey(),
            'name' => 'Consultation',
            'slug' => 'consultation-'.Str::lower(Str::random(8)),
            'service_type' => 'clinic',
            'duration_minutes' => 30,
            'price' => 50,
            'is_active' => true,
        ]);

        return Appointment::query()->create([
            'patient_id' => $patient->getKey(),
            'service_id' => $service->getKey(),
            'staff_id' => $staff->getKey(),
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addMinutes(30),
            'status' => 'pending',
        ]);
    }
}

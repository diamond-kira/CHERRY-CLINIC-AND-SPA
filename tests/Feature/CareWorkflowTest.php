<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CareWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_assigned_doctor_can_start_update_and_complete_consultation(): void
    {
        $doctor = $this->userWithRole('doctor');
        $patient = $this->userWithRole('patient');
        $appointment = $this->appointment($patient->patient, $doctor->staff, 'clinic');

        $this->actingAs($doctor)
            ->post(route('doctor.appointments.consultation.store', $appointment), [
                'presenting_complaint' => 'Headache',
                'examination_notes' => 'Initial assessment',
            ])
            ->assertRedirect(route('doctor.consultations.index'));

        $consultation = $appointment->fresh()->consultation;
        $this->assertSame('in_progress', $consultation->status);
        $this->assertSame('in_progress', $appointment->fresh()->status);

        $this->patch(route('doctor.consultations.update', $consultation), [
            'diagnosis' => 'Migraine',
            'treatment_plan' => 'Rest and follow-up',
        ])->assertSessionHasNoErrors();

        $this->post(route('doctor.consultations.complete', $consultation))->assertRedirect(route('doctor.consultations.index'));
        $this->assertSame('completed', $consultation->fresh()->status);
        $this->assertSame('completed', $appointment->fresh()->status);
    }

    public function test_assigned_therapist_can_record_and_complete_spa_treatment(): void
    {
        $therapist = $this->userWithRole('therapist');
        $patient = $this->userWithRole('patient');
        $appointment = $this->appointment($patient->patient, $therapist->staff, 'spa');

        $this->actingAs($therapist)
            ->post(route('therapist.appointments.treatment.store', $appointment), [
                'treatment_notes' => 'Treatment started.',
                'products_text' => "Cleanser\nMoisturizer",
                'recommendations' => 'Hydrate after treatment.',
            ])
            ->assertRedirect(route('therapist.treatments.index'));

        $record = $appointment->fresh()->spaServiceRecord;
        $this->assertSame(['Cleanser', 'Moisturizer'], $record->products_used);
        $this->assertSame('in_progress', $record->status);

        $this->post(route('therapist.treatments.complete', $record))->assertRedirect(route('therapist.treatments.index'));
        $this->assertSame('completed', $record->fresh()->status);
        $this->assertSame('completed', $appointment->fresh()->status);
    }

    public function test_receptionist_payment_issues_a_receipt_and_checks_patient_link(): void
    {
        $receptionist = $this->userWithRole('receptionist');
        $patientA = $this->userWithRole('patient');
        $patientB = $this->userWithRole('patient');
        $appointment = $this->appointment($patientB->patient, $this->userWithRole('doctor')->staff, 'clinic');

        $this->actingAs($receptionist)->post(route('receptionist.payments.store'), [
            'patient_id' => $patientA->patient->getKey(),
            'appointment_id' => $appointment->getKey(),
            'amount' => '75.00',
            'currency' => 'USD',
            'status' => 'paid',
            'payment_method' => 'cash',
        ])->assertSessionHasErrors('appointment_id');

        $this->post(route('receptionist.payments.store'), [
            'patient_id' => $patientA->patient->getKey(),
            'amount' => '75.00',
            'currency' => 'USD',
            'status' => 'paid',
            'payment_method' => 'cash',
        ])->assertRedirect(route('receptionist.payments.index'));

        $this->assertDatabaseHas('payments', [
            'patient_id' => $patientA->patient->getKey(),
            'status' => 'paid',
            'amount' => '75.00',
        ]);
        $this->assertDatabaseCount('receipts', 1);
    }

    public function test_patient_booking_checks_provider_qualification_and_keeps_ownership(): void
    {
        $patient = $this->userWithRole('patient');
        $otherPatient = $this->userWithRole('patient');
        $doctor = $this->userWithRole('doctor');
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
            'price' => 75,
            'is_active' => true,
        ]);
        $doctor->staff->services()->attach($service->getKey());
        $startsAt = now()->addDays(3)->setTime(10, 0);
        $doctor->staff->schedules()->create([
            'day_of_week' => $startsAt->dayOfWeek,
            'starts_at' => '09:00',
            'ends_at' => '17:00',
            'is_available' => true,
        ]);

        $this->actingAs($patient)->post(route('patient.appointments.store'), [
            'patient_id' => $otherPatient->patient->getKey(),
            'service_id' => $service->getKey(),
            'staff_id' => $doctor->staff->getKey(),
            'starts_at' => $startsAt->format('Y-m-d\TH:i'),
        ])->assertRedirect(route('patient.dashboard'));

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->patient->getKey(),
            'service_id' => $service->getKey(),
            'staff_id' => $doctor->staff->getKey(),
        ]);
        $this->assertDatabaseMissing('appointments', ['patient_id' => $otherPatient->patient->getKey()]);
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

    private function appointment(Patient $patient, Staff $staff, string $type): Appointment
    {
        $category = ServiceCategory::query()->create([
            'name' => ucfirst($type),
            'slug' => $type.'-'.Str::lower(Str::random(8)),
            'service_type' => $type,
            'is_active' => true,
        ]);
        $service = Service::query()->create([
            'service_category_id' => $category->getKey(),
            'name' => ucfirst($type).' visit',
            'slug' => $type.'-visit-'.Str::lower(Str::random(8)),
            'service_type' => $type,
            'duration_minutes' => 30,
            'price' => 75,
            'is_active' => true,
        ]);

        return Appointment::query()->create([
            'patient_id' => $patient->getKey(),
            'service_id' => $service->getKey(),
            'staff_id' => $staff->getKey(),
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addMinutes(30),
            'status' => 'confirmed',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Services\NavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrescriptionWorkflowController extends Controller
{
    public function create(Request $request, NavigationService $navigation)
    {
        $this->authorize('create', Prescription::class);
        $consultations = Consultation::query()->with('appointment.patient.user:id,name', 'appointment.service:id,name')
            ->whereIn('status', ['in_progress', 'completed']);

        if (! $request->user()->hasRole('super_admin')) {
            $consultations->where('doctor_id', $request->user()->staff?->getKey() ?? 0);
        }

        return view('portal.prescription-create', [
            'consultations' => $consultations->orderByDesc('created_at')->limit(100)->get(),
            'navigation' => $navigation->forUser($request->user()),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Prescription::class);
        $validated = $request->validate([
            'consultation_id' => ['required', 'integer', 'exists:consultations,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.medication_name' => ['required', 'string', 'max:255'],
            'items.*.dosage' => ['required', 'string', 'max:255'],
            'items.*.route' => ['nullable', 'string', 'max:100'],
            'items.*.frequency' => ['required', 'string', 'max:255'],
            'items.*.duration' => ['nullable', 'string', 'max:100'],
            'items.*.quantity' => ['nullable', 'string', 'max:100'],
            'items.*.instructions' => ['nullable', 'string', 'max:2000'],
        ]);

        $consultationQuery = Consultation::query()->whereKey($validated['consultation_id'])
            ->whereIn('status', ['in_progress', 'completed']);

        if (! $request->user()->hasRole('super_admin')) {
            $consultationQuery->where('doctor_id', $request->user()->staff?->getKey() ?? 0);
        }

        $consultation = $consultationQuery->first();

        if (! $consultation) {
            throw ValidationException::withMessages(['consultation_id' => 'Choose a consultation assigned to you.']);
        }

        DB::transaction(function () use ($request, $validated, $consultation): void {
            $prescription = Prescription::query()->create([
                'consultation_id' => $consultation->getKey(),
                'issued_by_staff_id' => $consultation->doctor_id,
                'status' => 'issued',
                'notes' => $validated['notes'] ?? null,
                'issued_at' => now(),
            ]);
            $prescription->items()->createMany($validated['items']);

            AuditLog::query()->create([
                'user_id' => $request->user()->getKey(),
                'action' => 'prescription.issued',
                'entity_type' => Prescription::class,
                'entity_id' => $prescription->getKey(),
                'description' => 'Prescription issued for an authorized consultation.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->route($request->user()->hasRole('doctor') ? 'doctor.prescriptions.index' : 'admin.prescriptions.index')
            ->with('status', 'Prescription issued.');
    }
}

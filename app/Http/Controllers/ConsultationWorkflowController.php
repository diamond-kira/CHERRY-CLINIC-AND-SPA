<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Consultation;
use App\Services\NavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationWorkflowController extends Controller
{
    public function create(Request $request, Appointment $appointment, NavigationService $navigation)
    {
        $this->authorize('createForAppointment', [Consultation::class, $appointment]);
        abort_unless($appointment->consultation === null, 409);

        return view('portal.consultation-form', [
            'appointment' => $appointment->load(['patient.user:id,name', 'service:id,name', 'staff.user:id,name']),
            'consultation' => null,
            'navigation' => $navigation->forUser($request->user()),
        ]);
    }

    public function store(Request $request, Appointment $appointment)
    {
        $this->authorize('createForAppointment', [Consultation::class, $appointment]);
        abort_unless($appointment->consultation === null, 409);

        $validated = $request->validate([
            'presenting_complaint' => ['nullable', 'string', 'max:10000'],
            'examination_notes' => ['nullable', 'string', 'max:20000'],
        ]);

        $consultation = DB::transaction(function () use ($request, $appointment, $validated): Consultation {
            $consultation = $appointment->consultation()->create([
                'doctor_id' => $appointment->staff_id,
                'status' => 'in_progress',
                'presenting_complaint' => $validated['presenting_complaint'] ?? null,
                'examination_notes' => $validated['examination_notes'] ?? null,
                'started_at' => now(),
            ]);
            $appointment->update(['status' => 'in_progress']);
            $this->audit($request, 'consultation.started', $consultation);

            return $consultation;
        });

        return redirect()->route($request->user()->hasRole('doctor') ? 'doctor.consultations.index' : 'admin.consultations.index')
            ->with('status', 'Consultation started.');
    }

    public function edit(Request $request, Consultation $consultation, NavigationService $navigation)
    {
        $this->authorize('update', $consultation);

        return view('portal.consultation-form', [
            'appointment' => $consultation->appointment()->with(['patient.user:id,name', 'service:id,name', 'staff.user:id,name'])->firstOrFail(),
            'consultation' => $consultation,
            'navigation' => $navigation->forUser($request->user()),
        ]);
    }

    public function update(Request $request, Consultation $consultation)
    {
        $this->authorize('update', $consultation);
        $validated = $request->validate([
            'presenting_complaint' => ['nullable', 'string', 'max:10000'],
            'examination_notes' => ['nullable', 'string', 'max:20000'],
            'diagnosis' => ['nullable', 'string', 'max:20000'],
            'treatment_plan' => ['nullable', 'string', 'max:20000'],
        ]);

        DB::transaction(function () use ($request, $consultation, $validated): void {
            $consultation->update($validated);
            $this->audit($request, 'consultation.updated', $consultation);
        });

        return back()->with('status', 'Consultation saved.');
    }

    public function complete(Request $request, Consultation $consultation)
    {
        $this->authorize('complete', $consultation);
        abort_unless($consultation->status === 'in_progress', 409);

        DB::transaction(function () use ($request, $consultation): void {
            $consultation->update(['status' => 'completed', 'completed_at' => now()]);
            $consultation->appointment()->update(['status' => 'completed', 'completed_at' => now()]);
            $this->audit($request, 'consultation.completed', $consultation);
        });

        return redirect()->route($request->user()->hasRole('doctor') ? 'doctor.consultations.index' : 'admin.consultations.index')
            ->with('status', 'Consultation completed.');
    }

    private function audit(Request $request, string $action, Consultation $consultation): void
    {
        AuditLog::query()->create([
            'user_id' => $request->user()->getKey(),
            'action' => $action,
            'entity_type' => Consultation::class,
            'entity_id' => $consultation->getKey(),
            'description' => 'Clinical record workflow changed.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\SpaServiceRecord;
use App\Services\NavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpaTreatmentWorkflowController extends Controller
{
    public function create(Request $request, Appointment $appointment, NavigationService $navigation)
    {
        $this->authorize('createForAppointment', [SpaServiceRecord::class, $appointment]);
        abort_unless($appointment->spaServiceRecord === null, 409);

        return view('portal.treatment-form', [
            'appointment' => $appointment->load(['patient.user:id,name', 'service:id,name', 'staff.user:id,name']),
            'record' => null,
            'navigation' => $navigation->forUser($request->user()),
        ]);
    }

    public function store(Request $request, Appointment $appointment)
    {
        $this->authorize('createForAppointment', [SpaServiceRecord::class, $appointment]);
        abort_unless($appointment->spaServiceRecord === null, 409);
        $validated = $request->validate([
            'treatment_notes' => ['nullable', 'string', 'max:20000'],
            'products_text' => ['nullable', 'string', 'max:10000'],
            'recommendations' => ['nullable', 'string', 'max:20000'],
        ]);

        DB::transaction(function () use ($request, $appointment, $validated): void {
            $record = $appointment->spaServiceRecord()->create([
                'therapist_id' => $appointment->staff_id,
                'status' => 'in_progress',
                'treatment_notes' => $validated['treatment_notes'] ?? null,
                'products_used' => $this->parseProducts($validated['products_text'] ?? ''),
                'recommendations' => $validated['recommendations'] ?? null,
                'started_at' => now(),
            ]);
            $appointment->update(['status' => 'in_progress']);
            $this->audit($request, 'spa_treatment.started', $record);
        });

        return redirect()->route($request->user()->hasRole('therapist') ? 'therapist.treatments.index' : 'admin.treatments.index')
            ->with('status', 'Treatment started.');
    }

    public function edit(Request $request, SpaServiceRecord $record, NavigationService $navigation)
    {
        $this->authorize('update', $record);

        return view('portal.treatment-form', [
            'appointment' => $record->appointment()->with(['patient.user:id,name', 'service:id,name', 'staff.user:id,name'])->firstOrFail(),
            'record' => $record,
            'navigation' => $navigation->forUser($request->user()),
        ]);
    }

    public function update(Request $request, SpaServiceRecord $record)
    {
        $this->authorize('update', $record);
        $validated = $request->validate([
            'treatment_notes' => ['nullable', 'string', 'max:20000'],
            'products_text' => ['nullable', 'string', 'max:10000'],
            'recommendations' => ['nullable', 'string', 'max:20000'],
        ]);
        $record->update([
            'treatment_notes' => $validated['treatment_notes'] ?? null,
            'products_used' => $this->parseProducts($validated['products_text'] ?? ''),
            'recommendations' => $validated['recommendations'] ?? null,
        ]);
        $this->audit($request, 'spa_treatment.updated', $record);

        return back()->with('status', 'Treatment record saved.');
    }

    public function complete(Request $request, SpaServiceRecord $record)
    {
        $this->authorize('complete', $record);
        abort_unless($record->status === 'in_progress', 409);

        DB::transaction(function () use ($request, $record): void {
            $record->update(['status' => 'completed', 'completed_at' => now()]);
            $record->appointment()->update(['status' => 'completed', 'completed_at' => now()]);
            $this->audit($request, 'spa_treatment.completed', $record);
        });

        return redirect()->route($request->user()->hasRole('therapist') ? 'therapist.treatments.index' : 'admin.treatments.index')
            ->with('status', 'Treatment completed.');
    }

    private function audit(Request $request, string $action, SpaServiceRecord $record): void
    {
        AuditLog::query()->create([
            'user_id' => $request->user()->getKey(),
            'action' => $action,
            'entity_type' => SpaServiceRecord::class,
            'entity_id' => $record->getKey(),
            'description' => 'Spa treatment workflow changed.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private function parseProducts(string $products): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $products) ?: [])));
    }
}

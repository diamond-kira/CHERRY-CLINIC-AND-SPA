<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Receipt;
use App\Services\NavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentManagementController extends Controller
{
    public function create(Request $request, NavigationService $navigation)
    {
        $this->authorize('create', Payment::class);

        return view('portal.payment-create', [
            'patients' => Patient::query()->with('user:id,name')->orderBy('id')->get(),
            'appointments' => Appointment::query()->whereIn('status', ['pending', 'confirmed', 'checked_in', 'completed'])
                ->with(['patient.user:id,name', 'service:id,name'])->orderByDesc('starts_at')->limit(100)->get(),
            'navigation' => $navigation->forUser($request->user()),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Payment::class);
        $validated = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'status' => ['required', Rule::in(['paid', 'pending'])],
            'payment_method' => ['nullable', Rule::in(['cash', 'card', 'bank_transfer', 'other'])],
        ]);

        if (! empty($validated['appointment_id'])) {
            $appointment = Appointment::query()->findOrFail($validated['appointment_id']);
            if ((int) $appointment->patient_id !== (int) $validated['patient_id']) {
                throw ValidationException::withMessages(['appointment_id' => 'The appointment must belong to the selected patient.']);
            }
        }

        DB::transaction(function () use ($request, $validated): void {
            $payment = Payment::query()->create([
                'patient_id' => $validated['patient_id'],
                'appointment_id' => $validated['appointment_id'] ?? null,
                'recorded_by_user_id' => $request->user()->getKey(),
                'amount' => $validated['amount'],
                'currency' => strtoupper($validated['currency']),
                'status' => $validated['status'],
                'payment_method' => $validated['payment_method'] ?? null,
                'paid_at' => $validated['status'] === 'paid' ? now() : null,
            ]);

            if ($payment->status === 'paid') {
                Receipt::query()->create([
                    'payment_id' => $payment->getKey(),
                    'receipt_number' => 'CZ-'.Str::upper((string) Str::ulid()),
                    'issued_at' => now(),
                ]);
            }

            AuditLog::query()->create([
                'user_id' => $request->user()->getKey(),
                'action' => 'payment.recorded',
                'entity_type' => Payment::class,
                'entity_id' => $payment->getKey(),
                'description' => 'Payment record created.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->route($request->user()->hasRole('super_admin') ? 'admin.payments.index' : 'receptionist.payments.index')
            ->with('status', 'Payment recorded.');
    }
}

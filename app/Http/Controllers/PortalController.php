<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Service;
use App\Models\SpaServiceRecord;
use App\Models\Staff;
use App\Services\NavigationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PortalController extends Controller
{
    public function appointments(Request $request, NavigationService $navigation)
    {
        $this->authorize('viewAny', Appointment::class);
        $user = $request->user();
        $query = Appointment::query()->with(['patient.user:id,name', 'service:id,name,service_type', 'staff.user:id,name']);

        if ($user->hasRole('patient')) {
            $query->where('patient_id', $user->patient?->getKey() ?? 0);
        } elseif ($user->hasPermission('payments.view_limited') && ! $user->hasPermission('payments.view_all')) {
            $query->where('recorded_by_user_id', $user->getKey());
        } elseif ($user->hasRole('doctor') || $user->hasRole('therapist')) {
            $query->where('staff_id', $user->staff?->getKey() ?? 0);
        }

        return $this->table($request, $navigation, 'Appointments', $query->orderByDesc('starts_at')->paginate(20), [
            ['Patient', fn (Appointment $appointment) => $appointment->patient?->user?->name ?? 'Patient'],
            ['Service', fn (Appointment $appointment) => $appointment->service?->name ?? 'Service'],
            ['Provider', fn (Appointment $appointment) => $appointment->staff?->user?->name ?? 'Unassigned'],
            ['Date & time', fn (Appointment $appointment) => $appointment->starts_at?->format('M j, Y g:i A')],
            ['Status', fn (Appointment $appointment) => str_replace('_', ' ', ucfirst($appointment->status))],
        ]);
    }

    public function patients(Request $request, NavigationService $navigation)
    {
        $this->authorize('viewAny', Patient::class);
        $user = $request->user();
        $query = Patient::query()->with('user:id,name,email');

        if ($user->hasPermission('patients.view_assigned') && ! $user->hasPermission('patients.view_all') && ! $user->hasPermission('patients.view_basic')) {
            $staffId = $user->staff?->getKey() ?? 0;
            $query->whereHas('appointments', fn (Builder $appointments) => $appointments->where('staff_id', $staffId));
        }

        return $this->table($request, $navigation, 'Patients / Clients', $query->orderBy('id')->paginate(20), [
            ['Name', fn (Patient $patient) => $patient->user?->name ?? 'Patient'],
            ['Email', fn (Patient $patient) => $patient->user?->email ?? ''],
            ['Phone', fn (Patient $patient) => $patient->phone ?? 'Not provided'],
            ['Status', fn (Patient $patient) => ucfirst($patient->status)],
        ]);
    }

    public function patientDetails(Request $request, Patient $patient, NavigationService $navigation)
    {
        $this->authorize('view', $patient);
        $user = $request->user();
        $appointments = $patient->appointments()->with(['service:id,name,service_type', 'staff.user:id,name']);

        if (! $user->hasRole('super_admin') && $user->staff) {
            $appointments->where('staff_id', $user->staff->getKey());
        }

        return view('portal.patient-detail', [
            'patient' => $patient->load('user:id,name,email'),
            'appointments' => $appointments->orderByDesc('starts_at')->limit(20)->get(),
            'navigation' => $navigation->forUser($user),
        ]);
    }

    public function services(Request $request, NavigationService $navigation)
    {
        $this->authorize('viewAny', Service::class);
        $query = Service::query()->with('category:id,name');

        if (! $request->user()->hasPermission('services.manage')) {
            $query->where('is_active', true);
        }

        return $this->table($request, $navigation, 'Services', $query->orderBy('name')->paginate(20), [
            ['Service', fn (Service $service) => $service->name],
            ['Category', fn (Service $service) => $service->category?->name ?? 'Uncategorized'],
            ['Type', fn (Service $service) => ucfirst($service->service_type)],
            ['Duration', fn (Service $service) => $service->duration_minutes.' min'],
            ['Price', fn (Service $service) => number_format((float) $service->price, 2)],
        ]);
    }

    public function staff(Request $request, NavigationService $navigation)
    {
        $this->authorize('viewAny', Staff::class);
        $staff = Staff::query()->with('user:id,name,email,role_id', 'user.role:id,name,slug')
            ->orderBy('staff_code')->paginate(20);

        return $this->table($request, $navigation, 'Staff', $staff, [
            ['Name', fn (Staff $member) => $member->user?->name ?? 'Staff member'],
            ['Role', fn (Staff $member) => $member->user?->role?->name ?? 'Staff'],
            ['Staff code', fn (Staff $member) => $member->staff_code],
            ['Specialty', fn (Staff $member) => $member->specialty ?? 'Not specified'],
            ['Status', fn (Staff $member) => $member->is_active ? 'Active' : 'Inactive'],
        ]);
    }

    public function consultations(Request $request, NavigationService $navigation)
    {
        $this->authorize('viewAny', Consultation::class);
        $user = $request->user();
        $query = Consultation::query()->with(['appointment.patient.user:id,name', 'doctor.user:id,name']);

        if (! $user->hasRole('super_admin')) {
            $query->where('doctor_id', $user->staff?->getKey() ?? 0);
        }

        return $this->table($request, $navigation, 'Consultations', $query->orderByDesc('created_at')->paginate(20), [
            ['Patient', fn (Consultation $consultation) => $consultation->appointment?->patient?->user?->name ?? 'Patient'],
            ['Clinician', fn (Consultation $consultation) => $consultation->doctor?->user?->name ?? 'Clinician'],
            ['Appointment', fn (Consultation $consultation) => $consultation->appointment?->starts_at?->format('M j, Y g:i A') ?? ''],
            ['Status', fn (Consultation $consultation) => ucfirst($consultation->status)],
        ]);
    }

    public function prescriptions(Request $request, NavigationService $navigation)
    {
        $this->authorize('viewAny', Prescription::class);
        $user = $request->user();
        $query = Prescription::query()->with(['consultation.appointment.patient.user:id,name', 'issuer.user:id,name']);

        if ($user->hasRole('doctor')) {
            $query->whereHas('consultation', fn (Builder $consultations) => $consultations->where('doctor_id', $user->staff?->getKey() ?? 0));
        } elseif ($user->hasRole('patient')) {
            $query->where('status', 'issued')->whereHas('consultation.appointment', fn (Builder $appointments) => $appointments->where('patient_id', $user->patient?->getKey() ?? 0));
        }

        return $this->table($request, $navigation, 'Prescriptions', $query->orderByDesc('created_at')->paginate(20), [
            ['Patient', fn (Prescription $prescription) => $prescription->consultation?->appointment?->patient?->user?->name ?? 'Patient'],
            ['Issued by', fn (Prescription $prescription) => $prescription->issuer?->user?->name ?? 'Clinician'],
            ['Issued', fn (Prescription $prescription) => $prescription->issued_at?->format('M j, Y') ?? 'Draft'],
            ['Status', fn (Prescription $prescription) => ucfirst($prescription->status)],
        ]);
    }

    public function treatments(Request $request, NavigationService $navigation)
    {
        $this->authorize('viewAny', SpaServiceRecord::class);
        $user = $request->user();
        $query = SpaServiceRecord::query()->with(['appointment.patient.user:id,name', 'appointment.service:id,name', 'therapist.user:id,name']);

        if ($user->hasRole('therapist')) {
            $query->where('therapist_id', $user->staff?->getKey() ?? 0);
        } elseif ($user->hasRole('patient')) {
            $query->where('status', 'completed')->whereHas('appointment', fn (Builder $appointments) => $appointments->where('patient_id', $user->patient?->getKey() ?? 0));
        }

        return $this->table($request, $navigation, 'Treatment Records', $query->orderByDesc('created_at')->paginate(20), [
            ['Client', fn (SpaServiceRecord $record) => $record->appointment?->patient?->user?->name ?? 'Client'],
            ['Treatment', fn (SpaServiceRecord $record) => $record->appointment?->service?->name ?? 'Spa service'],
            ['Therapist', fn (SpaServiceRecord $record) => $record->therapist?->user?->name ?? 'Therapist'],
            ['Date', fn (SpaServiceRecord $record) => $record->created_at?->format('M j, Y')],
            ['Status', fn (SpaServiceRecord $record) => ucfirst($record->status)],
        ]);
    }

    public function payments(Request $request, NavigationService $navigation)
    {
        $this->authorize('viewAny', Payment::class);
        $user = $request->user();
        $query = Payment::query()->with(['patient.user:id,name', 'appointment.service:id,name', 'receipt:id,payment_id,receipt_number']);

        if ($user->hasRole('patient')) {
            $query->where('patient_id', $user->patient?->getKey() ?? 0);
        }

        return $this->table($request, $navigation, 'Payments', $query->orderByDesc('created_at')->paginate(20), [
            ['Patient', fn (Payment $payment) => $payment->patient?->user?->name ?? 'Patient'],
            ['Service', fn (Payment $payment) => $payment->appointment?->service?->name ?? 'Payment'],
            ['Amount', fn (Payment $payment) => $payment->currency.' '.number_format((float) $payment->amount, 2)],
            ['Date', fn (Payment $payment) => $payment->paid_at?->format('M j, Y') ?? 'Pending'],
            ['Status', fn (Payment $payment) => ucfirst($payment->status)],
            ['Receipt', fn (Payment $payment) => $payment->receipt?->receipt_number ?? '—'],
        ]);
    }

    public function auditLogs(Request $request, NavigationService $navigation)
    {
        $this->authorize('viewAny', AuditLog::class);
        $logs = AuditLog::query()->with('user:id,name')->orderByDesc('created_at')->paginate(30);

        return $this->table($request, $navigation, 'Audit Logs', $logs, [
            ['User', fn (AuditLog $log) => $log->user?->name ?? 'System'],
            ['Action', fn (AuditLog $log) => $log->action],
            ['Record', fn (AuditLog $log) => $log->entity_type ? class_basename($log->entity_type).' #'.$log->entity_id : 'System'],
            ['Description', fn (AuditLog $log) => $log->description ?? ''],
            ['Time', fn (AuditLog $log) => $log->created_at?->format('M j, Y g:i A')],
        ]);
    }

    public function patientRecords(Request $request, Patient $patient, NavigationService $navigation)
    {
        $this->authorize('view', $patient);

        abort_unless($request->user()->hasRole('patient'), 403);

        $appointments = $patient->appointments()->where('status', 'completed')->with([
            'consultation' => fn (Builder $query) => $query->where('status', 'completed')->with('prescriptions.items'),
            'spaServiceRecord' => fn (Builder $query) => $query->where('status', 'completed'),
            'service:id,name,service_type',
        ])->orderByDesc('starts_at')->get();

        return view('portal.records', [
            'patient' => $patient,
            'appointments' => $appointments,
            'navigation' => $navigation->forUser($request->user()),
        ]);
    }

    public function reports(Request $request, NavigationService $navigation)
    {
        Gate::authorize('viewReports');
        $user = $request->user();
        $role = $user->role->slug;
        $metrics = match ($role) {
            'super_admin' => [
                ['Patients', Patient::query()->count()],
                ['Appointments', Appointment::query()->count()],
                ['Payments received', number_format((float) Payment::query()->where('status', 'paid')->sum('amount'), 2)],
            ],
            'receptionist' => [
                ['Appointments today', Appointment::query()->whereDate('starts_at', today())->count()],
                ['Pending appointments', Appointment::query()->where('status', 'pending')->count()],
            ],
            'doctor' => [
                ['Assigned consultations', Consultation::query()->where('doctor_id', $user->staff?->getKey() ?? 0)->count()],
                ['Completed consultations', Consultation::query()->where('doctor_id', $user->staff?->getKey() ?? 0)->where('status', 'completed')->count()],
            ],
            default => [
                ['Assigned treatments', SpaServiceRecord::query()->where('therapist_id', $user->staff?->getKey() ?? 0)->count()],
                ['Completed treatments', SpaServiceRecord::query()->where('therapist_id', $user->staff?->getKey() ?? 0)->where('status', 'completed')->count()],
            ],
        };

        return view('portal.report', [
            'metrics' => $metrics,
            'navigation' => $navigation->forUser($user),
        ]);
    }

    public function profile(Request $request, NavigationService $navigation)
    {
        return app(ProfileController::class)->edit($request, $navigation);
    }

    public function notifications(Request $request, NavigationService $navigation)
    {
        abort_unless($request->user()->hasPermission('notifications.view_own')
            || $request->user()->hasPermission('notifications.view_all'), 403);

        return $this->table($request, $navigation, 'Notifications', $request->user()->notifications()->latest()->paginate(20), [
            ['Notification', fn ($notification) => $notification->data['title'] ?? $notification->data['message'] ?? 'System notification'],
            ['Received', fn ($notification) => $notification->created_at?->format('M j, Y g:i A')],
            ['Read', fn ($notification) => $notification->read_at ? 'Read' : 'Unread'],
        ]);
    }

    private function table(Request $request, NavigationService $navigation, string $title, $records, array $columns)
    {
        return view('portal.index', [
            'title' => $title,
            'records' => $records,
            'columns' => $columns,
            'navigation' => $navigation->forUser($request->user()),
        ]);
    }
}

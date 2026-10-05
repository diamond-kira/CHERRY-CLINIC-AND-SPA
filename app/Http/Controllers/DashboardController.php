<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\SpaServiceRecord;
use App\Models\Staff;
use App\Services\NavigationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, NavigationService $navigation)
    {
        $user = $request->user();
        $role = $user->role->slug;
        $appointments = $this->appointmentsFor($user->role->slug, $user->patient?->getKey(), $user->staff?->getKey());
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $today = (clone $appointments)->whereBetween('starts_at', [$todayStart, $todayEnd]);

        $metrics = match ($role) {
            'super_admin' => $this->adminMetrics($today),
            'receptionist' => [
                ['Today\'s Appointments', (clone $today)->count()],
                ['Pending', (clone $today)->where('status', 'pending')->count()],
                ['Waiting', (clone $today)->where('status', 'checked_in')->count()],
                ['Completed', (clone $today)->where('status', 'completed')->count()],
            ],
            'doctor' => [
                ['Today\'s Appointments', (clone $today)->count()],
                ['Upcoming', (clone $appointments)->where('starts_at', '>=', now())->whereIn('status', ['pending', 'confirmed'])->count()],
                ['Waiting Patients', (clone $today)->where('status', 'checked_in')->count()],
                ['Patients Assigned', (clone $appointments)->distinct('patient_id')->count('patient_id')],
                ['Pending Consultations', $this->pendingConsultations($appointments)],
                ['Completed Consultations', $this->completedConsultations($user->staff?->getKey())],
                ['Completed Today', (clone $today)->where('status', 'completed')->count()],
            ],
            'therapist' => [
                ['Today\'s Spa Appointments', (clone $today)->count()],
                ['Upcoming Treatments', (clone $appointments)->where('starts_at', '>=', now())->whereIn('status', ['pending', 'confirmed'])->count()],
                ['Current Treatments', $this->currentTreatments($user->staff?->getKey())],
                ['Completed Today', (clone $today)->where('status', 'completed')->count()],
            ],
            default => $this->patientMetrics($appointments, $user->patient?->getKey()),
        };

        $upcomingAppointment = $role === 'patient'
            ? (clone $appointments)->with(['service', 'staff.user'])->where('starts_at', '>=', now())
                ->whereIn('status', ['pending', 'confirmed'])->orderBy('starts_at')->first()
            : null;

        $recentAppointments = (clone $appointments)->with(['patient.user', 'service', 'staff.user'])
            ->orderByDesc('starts_at')->limit(8)->get();
        $recentActivity = $role === 'super_admin'
            ? AuditLog::query()->with('user:id,name')->orderByDesc('created_at')->limit(6)->get()
            : collect();

        return view('portal.dashboard', [
            'role' => $role,
            'metrics' => $metrics,
            'upcomingAppointment' => $upcomingAppointment,
            'recentAppointments' => $recentAppointments,
            'recentActivity' => $recentActivity,
            'navigation' => $navigation->forUser($user),
        ]);
    }

    private function appointmentsFor(string $role, ?int $patientId, ?int $staffId): Builder
    {
        $query = Appointment::query();

        return match ($role) {
            'patient' => $query->where('patient_id', $patientId ?? 0),
            'doctor', 'therapist' => $query->where('staff_id', $staffId ?? 0),
            default => $query,
        };
    }

    private function adminMetrics(Builder $today): array
    {
        return [
            ['Total Patients', Patient::query()->count()],
            ['Total Staff', Staff::query()->count()],
            ['Active Staff', Staff::query()->where('is_active', true)->count()],
            ['Today\'s Appointments', (clone $today)->count()],
            ['Pending Today', (clone $today)->where('status', 'pending')->count()],
            ['Completed Today', (clone $today)->where('status', 'completed')->count()],
            ['Cancelled Today', (clone $today)->where('status', 'cancelled')->count()],
            ['Active Services', Service::query()->where('is_active', true)->count()],
            ['Revenue Today', number_format((float) Payment::query()->where('status', 'paid')->whereBetween('paid_at', [now()->startOfDay(), now()->endOfDay()])->sum('amount'), 2)],
            ['Revenue This Month', number_format((float) Payment::query()->where('status', 'paid')->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'), 2)],
        ];
    }

    private function patientMetrics(Builder $appointments, ?int $patientId): array
    {
        return [
            ['Upcoming', (clone $appointments)->where('starts_at', '>=', now())->whereIn('status', ['pending', 'confirmed'])->count()],
            ['Completed', (clone $appointments)->where('status', 'completed')->count()],
            ['Pending Payments', Payment::query()->where('patient_id', $patientId ?? 0)->where('status', 'pending')->count()],
        ];
    }

    private function pendingConsultations(Builder $appointments): int
    {
        return (clone $appointments)->whereIn('status', ['checked_in', 'in_progress'])
            ->whereDoesntHave('consultation', fn (Builder $query) => $query->where('status', 'completed'))
            ->count();
    }

    private function currentTreatments(?int $staffId): int
    {
        return SpaServiceRecord::query()->where('therapist_id', $staffId ?? 0)->where('status', 'in_progress')->count();
    }

    private function completedConsultations(?int $staffId): int
    {
        return Consultation::query()
            ->where('doctor_id', $staffId ?? 0)
            ->where('status', 'completed')
            ->count();
    }
}

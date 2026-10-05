<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Services\NavigationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentBookingController extends Controller
{
    public function create(Request $request, NavigationService $navigation)
    {
        $this->authorize('create', Appointment::class);

        return view('portal.appointment-create', [
            'services' => Service::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'service_type', 'duration_minutes', 'price']),
            'staff' => Staff::query()->where('is_active', true)->with('user:id,name,role_id')->orderBy('staff_code')->get(),
            'patients' => $request->user()->hasPermission('patients.create')
                ? Patient::query()->with('user:id,name')->orderBy('id')->get()
                : collect(),
            'navigation' => $navigation->forUser($request->user()),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Appointment::class);
        $user = $request->user();
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'staff_id' => ['required', 'integer', 'exists:staff,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'patient_id' => [$user->hasPermission('patients.create') ? 'required' : 'nullable', 'integer', 'exists:patients,id'],
        ]);

        $service = Service::query()->whereKey($validated['service_id'])->where('is_active', true)->firstOrFail();
        $staff = Staff::query()->whereKey($validated['staff_id'])->where('is_active', true)->with('user.role')->firstOrFail();
        $expectedRole = in_array($service->service_type, ['clinic', 'clinical'], true) ? 'doctor' : 'therapist';

        if ($staff->user?->role?->slug !== $expectedRole
            || ! $service->qualifiedStaff()->whereKey($staff->getKey())->wherePivot('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'staff_id' => 'This provider is not qualified for the selected service.',
            ]);
        }

        $startsAt = Carbon::parse($validated['starts_at']);
        $endsAt = $startsAt->copy()->addMinutes($service->duration_minutes);
        $scheduleExists = StaffSchedule::query()->where('staff_id', $staff->getKey())
            ->where('day_of_week', $startsAt->dayOfWeek)
            ->where('is_available', true)
            ->where('starts_at', '<=', $startsAt->format('H:i:s'))
            ->where('ends_at', '>=', $endsAt->format('H:i:s'))
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $startsAt->toDateString()))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', $startsAt->toDateString()))
            ->exists();

        if (! $scheduleExists) {
            throw ValidationException::withMessages([
                'starts_at' => 'The provider is not scheduled for the selected time.',
            ]);
        }

        $overlaps = Appointment::query()->where('staff_id', $staff->getKey())
            ->whereIn('status', ['pending', 'confirmed', 'checked_in', 'in_progress'])
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages([
                'starts_at' => 'The provider already has an appointment during this time.',
            ]);
        }

        $patientId = $user->hasRole('patient')
            ? $user->patient()->value('id')
            : $validated['patient_id'];

        abort_if(! $patientId, 403);

        $appointment = DB::transaction(function () use ($request, $patientId, $service, $staff, $startsAt, $endsAt): Appointment {
            $appointment = Appointment::query()->create([
                'patient_id' => $patientId,
                'service_id' => $service->getKey(),
                'staff_id' => $staff->getKey(),
                'created_by_user_id' => $request->user()->getKey(),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => 'pending',
            ]);

            AuditLog::query()->create([
                'user_id' => $request->user()->getKey(),
                'action' => 'appointment.created',
                'entity_type' => Appointment::class,
                'entity_id' => $appointment->getKey(),
                'description' => 'Appointment booked.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $appointment;
        });

        return redirect()->route($request->user()->dashboardRouteName())->with('status', 'Appointment request created.');
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        $this->authorize('cancel', $appointment);
        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
        ]);

        return back()->with('status', 'Appointment cancelled.');
    }

    public function confirm(Appointment $appointment)
    {
        $this->authorize('update', $appointment);
        abort_unless($appointment->status === 'pending', 409);

        $appointment->update(['status' => 'confirmed']);

        return back()->with('status', 'Appointment confirmed.');
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);
        abort_unless(in_array($appointment->status, ['pending', 'confirmed'], true), 409);
        $validated = $request->validate([
            'starts_at' => ['required', 'date', 'after:now'],
        ]);

        $service = $appointment->service()->where('is_active', true)->firstOrFail();
        $staff = Staff::query()->whereKey($appointment->staff_id)->where('is_active', true)->firstOrFail();
        $startsAt = Carbon::parse($validated['starts_at']);
        $endsAt = $startsAt->copy()->addMinutes($service->duration_minutes);
        $scheduleExists = StaffSchedule::query()->where('staff_id', $staff->getKey())
            ->where('day_of_week', $startsAt->dayOfWeek)
            ->where('is_available', true)
            ->where('starts_at', '<=', $startsAt->format('H:i:s'))
            ->where('ends_at', '>=', $endsAt->format('H:i:s'))
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $startsAt->toDateString()))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', $startsAt->toDateString()))
            ->exists();

        if (! $scheduleExists) {
            throw ValidationException::withMessages(['starts_at' => 'The provider is not scheduled for the selected time.']);
        }

        $overlaps = Appointment::query()->where('staff_id', $staff->getKey())->whereKeyNot($appointment->getKey())
            ->whereIn('status', ['pending', 'confirmed', 'checked_in', 'in_progress'])
            ->where('starts_at', '<', $endsAt)->where('ends_at', '>', $startsAt)->exists();

        if ($overlaps) {
            throw ValidationException::withMessages(['starts_at' => 'The provider already has an appointment during this time.']);
        }

        $appointment->update(['starts_at' => $startsAt, 'ends_at' => $endsAt]);

        return back()->with('status', 'Appointment rescheduled.');
    }

    public function checkIn(Appointment $appointment)
    {
        $this->authorize('checkIn', $appointment);
        abort_unless(in_array($appointment->status, ['pending', 'confirmed'], true), 409);

        $appointment->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        return back()->with('status', 'Patient checked in.');
    }
}

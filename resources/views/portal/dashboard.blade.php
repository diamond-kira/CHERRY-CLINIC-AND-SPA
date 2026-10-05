@extends('layouts.portal', ['title' => 'Dashboard'])

@section('content')
@php
    $titles = [
        'super_admin' => ['System overview', 'A clear view of today across the clinic and spa.'],
        'receptionist' => ['Front desk', 'Today’s schedule and patient flow.'],
        'doctor' => ['Clinical day', 'Your assigned appointments and consultations.'],
        'therapist' => ['Treatment day', 'Your assigned spa appointments and client care.'],
        'patient' => ['Your care', 'Appointments and services in one place.'],
    ];
    $titleData = $titles[$role] ?? $titles['patient'];
    $actions = match ($role) {
        'super_admin' => [
            ['Create appointment', route('admin.appointments.create')],
            ['Add patient', route('admin.patients.create')],
            ['Add staff', route('admin.staff.create')],
            ['Add service', route('admin.services.create')],
            ['Record payment', route('admin.payments.create')],
            ['Reports', route('admin.reports.index')],
        ],
        'receptionist' => [
            ['Book appointment', route('receptionist.appointments.create')],
            ['Register patient', route('receptionist.patients.create')],
            ['Check in', route('receptionist.appointments.index')],
            ['Record payment', route('receptionist.payments.create')],
        ],
        'doctor' => [
            ['My appointments', route('doctor.appointments.index')],
            ['Consultations', route('doctor.consultations.index')],
            ['Prescriptions', route('doctor.prescriptions.index')],
        ],
        'therapist' => [
            ['My appointments', route('therapist.appointments.index')],
            ['Treatment records', route('therapist.treatments.index')],
            ['Spa services', route('therapist.services.index')],
        ],
        default => [
            ['Book appointment', route('patient.appointments.create')],
            ['My appointments', route('patient.appointments.index')],
            ['Explore services', route('patient.services.index')],
            ['My records', route('patient.records')],
        ],
    };
@endphp
<div class="cz-page-head">
    <div>
        <p class="cz-eyebrow">{{ auth()->user()->role?->name }}</p>
        <h1 class="cz-page-title">{{ $titleData[0] }}</h1>
        <p class="cz-page-intro">{{ $titleData[1] }}</p>
    </div>
</div>

<section class="cz-metrics" aria-label="Dashboard metrics">
    @foreach ($metrics as [$label, $value])
        <div class="cz-metric">
            <div class="cz-metric-label">{{ $label }}</div>
            <div class="cz-metric-value">{{ $value }}</div>
        </div>
    @endforeach
</section>

@if ($role === 'patient' && $upcomingAppointment)
    <section class="cz-panel">
        <div class="cz-section-head"><h2 class="cz-section-title">Upcoming appointment</h2><a class="cz-action" href="{{ route('patient.appointments.index') }}">View appointments</a></div>
        <div class="cz-panel-grid">
            <div><div class="cz-detail-label">Service</div><div class="cz-detail-value">{{ $upcomingAppointment->service?->name }}</div></div>
            <div><div class="cz-detail-label">Date</div><div class="cz-detail-value">{{ $upcomingAppointment->starts_at?->format('l, F j · g:i A') }}</div></div>
            <div><div class="cz-detail-label">Provider</div><div class="cz-detail-value">{{ $upcomingAppointment->staff?->user?->name ?? 'To be assigned' }}</div></div>
        </div>
    </section>
@endif

<section>
    <div class="cz-section-head"><h2 class="cz-section-title">Quick actions</h2></div>
    <div class="cz-actions">
        @foreach ($actions as [$label, $href])
            <a class="cz-action" href="{{ $href }}">{{ $label }}</a>
        @endforeach
    </div>
</section>

<section>
    <div class="cz-section-head"><h2 class="cz-section-title">{{ $role === 'patient' ? 'Recent appointments' : 'Recent schedule' }}</h2></div>
    <div class="cz-table-wrap">
        <table class="cz-table">
            <thead><tr>
                @if ($role !== 'patient')<th>Patient</th>@endif
                <th>Service</th><th>Provider</th><th>Date &amp; time</th><th>Status</th>
            </tr></thead>
            <tbody>
            @forelse ($recentAppointments as $appointment)
                <tr>
                    @if ($role !== 'patient')<td>{{ $appointment->patient?->user?->name ?? 'Patient' }}</td>@endif
                    <td>{{ $appointment->service?->name ?? 'Service' }}</td>
                    <td>{{ $appointment->staff?->user?->name ?? 'Unassigned' }}</td>
                    <td>{{ $appointment->starts_at?->format('M j, Y g:i A') }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($appointment->status)) }}</td>
                </tr>
            @empty
                <tr><td class="cz-empty" colspan="5">No appointments to show.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

@if ($role === 'super_admin')
    <section class="mt-8">
        <div class="cz-section-head"><h2 class="cz-section-title">Recent system activity</h2><a class="cz-action" href="{{ route('admin.audit-logs.index') }}">View audit logs</a></div>
        <div class="cz-table-wrap">
            <table class="cz-table"><thead><tr><th>Action</th><th>User</th><th>Record</th><th>Time</th></tr></thead><tbody>
            @forelse ($recentActivity as $activity)
                <tr><td>{{ str_replace('.', ' ', ucfirst($activity->action)) }}</td><td>{{ $activity->user?->name ?? 'System' }}</td><td>{{ $activity->entity_type ? class_basename($activity->entity_type).' #'.$activity->entity_id : 'System' }}</td><td>{{ $activity->created_at?->format('M j, g:i A') }}</td></tr>
            @empty
                <tr><td class="cz-empty" colspan="4">No recent activity.</td></tr>
            @endforelse
            </tbody></table>
        </div>
    </section>
@endif
@endsection
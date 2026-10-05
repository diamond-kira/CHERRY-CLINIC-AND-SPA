@extends('layouts.portal', ['title' => 'Patient'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Patient / Client</p><h1 class="cz-page-title">{{ $patient->user?->name }}</h1></div></div>
<section class="cz-panel"><div class="cz-panel-grid">
    <div><div class="cz-detail-label">Email</div><div class="cz-detail-value">{{ $patient->user?->email }}</div></div>
    <div><div class="cz-detail-label">Phone</div><div class="cz-detail-value">{{ $patient->phone ?: 'Not provided' }}</div></div>
    <div><div class="cz-detail-label">Status</div><div class="cz-detail-value">{{ ucfirst($patient->status) }}</div></div>
</div></section>
<div class="cz-section-head"><h2 class="cz-section-title">{{ auth()->user()->hasRole('doctor') ? 'Assigned appointments' : 'Appointments' }}</h2></div>
<div class="cz-table-wrap"><table class="cz-table"><thead><tr><th>Service</th><th>Provider</th><th>Date</th><th>Status</th></tr></thead><tbody>
@forelse ($appointments as $appointment)
<tr><td>{{ $appointment->service?->name }}</td><td>{{ $appointment->staff?->user?->name ?? 'Unassigned' }}</td><td>{{ $appointment->starts_at?->format('M j, Y g:i A') }}</td><td>{{ ucfirst($appointment->status) }}</td></tr>
@empty
<tr><td class="cz-empty" colspan="4">No appointments to show.</td></tr>
@endforelse
</tbody></table></div>
@endsection
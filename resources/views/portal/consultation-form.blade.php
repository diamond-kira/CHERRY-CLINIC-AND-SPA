@extends('layouts.portal', ['title' => 'Consultation'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Clinical record</p><h1 class="cz-page-title">{{ $consultation ? 'Consultation' : 'Start consultation' }}</h1><p class="cz-page-intro">{{ $appointment->patient?->user?->name }} · {{ $appointment->service?->name }} · {{ $appointment->starts_at?->format('M j, Y g:i A') }}</p></div></div>
@if ($errors->any())<div class="cz-flash" role="alert">{{ $errors->first() }}</div>@endif
<form class="cz-form" method="POST" action="{{ $consultation ? route(auth()->user()->hasRole('doctor') ? 'doctor.consultations.update' : 'admin.consultations.update', $consultation) : route(auth()->user()->hasRole('doctor') ? 'doctor.appointments.consultation.store' : 'admin.appointments.consultation.store', $appointment) }}">
    @csrf
    @if ($consultation) @method('PATCH') @endif
    <div class="cz-field"><label for="presenting_complaint">Presenting complaint</label><textarea class="cz-input" id="presenting_complaint" name="presenting_complaint" rows="3">{{ old('presenting_complaint', $consultation?->presenting_complaint) }}</textarea></div>
    <div class="cz-field"><label for="examination_notes">Examination notes</label><textarea class="cz-input" id="examination_notes" name="examination_notes" rows="5">{{ old('examination_notes', $consultation?->examination_notes) }}</textarea></div>
    @if ($consultation)
        <div class="cz-field"><label for="diagnosis">Diagnosis</label><textarea class="cz-input" id="diagnosis" name="diagnosis" rows="3">{{ old('diagnosis', $consultation->diagnosis) }}</textarea></div>
        <div class="cz-field"><label for="treatment_plan">Treatment plan</label><textarea class="cz-input" id="treatment_plan" name="treatment_plan" rows="4">{{ old('treatment_plan', $consultation->treatment_plan) }}</textarea></div>
    @endif
    <button class="cz-button" type="submit">{{ $consultation ? 'Save consultation' : 'Start consultation' }}</button>
</form>
@if ($consultation && $consultation->status === 'in_progress')
    <form method="POST" action="{{ route(auth()->user()->hasRole('doctor') ? 'doctor.consultations.complete' : 'admin.consultations.complete', $consultation) }}" class="mt-4">@csrf<button class="cz-button cz-button-secondary" type="submit">Complete consultation</button></form>
@endif
@endsection
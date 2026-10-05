@extends('layouts.portal', ['title' => 'Treatment Record'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Spa treatment record</p><h1 class="cz-page-title">{{ $record ? 'Treatment record' : 'Start treatment' }}</h1><p class="cz-page-intro">{{ $appointment->patient?->user?->name }} · {{ $appointment->service?->name }} · {{ $appointment->starts_at?->format('M j, Y g:i A') }}</p></div></div>
@if ($errors->any())<div class="cz-flash" role="alert">{{ $errors->first() }}</div>@endif
<form class="cz-form" method="POST" action="{{ $record ? route(auth()->user()->hasRole('therapist') ? 'therapist.treatments.update' : 'admin.treatments.update', $record) : route(auth()->user()->hasRole('therapist') ? 'therapist.appointments.treatment.store' : 'admin.appointments.treatment.store', $appointment) }}">
    @csrf
    @if ($record) @method('PATCH') @endif
    <div class="cz-field"><label for="treatment_notes">Treatment notes</label><textarea class="cz-input" id="treatment_notes" name="treatment_notes" rows="5">{{ old('treatment_notes', $record?->treatment_notes) }}</textarea></div>
    <div class="cz-field"><label for="products_text">Products used (one per line)</label><textarea class="cz-input" id="products_text" name="products_text" rows="3">{{ old('products_text', implode("\n", $record?->products_used ?? [])) }}</textarea></div>
    <div class="cz-field"><label for="recommendations">Recommendations</label><textarea class="cz-input" id="recommendations" name="recommendations" rows="4">{{ old('recommendations', $record?->recommendations) }}</textarea></div>
    <button class="cz-button" type="submit">{{ $record ? 'Save treatment' : 'Start treatment' }}</button>
</form>
@if ($record && $record->status === 'in_progress')
    <form method="POST" action="{{ route(auth()->user()->hasRole('therapist') ? 'therapist.treatments.complete' : 'admin.treatments.complete', $record) }}" class="mt-4">@csrf<button class="cz-button cz-button-secondary" type="submit">Complete treatment</button></form>
@endif
@endsection
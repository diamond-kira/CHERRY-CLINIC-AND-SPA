@extends('layouts.portal', ['title' => 'Issue Prescription'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Clinical care</p><h1 class="cz-page-title">Issue prescription</h1><p class="cz-page-intro">Select a consultation assigned to you and enter medication instructions.</p></div></div>
@if ($errors->any())<div class="cz-flash" role="alert">{{ $errors->first() }}</div>@endif
<form class="cz-form" method="POST" action="{{ route(auth()->user()->hasRole('doctor') ? 'doctor.prescriptions.store' : 'admin.prescriptions.store') }}">
    @csrf
    <div class="cz-field"><label for="consultation_id">Consultation</label><select class="cz-input" id="consultation_id" name="consultation_id" required><option value="">Choose consultation</option>@foreach ($consultations as $consultation)<option value="{{ $consultation->id }}">{{ $consultation->appointment?->patient?->user?->name }} · {{ $consultation->appointment?->service?->name }} · {{ $consultation->created_at?->format('M j, Y') }}</option>@endforeach</select></div>
    <div class="cz-field"><label for="notes">Prescription notes</label><textarea class="cz-input" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea></div>
    <fieldset class="cz-field"><legend>Medication</legend>
        <div class="cz-field"><label for="medication_name">Medication name</label><input class="cz-input" id="medication_name" name="items[0][medication_name]" required></div>
        <div class="cz-field"><label for="dosage">Dosage</label><input class="cz-input" id="dosage" name="items[0][dosage]" required></div>
        <div class="cz-field"><label for="route">Route</label><input class="cz-input" id="route" name="items[0][route]"></div>
        <div class="cz-field"><label for="frequency">Frequency</label><input class="cz-input" id="frequency" name="items[0][frequency]" required></div>
        <div class="cz-field"><label for="duration">Duration</label><input class="cz-input" id="duration" name="items[0][duration]"></div>
        <div class="cz-field"><label for="quantity">Quantity</label><input class="cz-input" id="quantity" name="items[0][quantity]"></div>
        <div class="cz-field"><label for="instructions">Instructions</label><textarea class="cz-input" id="instructions" name="items[0][instructions]" rows="2"></textarea></div>
    </fieldset>
    <button class="cz-button" type="submit">Issue prescription</button>
</form>
@endsection
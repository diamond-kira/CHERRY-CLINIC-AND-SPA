@extends('layouts.portal', ['title' => 'Book Appointment'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Appointments</p><h1 class="cz-page-title">Book an appointment</h1><p class="cz-page-intro">Choose a service, qualified provider, and available time.</p></div></div>
@if ($errors->any())<div class="cz-flash" role="alert">{{ $errors->first() }}</div>@endif
<form class="cz-form" method="POST" action="{{ route(auth()->user()->role->slug.'.appointments.store') }}">
    @csrf
    @if ($patients->isNotEmpty())
        <div class="cz-field"><label for="patient_id">Patient</label><select class="cz-input" id="patient_id" name="patient_id" required><option value="">Choose a patient</option>@foreach ($patients as $patient)<option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>{{ $patient->user?->name }}</option>@endforeach</select></div>
    @endif
    <div class="cz-field"><label for="service_id">Service</label><select class="cz-input" id="service_id" name="service_id" required><option value="">Choose a service</option>@foreach ($services as $service)<option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>{{ $service->name }} · {{ $service->duration_minutes }} min · {{ number_format((float) $service->price, 2) }}</option>@endforeach</select></div>
    <div class="cz-field"><label for="staff_id">Provider</label><select class="cz-input" id="staff_id" name="staff_id" required><option value="">Choose a provider</option>@foreach ($staff as $member)<option value="{{ $member->id }}" @selected(old('staff_id') == $member->id)>{{ $member->user?->name }} · {{ $member->user?->role?->name }}</option>@endforeach</select></div>
    <div class="cz-field"><label for="starts_at">Date and time</label><input class="cz-input" id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" required></div>
    <div><button class="cz-button" type="submit">Request appointment</button></div>
</form>
@endsection
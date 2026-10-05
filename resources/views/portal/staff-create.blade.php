@extends('layouts.portal', ['title' => 'Add Staff'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Staff management</p><h1 class="cz-page-title">Create staff account</h1><p class="cz-page-intro">Staff roles are assigned by the super admin.</p></div></div>
@if ($errors->any())<div class="cz-flash" role="alert">{{ $errors->first() }}</div>@endif
<form class="cz-form" method="POST" action="{{ route('admin.staff.store') }}">
    @csrf
    <div class="cz-field"><label for="name">Full name</label><input class="cz-input" id="name" name="name" value="{{ old('name') }}" required></div>
    <div class="cz-field"><label for="email">Email address</label><input class="cz-input" id="email" name="email" type="email" value="{{ old('email') }}" required></div>
    <div class="cz-field"><label for="role_slug">Staff role</label><select class="cz-input" id="role_slug" name="role_slug" required><option value="">Select role</option><option value="receptionist">Receptionist</option><option value="doctor">Doctor / Clinician</option><option value="therapist">Therapist / Spa Staff</option></select></div>
    <div class="cz-field"><label for="staff_code">Staff code</label><input class="cz-input" id="staff_code" name="staff_code" value="{{ old('staff_code') }}" required></div>
    <div class="cz-field"><label for="license_number">License number</label><input class="cz-input" id="license_number" name="license_number" value="{{ old('license_number') }}"></div>
    <div class="cz-field"><label for="specialty">Specialty</label><input class="cz-input" id="specialty" name="specialty" value="{{ old('specialty') }}"></div>
    <fieldset class="cz-field"><legend>Qualified services</legend>@foreach ($services as $service)<label><input type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, old('service_ids', [])))> {{ $service->name }} ({{ $service->service_type }})</label>@endforeach</fieldset>
    <fieldset class="cz-field"><legend>Weekly availability</legend><div class="flex flex-wrap gap-3">@foreach ([0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'] as $day => $label)<label><input type="checkbox" name="days[]" value="{{ $day }}" @checked(in_array($day, old('days', [])))> {{ $label }}</label>@endforeach</div></fieldset>
    <div class="cz-field"><label for="starts_at">Available from</label><input class="cz-input" id="starts_at" name="starts_at" type="time" value="{{ old('starts_at', '09:00') }}"></div>
    <div class="cz-field"><label for="ends_at">Available until</label><input class="cz-input" id="ends_at" name="ends_at" type="time" value="{{ old('ends_at', '17:00') }}"></div>
    <div class="cz-field"><label for="password">Temporary password</label><input class="cz-input" id="password" name="password" type="password" required></div>
    <div class="cz-field"><label for="password_confirmation">Confirm password</label><input class="cz-input" id="password_confirmation" name="password_confirmation" type="password" required></div>
    <button class="cz-button" type="submit">Create staff account</button>
</form>
@endsection
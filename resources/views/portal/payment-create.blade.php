@extends('layouts.portal', ['title' => 'Record Payment'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Payments</p><h1 class="cz-page-title">Record payment</h1><p class="cz-page-intro">A receipt is issued automatically for payments marked paid.</p></div></div>
@if ($errors->any())<div class="cz-flash" role="alert">{{ $errors->first() }}</div>@endif
<form class="cz-form" method="POST" action="{{ route(auth()->user()->role->slug.'.payments.store') }}">
    @csrf
    <div class="cz-field"><label for="patient_id">Patient</label><select class="cz-input" name="patient_id" id="patient_id" required><option value="">Choose a patient</option>@foreach ($patients as $patient)<option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>{{ $patient->user?->name }}</option>@endforeach</select></div>
    <div class="cz-field"><label for="appointment_id">Appointment (optional)</label><select class="cz-input" name="appointment_id" id="appointment_id"><option value="">Not linked</option>@foreach ($appointments as $appointment)<option value="{{ $appointment->id }}" @selected(old('appointment_id') == $appointment->id)>{{ $appointment->patient?->user?->name }} · {{ $appointment->service?->name }} · {{ $appointment->starts_at?->format('M j, Y') }}</option>@endforeach</select></div>
    <div class="cz-field"><label for="amount">Amount</label><input class="cz-input" name="amount" id="amount" type="number" min="0.01" step="0.01" value="{{ old('amount') }}" required></div>
    <div class="cz-field"><label for="currency">Currency code</label><input class="cz-input" name="currency" id="currency" maxlength="3" value="{{ old('currency', 'USD') }}" required></div>
    <div class="cz-field"><label for="status">Payment status</label><select class="cz-input" name="status" id="status" required><option value="paid">Paid</option><option value="pending">Pending</option></select></div>
    <div class="cz-field"><label for="payment_method">Method</label><select class="cz-input" name="payment_method" id="payment_method"><option value="">Choose method</option><option value="cash">Cash</option><option value="card">Card</option><option value="bank_transfer">Bank transfer</option><option value="other">Other</option></select></div>
    <button class="cz-button" type="submit">Record payment</button>
</form>
@endsection
@extends('layouts.portal', ['title' => 'Register Patient'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Patient / Client</p><h1 class="cz-page-title">Register patient</h1><p class="cz-page-intro">Create a patient account and profile.</p></div></div>
@if ($errors->any())<div class="cz-flash" role="alert">{{ $errors->first() }}</div>@endif
<form class="cz-form" method="POST" action="{{ route(auth()->user()->role->slug.'.patients.store') }}">
    @csrf
    <div class="cz-field"><label for="name">Full name</label><input class="cz-input" id="name" name="name" value="{{ old('name') }}" required></div>
    <div class="cz-field"><label for="email">Email address</label><input class="cz-input" id="email" name="email" type="email" value="{{ old('email') }}" required></div>
    <div class="cz-field"><label for="phone">Phone</label><input class="cz-input" id="phone" name="phone" type="tel" value="{{ old('phone') }}"></div>
    <div class="cz-field"><label for="password">Temporary password</label><input class="cz-input" id="password" name="password" type="password" required></div>
    <div class="cz-field"><label for="password_confirmation">Confirm password</label><input class="cz-input" id="password_confirmation" name="password_confirmation" type="password" required></div>
    <button class="cz-button" type="submit">Create patient account</button>
</form>
@endsection
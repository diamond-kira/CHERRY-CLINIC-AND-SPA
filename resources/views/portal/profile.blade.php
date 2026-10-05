@extends('layouts.portal', ['title' => 'My Profile'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Account</p><h1 class="cz-page-title">My Profile</h1></div></div>
@if ($errors->any())<div class="cz-flash" role="alert">{{ $errors->first() }}</div>@endif
<section class="cz-panel"><div class="cz-panel-grid">
    <div><div class="cz-detail-label">Role</div><div class="cz-detail-value">{{ $user->role?->name }}</div></div>
    @if ($user->staff)<div><div class="cz-detail-label">Staff code</div><div class="cz-detail-value">{{ $user->staff->staff_code }}</div></div>@endif
</div></section>
<form class="cz-form" method="POST" action="{{ route('profile.update') }}">
    @csrf @method('PATCH')
    <div class="cz-field"><label for="name">Name</label><input class="cz-input" id="name" name="name" value="{{ old('name', $user->name) }}" required></div>
    <div class="cz-field"><label for="email">Email</label><input class="cz-input" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required></div>
    @if ($user->patient)<div class="cz-field"><label for="phone">Phone</label><input class="cz-input" id="phone" name="phone" value="{{ old('phone', $user->patient->phone) }}"></div>@endif
    <button class="cz-button" type="submit">Save profile</button>
</form>
<section class="cz-panel">
    <div class="cz-section-head"><h2 class="cz-section-title">Change password</h2></div>
    <form class="cz-form" method="POST" action="{{ route('profile.password.update') }}">
        @csrf @method('PATCH')
        <div class="cz-field"><label for="current_password">Current password</label><input class="cz-input" id="current_password" name="current_password" type="password" required></div>
        <div class="cz-field"><label for="password">New password</label><input class="cz-input" id="password" name="password" type="password" required></div>
        <div class="cz-field"><label for="password_confirmation">Confirm new password</label><input class="cz-input" id="password_confirmation" name="password_confirmation" type="password" required></div>
        <button class="cz-button" type="submit">Change password</button>
    </form>
</section>
@endsection
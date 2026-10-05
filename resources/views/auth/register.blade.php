@extends('layouts.guest', ['title' => 'Create account'])

@section('content')
<form class="cz-auth-form" method="POST" action="{{ route('register.store') }}">
    @csrf
    <h2>Create your account</h2>
    <p>Register as a patient or spa client.</p>
    @if ($errors->any())
        <div class="cz-flash" role="alert">{{ $errors->first() }}</div>
    @endif
    <div class="cz-form">
        <div class="cz-field">
            <label for="name">Full name</label>
            <input class="cz-input" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required>
        </div>
        <div class="cz-field">
            <label for="email">Email address</label>
            <input class="cz-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
        </div>
        <div class="cz-field">
            <label for="password">Password</label>
            <input class="cz-input" id="password" name="password" type="password" autocomplete="new-password" required>
        </div>
        <div class="cz-field">
            <label for="password_confirmation">Confirm password</label>
            <input class="cz-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>
        <button class="cz-button" type="submit">Create account</button>
    </div>
    <div class="cz-auth-footer">Already registered? <a href="{{ route('login') }}">Sign in</a></div>
</form>
@endsection
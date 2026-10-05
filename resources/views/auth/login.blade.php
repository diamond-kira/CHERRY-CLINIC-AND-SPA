@extends('layouts.guest', ['title' => 'Sign in'])

@section('content')
<form class="cz-auth-form" method="POST" action="{{ route('login') }}">
    @csrf
    <h2>Welcome back</h2>
    <p>Sign in to continue to your account.</p>
    @if ($errors->any())
        <div class="cz-flash" role="alert">{{ $errors->first() }}</div>
    @endif
    <div class="cz-form">
        <div class="cz-field">
            <label for="email">Email address</label>
            <input class="cz-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        </div>
        <div class="cz-field">
            <label for="password">Password</label>
            <input class="cz-input" id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <label><input type="checkbox" name="remember" value="1"> Remember me</label>
        <button class="cz-button" type="submit">Sign in</button>
    </div>
    <div class="cz-auth-footer">New to Cherry Zephyr? <a href="{{ route('register') }}">Create a patient account</a></div>
    <div class="cz-auth-footer"><a href="{{ route('password.request') }}">Forgot your password?</a></div>
</form>
@endsection
@extends('layouts.portal', ['title' => 'Settings'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Administration</p><h1 class="cz-page-title">Settings</h1><p class="cz-page-intro">System settings are private to authorized administrators.</p></div></div>
@if ($errors->any())<div class="cz-flash" role="alert">{{ $errors->first() }}</div>@endif
<form class="cz-form" method="POST" action="{{ route('admin.settings.update') }}">
    @csrf @method('PATCH')
    @forelse ($settings as $setting)
        <div class="cz-field"><label for="setting-{{ $setting->id }}">{{ str_replace(['.', '_', '-'], ' ', ucfirst($setting->setting_key)) }}</label><input class="cz-input" id="setting-{{ $setting->id }}" name="settings[{{ $setting->setting_key }}]" value="{{ old('settings.'.$setting->setting_key, $setting->value) }}"></div>
    @empty
        <p class="cz-page-intro">No settings have been configured.</p>
    @endforelse
    <button class="cz-button" type="submit">Save settings</button>
</form>
@endsection
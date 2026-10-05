@extends('layouts.portal', ['title' => 'Add Service'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Service catalogue</p><h1 class="cz-page-title">Add service</h1><p class="cz-page-intro">Service prices and provider qualifications are managed by the super admin.</p></div></div>
@if ($errors->any())<div class="cz-flash" role="alert">{{ $errors->first() }}</div>@endif
<form class="cz-form" method="POST" action="{{ route('admin.services.store') }}">
    @csrf
    <div class="cz-field"><label for="name">Service name</label><input class="cz-input" id="name" name="name" value="{{ old('name') }}" required></div>
    <div class="cz-field"><label for="category_name">Category</label><input class="cz-input" id="category_name" name="category_name" value="{{ old('category_name') }}" required></div>
    <div class="cz-field"><label for="service_type">Service type</label><select class="cz-input" id="service_type" name="service_type" required><option value="">Choose type</option><option value="clinic">Clinic</option><option value="spa">Spa</option></select></div>
    <div class="cz-field"><label for="duration_minutes">Duration in minutes</label><input class="cz-input" id="duration_minutes" name="duration_minutes" type="number" min="5" max="600" value="{{ old('duration_minutes', 30) }}" required></div>
    <div class="cz-field"><label for="price">Price</label><input class="cz-input" id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price') }}" required></div>
    <div class="cz-field"><label for="description">Description</label><textarea class="cz-input" id="description" name="description" rows="4">{{ old('description') }}</textarea></div>
    <button class="cz-button" type="submit">Save service</button>
</form>
@endsection
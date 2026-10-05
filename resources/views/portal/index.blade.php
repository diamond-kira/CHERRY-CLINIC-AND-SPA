@extends('layouts.portal')

@section('content')
<div class="cz-page-head">
    <div><p class="cz-eyebrow">Workspace</p><h1 class="cz-page-title">{{ $title }}</h1></div>
    @if ($title === 'Appointments' && auth()->user()->can('create', App\Models\Appointment::class))
        <a class="cz-button" href="{{ route(auth()->user()->role->slug.'.appointments.create') }}">Book appointment</a>
    @elseif ($title === 'Patients / Clients' && auth()->user()->can('create', App\Models\Patient::class))
        <a class="cz-button" href="{{ route(auth()->user()->role->slug.'.patients.create') }}">Register patient</a>
    @elseif ($title === 'Services' && auth()->user()->hasPermission('services.manage'))
        <a class="cz-button" href="{{ route('admin.services.create') }}">Add service</a>
    @elseif ($title === 'Staff' && auth()->user()->can('create', App\Models\Staff::class))
        <a class="cz-button" href="{{ route('admin.staff.create') }}">Add staff</a>
    @elseif ($title === 'Prescriptions' && auth()->user()->can('create', App\Models\Prescription::class))
        <a class="cz-button" href="{{ route(auth()->user()->hasRole('doctor') ? 'doctor.prescriptions.create' : 'admin.prescriptions.create') }}">Issue prescription</a>
    @endif
</div>
<div class="cz-table-wrap">
    <table class="cz-table">
        <thead><tr>@foreach ($columns as $column)<th>{{ $column[0] }}</th>@endforeach</tr></thead>
        <tbody>
        @forelse ($records as $record)
            <tr>@foreach ($columns as $column)<td>{{ $column[1]($record) }}</td>@endforeach</tr>
        @empty
            <tr><td class="cz-empty" colspan="{{ count($columns) }}">No records to show.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $records->links() }}</div>
@endsection
@extends('layouts.portal', ['title' => 'My Records'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Private health information</p><h1 class="cz-page-title">My Records</h1><p class="cz-page-intro">Finalized consultations, prescriptions, and completed treatments.</p></div></div>
@forelse ($appointments as $appointment)
    <section class="cz-panel">
        <div class="cz-section-head"><h2 class="cz-section-title">{{ $appointment->service?->name }} · {{ $appointment->starts_at?->format('M j, Y') }}</h2></div>
        @if ($appointment->consultation)
            <div class="cz-panel-grid">
                <div><div class="cz-detail-label">Diagnosis</div><div class="cz-detail-value">{{ $appointment->consultation->diagnosis ?: 'No diagnosis recorded' }}</div></div>
                <div><div class="cz-detail-label">Treatment plan</div><div class="cz-detail-value">{{ $appointment->consultation->treatment_plan ?: 'No plan recorded' }}</div></div>
            </div>
            @foreach ($appointment->consultation->prescriptions->where('status', 'issued') as $prescription)
                <div class="mt-5"><h3 class="cz-section-title">Prescription</h3>
                    @foreach ($prescription->items as $item)
                        <div class="cz-detail-value">{{ $item->medication_name }} · {{ $item->dosage }} · {{ $item->frequency }}</div>
                    @endforeach
                </div>
            @endif
        @endif
        @if ($appointment->spaServiceRecord)
            <div class="cz-panel-grid">
                <div><div class="cz-detail-label">Treatment notes</div><div class="cz-detail-value">{{ $appointment->spaServiceRecord->treatment_notes ?: 'No notes recorded' }}</div></div>
                <div><div class="cz-detail-label">Recommendations</div><div class="cz-detail-value">{{ $appointment->spaServiceRecord->recommendations ?: 'No recommendations recorded' }}</div></div>
            </div>
        @endif
    </section>
@empty
    <p class="cz-empty">Finalized records will appear here.</p>
@endforelse
@endsection
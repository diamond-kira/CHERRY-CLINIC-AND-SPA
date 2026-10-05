@extends('layouts.portal', ['title' => 'Reports'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Reporting</p><h1 class="cz-page-title">Reports</h1><p class="cz-page-intro">Summary data limited to your authorized operational scope.</p></div></div>
<section class="cz-metrics">
    @foreach ($metrics as [$label, $value])
        <div class="cz-metric"><div class="cz-metric-label">{{ $label }}</div><div class="cz-metric-value">{{ $value }}</div></div>
    @endforeach
</section>
@endsection
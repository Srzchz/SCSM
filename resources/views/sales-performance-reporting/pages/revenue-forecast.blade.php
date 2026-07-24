{{--
    resources/views/sales-performance-reporting/pages/revenue-forecast.blade.php
    Route: GET /sales-performance-reporting/revenue-forecast -> RevenueForecastController@index

    CHANGED: the growth/deal-close/seasonality sliders are removed. The
    forecast is now fully automatic — Linear Regression and Weighted
    Moving Average, both computed live from sales_orders by
    RevenueForecastService and stored (labeled by method) in
    sales_performance_reporting_forecasts.
--}}
@extends('layouts.app')

@section('title', 'Revenue Forecast')

@push('styles')
    @include('sales-performance-reporting.partials.styles')
@endpush

@section('content')

<div class="spr-page">
    <div class="page-header">
        <div>
            <h1 class="section-title">Revenue Forecast</h1>
            <p class="section-hint">Automated projection for {{ $period }}, generated from actual sales history.</p>
        </div>
    </div>

    <section class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="card stat-card">
            <div class="stat-label">&#128200; Linear Regression EOQ</div>
            <div class="stat-value">&#8369;{{ number_format($linearEoq / 1000, 1) }}K</div>
            <div class="stat-sub neutral">Trend-line projection</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">&#128202; Weighted Moving Avg EOQ</div>
            <div class="stat-value">&#8369;{{ number_format($wmaEoq / 1000, 1) }}K</div>
            <div class="stat-sub {{ $pctVsLastQuarter >= 0 ? '' : 'warn' }}">
                {{ $pctVsLastQuarter >= 0 ? '+' : '' }}{{ $pctVsLastQuarter }}% avg vs last quarter
            </div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">&#8987; Days Remaining</div>
            <div class="stat-value">{{ $daysRemaining }}</div>
            <div class="stat-sub neutral">Until end of {{ $period }}</div>
        </div>
    </section>

    <section class="card panel">
        <h2>Monthly revenue trend and projection</h2>
        <p class="panel-sub">
            Both methods are recalculated automatically from your sales data every time this page loads — no manual inputs.
        </p>
        <div class="chart-wrap" style="height:340px;">
            <canvas id="forecastChart"></canvas>
        </div>
        <div class="legend-row">
            <span><span class="legend-dot" style="background:var(--accent-green)"></span>Actual</span>
            <span><span class="legend-dot" style="background:var(--line-forecast)"></span>Linear Regression</span>
            <span><span class="legend-dot" style="background:#7ED8FF"></span>Weighted Moving Average</span>
            <span><span class="legend-dot" style="background:#b7aef0"></span>Today</span>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    const months     = @json($months);
    const actual     = @json($actual);      // null past "today" — actuals stop, chart shows a gap
    const linearLine = @json($linearLine);   // null before "today" — forecast line starts at today
    const wmaLine    = @json($wmaLine);
    const todayIdx   = {{ $todayIdx }};

    // Vertical "today" marker plugin — no external plugin dependency required
    const todayMarkerPlugin = {
        id: 'todayMarker',
        afterDraw(chart){
            const xScale = chart.scales.x;
            const yScale = chart.scales.y;
            const x = xScale.getPixelForValue(todayIdx);
            const ctx = chart.ctx;
            ctx.save();
            ctx.beginPath();
            ctx.setLineDash([5,4]);
            ctx.moveTo(x, yScale.top);
            ctx.lineTo(x, yScale.bottom);
            ctx.strokeStyle = '#b7aef0';
            ctx.lineWidth = 2;
            ctx.stroke();
            ctx.restore();
        }
    };

    new Chart(document.getElementById('forecastChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Actual', data: actual,
                    borderColor: '#8bc34a', backgroundColor: 'rgba(139,195,74,0.15)',
                    fill: true, tension: .35, pointRadius: 0, borderWidth: 3
                },
                {
                    label: 'Linear Regression', data: linearLine,
                    borderColor: '#3a3a4a', backgroundColor: 'rgba(58,58,74,0.06)',
                    fill: false, tension: .35, pointRadius: 0, borderWidth: 2, borderDash: [6, 4]
                },
                {
                    label: 'Weighted Moving Average', data: wmaLine,
                    borderColor: '#7ED8FF', backgroundColor: 'rgba(126,216,255,0.08)',
                    fill: false, tension: .35, pointRadius: 0, borderWidth: 2, borderDash: [2, 3]
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { color: '#7a7398', font: { size: 11 } }, grid: { color: '#e7e4fb' } },
                x: { ticks: { color: '#7a7398', font: { size: 11 } }, grid: { display: false } }
            }
        },
        plugins: [todayMarkerPlugin]
    });
</script>
@endpush

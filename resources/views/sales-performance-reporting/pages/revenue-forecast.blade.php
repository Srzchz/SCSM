{{--
    resources/views/pages/revenue-forecast.blade.php
    Route example: Route::get('/revenue-forecast', fn () => view('pages.revenue-forecast', ['active' => 'revenue-forecast']));
--}}
@extends('layouts.app')

@section('title', 'Revenue Forecast')


@section('content')

<link rel="stylesheet" href="styles.css">
    <section class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="card stat-card">
            <div class="stat-label">$ EOQ Forecast</div>
            <div class="stat-value" id="kpiEoq">$4.6M</div>
            <div class="stat-sub" id="kpiEoqSub">+9.5% vs actual Q1</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">&#8776; Confidence Range</div>
            <div class="stat-value" id="kpiConfidence" style="font-size:1.5rem;">$4.4M – $4.8M</div>
            <div class="stat-sub neutral">Based on current assumptions</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">&#8987; Days Remaining</div>
            <div class="stat-value">23</div>
            <div class="stat-sub neutral">Until end of quarter</div>
        </div>
    </section>

    @if (session('success'))
        <div class="card panel" style="background:#e9f5d3; border-color:#c9e6a0; color:#3a5c22; font-weight:700; margin-bottom:22px;">
            &#9989; {{ session('success') }}
        </div>
    @endif

    <section class="card panel" style="margin-bottom:22px;">
        <h2>Forecast Assumption</h2>
        <form method="POST" action="{{ route('sales-performance-reporting.revenue-forecast.update') }}">
            @csrf
            <div class="slider-row">
                <label for="sliderGrowth">Growth rate (MoM)</label>
                <input type="range" name="growth_rate_pct" id="sliderGrowth" min="0" max="20" value="{{ $assumptions->growth_rate_pct }}" step="0.5" oninput="recalcForecast()">
                <span class="slider-value" id="sliderGrowthVal">{{ $assumptions->growth_rate_pct }}%</span>
            </div>
            <div class="slider-row">
                <label for="sliderDeal">Deal close rate</label>
                <input type="range" name="deal_close_rate_pct" id="sliderDeal" min="0" max="100" value="{{ $assumptions->deal_close_rate_pct }}" step="1" oninput="recalcForecast()">
                <span class="slider-value" id="sliderDealVal">{{ $assumptions->deal_close_rate_pct }}%</span>
            </div>
            <div class="slider-row">
                <label for="sliderSeason">Seasonality factor</label>
                <input type="range" name="seasonality_factor_pct" id="sliderSeason" min="0" max="100" value="{{ $assumptions->seasonality_factor_pct }}" step="1" oninput="recalcForecast()">
                <span class="slider-value" id="sliderSeasonVal">{{ $assumptions->seasonality_factor_pct }}%</span>
            </div>
            <div style="display:flex; justify-content:flex-end; margin-top:10px;">
                <button type="submit" class="btn btn-primary">Save Assumptions to Database</button>
            </div>
        </form>
    </section>

    <section class="card panel">
        <h2>Monthly revenue trend and projection</h2>
        <div class="chart-wrap" style="height:340px;">
            <canvas id="forecastChart"></canvas>
        </div>
        <div class="legend-row">
            <span><span class="legend-dot" style="background:var(--accent-green)"></span>Actual</span>
            <span><span class="legend-dot" style="background:var(--line-forecast)"></span>Forecast</span>
            <span><span class="legend-dot" style="background:#b7aef0"></span>Today</span>
        </div>
    </section>

@endsection

@section('extra-scripts')
<script>
    const months   = @json($months);
    const actual   = @json($actual); // null = month hasn't closed yet (Chart.js will show a gap, which is correct)
    const todayIdx = {{ $todayIdx }};

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

    function buildForecastSeries(growthPct, dealPct, seasonPct){
        const g = growthPct / 100;
        const dealFactor   = 0.7 + (dealPct / 100) * 0.6;   // 0.70 .. 1.30
        const seasonFactor = 0.85 + (seasonPct / 100) * 0.3; // 0.85 .. 1.15

        const series = actual.slice(0, todayIdx + 1); // history mirrors actual up to today
        let last = actual[todayIdx];
        for(let i = todayIdx + 1; i < months.length; i++){
            last = last * (1 + g);
            series.push(Math.round(last));
        }
        // Scale only the projected tail by deal-close / seasonality assumptions
        return series.map((v, i) => i > todayIdx ? Math.round(v * dealFactor * seasonFactor) : v);
    }

    let forecastChart;

    function recalcForecast(){
        const g = parseFloat(document.getElementById('sliderGrowth').value);
        const d = parseFloat(document.getElementById('sliderDeal').value);
        const s = parseFloat(document.getElementById('sliderSeason').value);

        document.getElementById('sliderGrowthVal').textContent = g + '%';
        document.getElementById('sliderDealVal').textContent = d + '%';
        document.getElementById('sliderSeasonVal').textContent = s + '%';

        const forecast = buildForecastSeries(g, d, s);
        const eoq = forecast[forecast.length - 1]; // in $K
        const q1Actual = actual[0] + actual[1] + actual[2];
        const pctVsQ1 = (((eoq * 3) - q1Actual) / q1Actual * 100).toFixed(1);

        document.getElementById('kpiEoq').textContent = '$' + (eoq / 1000).toFixed(2) + 'M';
        document.getElementById('kpiEoqSub').textContent = (pctVsQ1 >= 0 ? '+' : '') + pctVsQ1 + '% vs actual Q1';
        document.getElementById('kpiConfidence').textContent =
            '$' + ((eoq * 0.93) / 1000).toFixed(2) + 'M – $' + ((eoq * 1.07) / 1000).toFixed(2) + 'M';

        if(forecastChart){
            forecastChart.data.datasets[1].data = forecast;
            forecastChart.update();
        }
    }

    forecastChart = new Chart(document.getElementById('forecastChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                { label:'Actual', data: actual, borderColor:'#8bc34a', backgroundColor:'rgba(139,195,74,0.15)', fill:true, tension:.35, pointRadius:0, borderWidth:3 },
                { label:'Forecast', data: buildForecastSeries({{ $assumptions->growth_rate_pct }}, {{ $assumptions->deal_close_rate_pct }}, {{ $assumptions->seasonality_factor_pct }}), borderColor:'#3a3a4a', backgroundColor:'rgba(58,58,74,0.08)', fill:true, tension:.35, pointRadius:0, borderWidth:2 }
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{
                y:{ beginAtZero:true, max:30000, ticks:{ stepSize:10000, color:'#7a7398', font:{ size:11 } }, grid:{ color:'#e7e4fb' } },
                x:{ ticks:{ color:'#7a7398', font:{ size:11 } }, grid:{ display:false } }
            }
        },
        plugins: [todayMarkerPlugin]
    });

    recalcForecast();
</script>
@endsection

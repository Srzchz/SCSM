{{--
    resources/views/pages/dashboard.blade.php
    Route: GET /dashboard -> App\Http\Controllers\DashboardController@index
--}}
@extends('sales-performance-reporting.layouts.app')

@section('title', 'Dashboard')

@section('content')

    {{-- STAT CARDS --}}
    <section class="stat-grid">
        <div class="card stat-card">
            <div class="stat-label">$ Total Revenue</div>
            <div class="stat-value">${{ number_format($stats['totalRevenue'] / 1000000, 2) }}M</div>
            <div class="stat-sub neutral">{{ $stats['attainmentPct'] }}% of goal</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">&#9678; Target Attainment</div>
            <div class="stat-value">{{ $stats['attainmentPct'] }}%</div>
            <div class="stat-sub neutral">${{ number_format($stats['targetActual'] / 1000000, 2) }}M of ${{ number_format($stats['targetGoal'] / 1000000, 2) }}M goal</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">&#128203; Closed Deals</div>
            <div class="stat-value">{{ $stats['closedDeals'] }}</div>
            <div class="stat-sub neutral">this quarter · sample data</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">&#8600; EOQ Forecast</div>
            <div class="stat-value">${{ number_format($stats['eoqForecast'] / 1000, 1) }}K</div>
            <div class="stat-sub neutral">based on latest forecast</div>
        </div>
    </section>

    {{-- CHART + INSIGHTS --}}
    <section class="content-grid">
        <div class="card panel">
            <h2>Revenue vs Forecast</h2>
            <div class="chart-wrap">
                <canvas id="revenueChart"></canvas>
            </div>
            <div class="legend-row">
                <span><span class="legend-dot" style="background:var(--accent-green)"></span>Actual</span>
                <span><span class="legend-dot" style="background:var(--line-forecast)"></span>Forecast</span>
            </div>
        </div>

        <div class="card panel">
            <h2>Decision Insight</h2>
            <div class="insight-list">
                @forelse ($insights as $alert)
                    @php
                        $kind = match($alert->category) {
                            'critical' => 'risk',
                            'positive' => 'opportunity',
                            default => 'watch',
                        };
                        $icon = match($alert->category) {
                            'critical' => '&#9888;&#65039;',
                            'positive' => '&#8593;',
                            default => '&#9201;&#65039;',
                        };
                    @endphp
                    <div class="insight {{ $kind }}">
                        <span class="icon">{!! $icon !!}</span>
                        {{ $alert->title }} — {{ \Illuminate\Support\Str::limit($alert->description, 80) }}
                    </div>
                @empty
                    <p style="color:var(--muted); font-size:.88rem;">No alerts yet — insights will appear here once alerts are logged.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- DIMENSION TOGGLE + SALES TABLE --}}
    <section class="card table-panel">
        <div class="table-panel-head" style="justify-content:center; gap:10px; margin-bottom:6px;">
            <div class="select" id="dashDimensionSelect" style="width:220px;">
                <button type="button" class="select-btn" onclick="toggleSelect(this)">
                    <span class="select-value">By Representative</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div class="select-menu">
                    <div class="select-option selected" data-value="rep">By Representative</div>
                    <div class="select-option" data-value="region">By Region</div>
                    <div class="select-option" data-value="product">By Product</div>
                </div>
            </div>
        </div>
        <h2 id="dashTableTitle">{{ $dashData['rep']['title'] }}</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th id="dashCol1">{{ $dashData['rep']['col1'] }}</th>
                    <th>Region</th>
                    <th>Actual</th>
                    <th>Target</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="dashTableBody"></tbody>
        </table>
    </section>

@endsection

@section('extra-scripts')
<script>
    // ---------- Revenue vs Forecast chart (data comes straight from the DB) ----------
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [
                { label:'Actual', data: @json($chartActual), borderColor:'#8bc34a', backgroundColor:'rgba(139,195,74,0.15)', fill:true, tension:.35, pointRadius:0, borderWidth:3 },
                { label:'Forecast', data: @json($chartForecast), borderColor:'#3a3a4a', backgroundColor:'rgba(58,58,74,0.08)', fill:true, tension:.35, pointRadius:0, borderWidth:2 }
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{
                y:{ beginAtZero:true, max:30000, ticks:{ stepSize:10000, color:'#7a7398', font:{ size:11 } }, grid:{ color:'#e7e4fb' } },
                x:{ ticks:{ color:'#7a7398', font:{ size:11 } }, grid:{ display:false } }
            }
        }
    });

    // ---------- Dimension-aware sales table (rendered server-side, passed as JSON) ----------
    const dashData = @json($dashData);

    function renderDashTable(dim){
        const d = dashData[dim];
        document.getElementById('dashTableTitle').textContent = d.title;
        document.getElementById('dashCol1').textContent = d.col1;
        const body = document.getElementById('dashTableBody');
        body.innerHTML = d.rows.map(r => `
            <tr>
                <td>${r.name}</td>
                <td>${r.region}</td>
                <td>${r.actual}</td>
                <td>${r.target}</td>
                <td>
                    <div class="progress-track">
                        <div class="progress-fill ${r.status}" style="width:${r.pct}%"></div>
                    </div>
                </td>
                <td><span class="status-pill ${r.status}">${r.label}</span></td>
            </tr>
        `).join('');
    }

    initSelect(document.getElementById('dashDimensionSelect'), function(value){ renderDashTable(value); });
    renderDashTable('rep');
</script>
@endsection

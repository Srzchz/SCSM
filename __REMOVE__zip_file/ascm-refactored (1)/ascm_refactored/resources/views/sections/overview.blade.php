{{--
    Section: Overview
    id="overview" on the parent <section> (set in spa.blade.php) is what
    app.js looks for when the "Overview" sidebar item is clicked.

    Dashboard layout adapted from the Curema design: stat cards, a growth
    chart, a segment breakdown, a top-customers table, and a right-hand
    column of smaller insight cards. Rebuilt with this project's existing
    CSS custom properties and plain-CSS component classes instead of
    Tailwind/Alpine, to match cases.blade.php / warranty.blade.php.
--}}

@php
    $ovStats = [
        ['label' => 'Total Customers', 'value' => '4,856', 'change' => '+12.4%', 'tint' => 'tint-purple'],
        ['label' => 'Repeat Customers', 'value' => '1,674', 'change' => '+10.7%', 'tint' => 'tint-green'],
        ['label' => 'CLV (Avg)', 'value' => '$236.78', 'change' => '+9.3%', 'tint' => 'tint-blue'],
        ['label' => 'Retention Rate', 'value' => '41.2%', 'change' => '+6.8%', 'tint' => 'tint-coral'],
    ];

    $ovSegments = [
        ['label' => 'VIP', 'value' => 864, 'pct' => '17.7%', 'color' => '#AD9EFF'],
        ['label' => 'Repeat Buyer', 'value' => 1302, 'pct' => '26.7%', 'color' => '#9CFF9F'],
        ['label' => 'New Customers', 'value' => 1172, 'pct' => '24%', 'color' => '#7ED8FF'],
        ['label' => 'At Risk', 'value' => 533, 'pct' => '11.5%', 'color' => '#FF9A91'],
        ['label' => 'Inactive', 'value' => 985, 'pct' => '20.2%', 'color' => '#B0B4EC'],
    ];

    $ovCustomers = [
        ['name' => 'Amara Reyes', 'segment' => 'VIP', 'orders' => 28, 'ltv' => '$4,120.00', 'last' => '2026-07-08', 'status' => 'Active'],
        ['name' => 'Northwind Co.', 'segment' => 'Repeat Buyer', 'orders' => 19, 'ltv' => '$2,860.50', 'last' => '2026-07-06', 'status' => 'Active'],
        ['name' => 'Contoso Ltd', 'segment' => 'VIP', 'orders' => 24, 'ltv' => '$3,975.00', 'last' => '2026-07-05', 'status' => 'Active'],
        ['name' => 'Jonas Villareal', 'segment' => 'New Customer', 'orders' => 2, 'ltv' => '$210.00', 'last' => '2026-07-04', 'status' => 'Active'],
        ['name' => 'Example Co', 'segment' => 'At Risk', 'orders' => 11, 'ltv' => '$1,340.00', 'last' => '2026-05-18', 'status' => 'At Risk'],
    ];

    $ovFollowups = [
        ['name' => 'Amara Reyes', 'note' => 'Renewal call re: annual plan', 'when' => 'Today, 3:00 PM'],
        ['name' => 'Northwind Co.', 'note' => 'Send Q3 order recap', 'when' => 'Tomorrow, 10:00 AM'],
        ['name' => 'Jonas Villareal', 'note' => 'Welcome check-in', 'when' => 'Jul 13, 9:30 AM'],
    ];

    $ovActivities = [
        ['title' => 'Order placed', 'meta' => 'Contoso Ltd • 2h ago', 'text' => 'ORD-9931 for 3 items, $412.00 total.'],
        ['title' => 'Case resolved', 'meta' => 'Support • Yesterday', 'text' => 'CS-2177 marked resolved by the warranty team.'],
        ['title' => 'Segment changed', 'meta' => 'Example Co • 2d ago', 'text' => 'Moved from Repeat Buyer to At Risk after 60 days inactive.'],
    ];
@endphp

<div class="overview-wrapper">
    <div class="overview-grid">
        <div class="overview-main">

            <div class="stat-cards">
                @foreach ($ovStats as $stat)
                    <div class="stat-card">
                        <div class="stat-card-icon {{ $stat['tint'] }}">
                            @switch($loop->index)
                                @case(0)
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-1a4 4 0 00-4-4h-1M9 20H4v-1a4 4 0 014-4h1m0-7a4 4 0 118 0 4 4 0 01-8 0zm10 3a3 3 0 10-3-3"/></svg>
                                    @break
                                @case(1)
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M4.5 9A8 8 0 0119.5 9M19.5 15a8 8 0 01-15 0"/></svg>
                                    @break
                                @case(2)
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                                    @break
                                @default
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.59 13.41L11 3.83A2 2 0 009.59 3.24L4 3a1 1 0 00-1 1l.24 5.59a2 2 0 00.58 1.41l9.59 9.59a2 2 0 002.83 0l4.35-4.35a2 2 0 000-2.83zM7 7h.01"/></svg>
                            @endswitch
                        </div>
                        <div>
                            <p class="stat-card-label">{{ $stat['label'] }}</p>
                            <p class="stat-card-value">{{ $stat['value'] }}</p>
                            <p class="stat-card-change">&uarr; {{ $stat['change'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="chart-row">
                <div class="module-card chart-card">
                    <div class="card-header chart-card-header">
                        <h2 class="card-title">Customer Growth</h2>
                        <select class="input input-compact" aria-label="Growth chart range">
                            <option>Last 30 days</option>
                            <option>Last 90 days</option>
                            <option>This year</option>
                        </select>
                    </div>
                    <div class="chart-canvas-wrap">
                        <canvas id="ov-growth-chart"
                                data-labels="{{ json_encode(['May 10', 'Jun 9', 'Jul 9', 'Aug 8']) }}"
                                data-values="{{ json_encode([20, 60, 130, 220]) }}"></canvas>
                    </div>
                </div>

                <div class="module-card chart-card">
                    <div class="card-header chart-card-header">
                        <h2 class="card-title">Customers by Segment</h2>
                        <span class="card-subtitle-link">View all segments</span>
                    </div>
                    <div class="segment-body">
                        <div class="segment-donut-wrap">
                            <canvas id="ov-segment-chart"
                                    data-labels="{{ json_encode(array_column($ovSegments, 'label')) }}"
                                    data-values="{{ json_encode(array_column($ovSegments, 'value')) }}"
                                    data-colors="{{ json_encode(array_column($ovSegments, 'color')) }}"></canvas>
                            <div class="segment-donut-center">
                                <span class="segment-donut-total">4,856</span>
                                <span class="segment-donut-label">Total Customers</span>
                            </div>
                        </div>
                        <ul class="segment-legend">
                            @foreach ($ovSegments as $seg)
                                <li>
                                    <span class="segment-legend-key">
                                        <span class="segment-swatch" style="background:{{ $seg['color'] }}"></span>
                                        {{ $seg['label'] }}
                                    </span>
                                    <span class="segment-legend-val">{{ $seg['value'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <section class="module-card" aria-label="Top customers">
                <div class="card-header top-customers-header">
                    <div>
                        <h2 class="card-title">Top Customers</h2>
                        <span class="card-subtitle">Ranked by lifetime value.</span>
                    </div>
                    <a href="#" class="link-btn" data-target="customer-relation">View all</a>
                </div>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Segment</th>
                                <th>Orders</th>
                                <th>Lifetime Value</th>
                                <th>Last Purchase</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ovCustomers as $c)
                                <tr>
                                    <td>{{ $c['name'] }}</td>
                                    <td>{{ $c['segment'] }}</td>
                                    <td>{{ $c['orders'] }}</td>
                                    <td>{{ $c['ltv'] }}</td>
                                    <td>{{ $c['last'] }}</td>
                                    <td>
                                        @if ($c['status'] === 'Active')
                                            <span class="pill pill-green">Active</span>
                                        @else
                                            <span class="pill pill-yellow">At Risk</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

        </div>

        <div class="overview-side">
            <div class="module-card side-card">
                <h2 class="card-title">Customer Insight</h2>
                <p class="side-card-hint">Repeat Buyer is your fastest-growing segment this month.</p>
                <div class="insight-bar-row">
                    <span class="insight-bar-label">Repeat Buyer</span>
                    <div class="insight-bar-track"><div class="insight-bar-fill" style="width:78%;background:#9CFF9F"></div></div>
                    <span class="insight-bar-pct">+18%</span>
                </div>
                <div class="insight-bar-row">
                    <span class="insight-bar-label">VIP</span>
                    <div class="insight-bar-track"><div class="insight-bar-fill" style="width:54%;background:#AD9EFF"></div></div>
                    <span class="insight-bar-pct">+9%</span>
                </div>
                <div class="insight-bar-row">
                    <span class="insight-bar-label">At Risk</span>
                    <div class="insight-bar-track"><div class="insight-bar-fill" style="width:22%;background:#FF9A91"></div></div>
                    <span class="insight-bar-pct">-4%</span>
                </div>
            </div>

            <div class="module-card side-card">
                <h2 class="card-title">Upcoming Follow-ups</h2>
                <ul class="followup-list">
                    @foreach ($ovFollowups as $f)
                        <li>
                            <span class="followup-name">{{ $f['name'] }}</span>
                            <span class="followup-note">{{ $f['note'] }}</span>
                            <span class="followup-when">{{ $f['when'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="module-card side-card">
                <h2 class="card-title">Recent Activities</h2>
                <div class="timeline timeline-compact">
                    @foreach ($ovActivities as $a)
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-body">
                                <div class="timeline-title">{{ $a['title'] }}</div>
                                <div class="timeline-meta">{{ $a['meta'] }}</div>
                                <div class="timeline-text">{{ $a['text'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .overview-wrapper{padding-top:0;}

    .overview-grid{display:flex;align-items:flex-start;gap:16px;min-width:0;}
    .overview-main{flex:1;min-width:0;display:flex;flex-direction:column;gap:16px;}
    .overview-side{width:260px;flex-shrink:0;display:flex;flex-direction:column;gap:16px;}
    @media (max-width: 980px){
        .overview-grid{flex-direction:column;}
        .overview-side{width:100%;}
    }

    .module-card{background:#ffffff;border:1px solid rgba(18,15,52,0.08);border-radius:18px;padding:18px;box-shadow:0 10px 30px rgba(18,15,52,0.04);min-width:0;overflow:hidden;}
    .card-header{margin-bottom:12px;}
    .card-title{margin:0;font-size:1rem;font-weight:800;}
    .card-subtitle{display:block;margin-top:4px;color:var(--color-text-muted);font-size:0.85rem;}
    .card-subtitle-link{font-size:0.8rem;color:var(--color-primary);font-weight:600;cursor:default;}

    .stat-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;}
    @media (max-width: 980px){.stat-cards{grid-template-columns:repeat(2,minmax(0,1fr));}}
    @media (max-width: 560px){.stat-cards{grid-template-columns:1fr;}}

    .stat-card{background:#fff;border:1px solid rgba(18,15,52,0.08);border-radius:16px;padding:16px;display:flex;align-items:flex-start;gap:12px;box-shadow:0 10px 30px rgba(18,15,52,0.04);}
    .stat-card-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .stat-card-icon svg{width:22px;height:22px;color:var(--color-text);}
    .tint-purple{background:rgba(176,180,236,0.45);}
    .tint-green{background:rgba(156,255,159,0.4);}
    .tint-blue{background:rgba(126,216,255,0.4);}
    .tint-coral{background:rgba(255,154,145,0.4);}
    .stat-card-label{margin:0;font-size:0.75rem;color:var(--color-text-muted);}
    .stat-card-value{margin:2px 0 0;font-size:1.25rem;font-weight:800;line-height:1.2;}
    .stat-card-change{margin:2px 0 0;font-size:0.75rem;font-weight:600;color:var(--color-indicator-text-green);}

    .chart-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    @media (max-width: 900px){.chart-row{grid-template-columns:1fr;}}
    .chart-card{height:307px;display:flex;flex-direction:column;}
    .chart-card-header{display:flex;align-items:center;justify-content:space-between;}
    .chart-canvas-wrap{flex:1;position:relative;min-height:0;}
    .input-compact{padding:6px 10px;font-size:0.75rem;width:auto;}

    .segment-body{flex:1;display:flex;align-items:center;gap:16px;min-height:0;}
    .segment-donut-wrap{position:relative;width:150px;height:150px;flex-shrink:0;}
    .segment-donut-wrap canvas{width:100% !important;height:100% !important;}
    .segment-donut-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;}
    .segment-donut-total{font-size:1.1rem;font-weight:800;}
    .segment-donut-label{font-size:0.7rem;color:var(--color-text-muted);}
    .segment-legend{list-style:none;margin:0;padding:0;flex:1;font-size:0.8rem;display:flex;flex-direction:column;gap:8px;}
    .segment-legend li{display:flex;align-items:center;justify-content:space-between;}
    .segment-legend-key{display:flex;align-items:center;gap:8px;}
    .segment-swatch{width:10px;height:10px;border-radius:999px;flex-shrink:0;}
    .segment-legend-val{color:var(--color-text-muted);}

    .top-customers-header{display:flex;align-items:center;justify-content:space-between;}

    .table-wrap{background:#fff;border:1px solid rgba(18,15,52,0.06);border-radius:14px;overflow-x:auto;box-shadow:inset 0 1px 0 rgba(255,255,255,0.8);max-width:100%;}
    .data-table{width:100%;border-collapse:collapse;min-width:0;}
    .data-table th{font-size:0.8rem;color:var(--color-text-muted);text-align:left;padding:12px 12px;border-bottom:1px solid rgba(18,15,52,0.06);background:#f6f7ff;}
    .data-table td{padding:12px 12px;border-bottom:1px solid rgba(18,15,52,0.05);vertical-align:top;font-size:0.9rem;}
    .link-btn{background:transparent;border:none;color:#2b3cff;font-weight:600;cursor:pointer;padding:0;text-decoration:none;font-size:0.85rem;}
    .link-btn:hover{text-decoration:underline;}

    .pill{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-weight:800;font-size:0.8rem;border:1px solid transparent;}
    .pill-green{background:rgba(156,255,159,0.18);color:var(--color-indicator-text-green);border-color:rgba(0,99,15,0.2);}
    .pill-yellow{background:rgba(249,223,170,0.25);color:var(--color-indicator-text-yellow);border-color:rgba(204,155,40,0.18);}

    .side-card{display:flex;flex-direction:column;gap:12px;}
    .side-card-hint{margin:-6px 0 4px;font-size:0.8rem;color:var(--color-text-muted);line-height:1.4;}

    .insight-bar-row{display:flex;align-items:center;gap:8px;font-size:0.75rem;}
    .insight-bar-label{width:76px;flex-shrink:0;color:var(--color-text-muted);}
    .insight-bar-track{flex:1;height:6px;border-radius:999px;background:var(--color-bg);overflow:hidden;}
    .insight-bar-fill{height:100%;border-radius:999px;}
    .insight-bar-pct{width:34px;text-align:right;flex-shrink:0;font-weight:700;}

    .followup-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:12px;}
    .followup-list li{display:flex;flex-direction:column;gap:2px;padding-bottom:12px;border-bottom:1px solid rgba(18,15,52,0.06);}
    .followup-list li:last-child{border-bottom:none;padding-bottom:0;}
    .followup-name{font-weight:700;font-size:0.85rem;}
    .followup-note{font-size:0.8rem;color:rgba(18,15,52,0.75);}
    .followup-when{font-size:0.75rem;color:var(--color-text-muted);}

    .timeline{display:flex;flex-direction:column;gap:14px;}
    .timeline-compact .timeline-item{gap:10px;}
    .timeline-item{display:flex;gap:12px;align-items:flex-start;}
    .timeline-dot{width:10px;height:10px;border-radius:50%;background:var(--color-primary);margin-top:5px;flex-shrink:0;}
    .timeline-title{font-weight:800;font-size:0.85rem;}
    .timeline-meta{color:var(--color-text-muted);font-size:0.75rem;margin-top:2px;}
    .timeline-text{margin-top:6px;color:rgba(18,15,52,0.85);line-height:1.4;font-size:0.8rem;}
</style>

<script>
    (function () {
        let ovGrowthChart = null;
        let ovSegmentChart = null;

        const waitForChartJs = (timeoutMs = 3000) => new Promise((resolve, reject) => {
            const start = Date.now();
            const check = () => {
                if (window.Chart) return resolve(window.Chart);
                if (Date.now() - start > timeoutMs) return reject(new Error('Chart.js failed to load'));
                setTimeout(check, 50);
            };
            check();
        });

        const buildCharts = (Chart) => {
            const growthEl = document.getElementById('ov-growth-chart');
            const segEl = document.getElementById('ov-segment-chart');
            if (!growthEl || !segEl) return;

            const growthLabels = JSON.parse(growthEl.dataset.labels || '[]');
            const growthValues = JSON.parse(growthEl.dataset.values || '[]');
            const segLabels = JSON.parse(segEl.dataset.labels || '[]');
            const segValues = JSON.parse(segEl.dataset.values || '[]');
            const segColors = JSON.parse(segEl.dataset.colors || '[]');

            if (ovGrowthChart) ovGrowthChart.destroy();
            if (ovSegmentChart) ovSegmentChart.destroy();

            ovGrowthChart = new Chart(growthEl, {
                type: 'line',
                data: {
                    labels: growthLabels,
                    datasets: [{
                        label: 'New Customers',
                        data: growthValues,
                        borderColor: '#120F34',
                        backgroundColor: 'rgba(18,15,52,0.08)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        borderWidth: 3,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: '#EFEDF9' }, ticks: { stepSize: 100 } },
                        x: { grid: { display: false } }
                    }
                }
            });

            ovSegmentChart = new Chart(segEl, {
                type: 'doughnut',
                data: {
                    labels: segLabels,
                    datasets: [{ data: segValues, backgroundColor: segColors, borderWidth: 0 }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    cutout: '70%',
                    plugins: { legend: { display: false } }
                }
            });
        };

        const resizeCharts = () => {
            if (ovGrowthChart) ovGrowthChart.resize();
            if (ovSegmentChart) ovSegmentChart.resize();
        };

        const init = async () => {
            try {
                const Chart = await waitForChartJs();
                buildCharts(Chart);

                let resizeTimeout;
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(resizeCharts, 150);
                });

                // This SPA pre-renders every section and toggles [hidden] on the
                // parent <section>. Chart.js can't size a canvas that was hidden
                // when it was created, so re-run resize whenever Overview becomes
                // visible again (covers app.js implementations we don't control).
                const overviewSection = document.getElementById('overview');
                if (overviewSection && 'MutationObserver' in window) {
                    const observer = new MutationObserver(() => {
                        if (!overviewSection.hasAttribute('hidden')) {
                            setTimeout(resizeCharts, 50);
                        }
                    });
                    observer.observe(overviewSection, { attributes: true, attributeFilter: ['hidden'] });
                }
            } catch (e) {
                console.error(e);
            }
        };

        init();
    })();
</script>

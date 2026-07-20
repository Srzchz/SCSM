@extends('crm.layouts.app')

@section('title', 'Dashboard')

@php
    $active = 'Dashboard';

    $stats = [
        ['label' => 'Total Customers', 'value' => '4,856', 'change' => '+12.4%', 'icon' => '👥', 'bg' => 'bg-curema-purplesoft'],
        ['label' => 'Repeat Customers', 'value' => '1,674', 'change' => '+10.7%', 'icon' => '🛒', 'bg' => 'bg-curema-greensoft'],
        ['label' => 'CLV (Avg)', 'value' => '$236.78', 'change' => '+9.3%', 'icon' => '💰', 'bg' => 'bg-curema-bluesoft'],
        ['label' => 'Retention Rate', 'value' => '41.2%', 'change' => '+6.8%', 'icon' => '🏷️', 'bg' => 'bg-curema-orangesoft'],
    ];

    $segments = [
        ['label' => 'VIP', 'value' => 864, 'pct' => '17.7%', 'color' => '#AD9EFF'],
        ['label' => 'Repeat Buyer', 'value' => 1302, 'pct' => '26.7%', 'color' => '#9CFF9F'],
        ['label' => 'New Customers', 'value' => 1172, 'pct' => '24%', 'color' => '#7ED8FF'],
        ['label' => 'At Risk', 'value' => 533, 'pct' => '11.5%', 'color' => '#FF9A91'],
        ['label' => 'Inactive', 'value' => 985, 'pct' => '20.2%', 'color' => '#B0B4EC'],
    ];

   
@endphp

@section('content')

    @include('crm.partials.topbar')

    <div class="flex items-start gap-4 min-w-0">

        <div class="flex-1 min-w-0 flex flex-col gap-4">

            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach ($stats as $stat)
                    <div class="bg-curema-card rounded-2xl border border-curema-border p-4 flex items-start gap-3">
                        <div class="w-11 h-11 rounded-xl {{ $stat['bg'] }} flex items-center justify-center text-lg shrink-0">
                            {{ $stat['icon'] }}
                        </div>
                        <div>
                            <p class="text-xs text-curema-sub">{{ $stat['label'] }}</p>
                            <p class="text-xl font-bold leading-tight">{{ $stat['value'] }}</p>
                            <p class="text-xs text-curema-green font-medium">↑ {{ $stat['change'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="h-[307px] bg-curema-card rounded-2xl border border-curema-border p-5 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold">Customer Growth</h2>
                        <select class="text-xs border border-curema-border rounded-lg px-2 py-1 text-curema-sub">
                            <option>Last 30 days</option>
                            <option>Last 90 days</option>
                            <option>This year</option>
                        </select>
                    </div>
                    <div class="flex-1 relative">
                        <canvas id="growthChart"
                                data-labels="{{ json_encode(['May 10','Jun 9','Jul 9','Aug 8']) }}"
                                data-values="{{ json_encode([20, 60, 130, 220]) }}"></canvas>
                    </div>
                </div>

                <div class="h-[307px] bg-curema-card rounded-2xl border border-curema-border p-5 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold">Customer By Segment</h2>
                        <a href="#" class="text-xs text-curema-purple font-medium">View all segments</a>
                    </div>
                    <div class="flex-1 flex items-center gap-4 min-h-0">
                        <div class="relative shrink-0" style="width: 192px; height: 192px; flex-shrink: 0;">
                            <canvas id="segmentChart"
                                    data-labels="{{ json_encode(array_column($segments, 'label')) }}"
                                    data-values="{{ json_encode(array_column($segments, 'value')) }}"
                                    data-colors="{{ json_encode(array_column($segments, 'color')) }}" style="display: block; width: 100% !important; height: 100% !important;"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-lg font-extrabold">4,856</span>
                                <span class="text-xs text-curema-sub">Total Customers</span>
                            </div>
                        </div>
                        <ul class="space-y-1.5 text-xs flex-1">
                            @foreach ($segments as $seg)
                                <li class="flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $seg['color'] }}"></span>
                                        {{ $seg['label'] }}
                                    </span>
                                    <span class="text-curema-sub">{{ $seg['value'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            @include('crm.partials.customer-table', [
                'tableTitle' => 'Top Customers',
                'tableCustomers' => $tableCustomers,
                'showViewAllLink' => true,
            ])
        </div>

        <div class="w-[220px] shrink-0 flex flex-col gap-4">
            @include('crm.partials.customer-insight')
            @include('crm.partials.upcoming-followups')
            @include('crm.partials.recent-activities')
        </div>
    </div>

@endsection

@push('scripts')
<script>
    (function () {
        let growthChart = null;
        let segChart = null;

        const waitForChartJs = (timeoutMs = 3000) => {
            return new Promise((resolve, reject) => {
                const start = Date.now();

                const check = () => {
                    if (window.Chart) return resolve(window.Chart);
                    if (Date.now() - start > timeoutMs) {
                        return reject(new Error('Chart.js failed to load (window.Chart is undefined)'));
                    }
                    setTimeout(check, 50);
                };

                check();
            });
        };

        const buildCharts = (Chart) => {
            const growthEl = document.getElementById('growthChart');
            const segEl = document.getElementById('segmentChart');

            if (!growthEl || !segEl) return;

            const growthLabels = JSON.parse(growthEl.dataset.labels || '[]');
            const growthValues = JSON.parse(growthEl.dataset.values || '[]');

            const segLabels = JSON.parse(segEl.dataset.labels || '[]');
            const segValues = JSON.parse(segEl.dataset.values || '[]');
            const segColors = JSON.parse(segEl.dataset.colors || '[]');

            // (Re)create charts
            if (growthChart) growthChart.destroy();
            if (segChart) segChart.destroy();

            growthChart = new Chart(growthEl, {
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
                    plugins: { legend: { display: false } },
                    responsive: true,
                    scales: {
                        y: { grid: { color: '#EFEDF9' }, ticks: { stepSize: 100 } },
                        x: { grid: { display: false } }
                    }
                }
            });

            segChart = new Chart(segEl, {
                type: 'doughnut',
                data: {
                    labels: segLabels,
                    datasets: [{
                        data: segValues,
                        backgroundColor: segColors,
                        borderWidth: 0,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    cutout: '70%',
                    plugins: { legend: { display: false } }
                }
            });
        };

        const init = async () => {
            try {
                const Chart = await waitForChartJs();
                buildCharts(Chart);

                // Resize without destroying/recreating (fixes missing charts due to timing)
                let resizeTimeout;

                const resizeAllCharts = () => {
                    // Ensure correct canvas backing store size after zoom changes
                    const canvases = [document.getElementById('growthChart'), document.getElementById('segmentChart')];
                    canvases.forEach((c) => {
                        if (!c) return;
                        // Sync actual canvas dimensions to its CSS size
                        c.width = Math.max(1, Math.round(c.offsetWidth));
                        c.height = Math.max(1, Math.round(c.offsetHeight));
                    });

                    if (growthChart) growthChart.resize();
                    if (segChart) segChart.resize();
                };

                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(() => {
                        // Run twice: some browsers update layout in two steps during zoom
                        resizeAllCharts();
                        setTimeout(resizeAllCharts, 50);
                    }, 150);
                });
            } catch (e) {
                console.error(e);
            }
        };

        init();
    })();
</script>
@endpush

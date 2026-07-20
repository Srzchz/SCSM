@extends('crm.layouts.app')

@section('title', 'Purchase Behavior')

@php $active = 'Purchase Behavior'; @endphp

@section('content')

    @include('crm.partials.topbar')

    <div class="flex items-start gap-4">

        <div class="flex-1 flex flex-col gap-4 min-w-0">

            <div class="grid grid-cols-1 md:grid-cols-[0.85fr_1.4fr] gap-4">
                <div class="bg-curema-card rounded-2xl border border-curema-border p-5 h-[400px] flex flex-col">
                    <h2 class="font-semibold mb-4">Purchase Behavior</h2>
                    <ul class="space-y-5">
                        @php
                            $metricIcons = ['👕', '🛍️', '⏱️', '↩️'];
                        @endphp
                        @foreach ($metrics as $i => $m)
                            <li class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-full bg-curema-purplesoft flex items-center justify-center text-base shrink-0">
                                    {{ $metricIcons[$i] ?? '📊' }}
                                </div>
                                <div>
                                    <p class="text-xs text-curema-sub">{{ $m['label'] }}</p>
                                    <p class="font-bold">{{ $m['value'] }}</p>
                                    @if (!empty($m['sub']))
                                        <p class="text-[11px] text-curema-sub">{{ $m['sub'] }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-curema-card rounded-2xl border border-curema-border p-5 h-[400px] flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold">Purchase Frequency</h2>
                        <div class="flex items-center gap-3 text-xs text-curema-sub">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-curema-purplesoft"></span> 2024</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-curema-purple"></span> 2025</span>
                        </div>
                    </div>
                    <div class="flex-1 relative">
                        <canvas id="frequencyChart"
                                data-labels="{{ json_encode($months) }}"
                                data-2024="{{ json_encode($series2024) }}"
                                data-2025="{{ json_encode($series2025) }}"></canvas>
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
    const freqEl = document.getElementById('frequencyChart');
    new Chart(freqEl, {
        type: 'bar',
        data: {
            labels: JSON.parse(freqEl.dataset.labels),
            datasets: [
                { label: '2024', data: JSON.parse(freqEl.dataset['2024']), backgroundColor: '#B0B4EC', borderRadius: 4 },
                { label: '2025', data: JSON.parse(freqEl.dataset['2025']), backgroundColor: '#120F34', borderRadius: 4 },
            ]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#EFEDF9' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush
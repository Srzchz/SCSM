@extends('crm.layouts.app')

@section('title', 'Communication Logs')

@php
    $active = 'Communication Logs';
    $statusClass = [
        'New' => 'bg-curema-bluesoft text-curema-blue',
        'Resolved' => 'bg-curema-greensoft text-curema-green',
    ];
@endphp

@section('content')

    @include('crm.partials.topbar')

    <div class="flex items-start gap-4">

        <div class="flex-1 flex flex-col gap-4 min-w-0">

            <div class="grid grid-cols-1 md:grid-cols-[1.6fr_1fr] gap-4">
                <div class="bg-curema-card rounded-2xl border border-curema-border p-5 h-[260px] flex flex-col">
                    <h2 class="font-semibold mb-4">Case Volume — last 7 days</h2>
                    <div class="flex-1 relative">
                        <canvas id="caseVolumeChart"
                                data-labels="{{ json_encode($days) }}"
                                data-open="{{ json_encode($open) }}"
                                data-resolved="{{ json_encode($resolved) }}"></canvas>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-curema-sub mt-3">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-curema-purplesoft"></span> Open</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-curema-purple"></span> Resolved</span>
                    </div>
                </div>

                <div class="bg-curema-card rounded-2xl border border-curema-border p-5 h-[260px] flex flex-col">
                    <h2 class="font-semibold mb-4">Total Cases this Week</h2>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="bg-curema-bluesoft rounded-xl py-4">
                            <p class="text-xl font-extrabold text-curema-blue">{{ $caseStats['open'] }}</p>
                            <p class="text-[11px] text-curema-sub mt-1">Open</p>
                        </div>
                        <div class="bg-curema-coral rounded-xl py-4">
                            <p class="text-xl font-extrabold text-curema-ink">{{ $caseStats['inProgress'] }}</p>
                            <p class="text-[11px] text-curema-sub mt-1">In Process</p>
                        </div>
                        <div class="bg-curema-greensoft rounded-xl py-4">
                            <p class="text-xl font-extrabold text-curema-green">{{ $caseStats['resolved'] }}</p>
                            <p class="text-[11px] text-curema-sub mt-1">Resolved</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-curema-card rounded-2xl border border-curema-border p-5">
                <h2 class="font-semibold mb-4">Communication Logs</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" data-export="true">
                        <thead>
                            <tr class="text-left text-curema-sub text-xs">
                                <th class="font-medium pb-3">Ticket ID</th>
                                <th class="font-medium pb-3">Customer</th>
                                <th class="font-medium pb-3">Issue</th>
                                <th class="font-medium pb-3">Details</th>
                                <th class="font-medium pb-3">Date</th>
                                <th class="font-medium pb-3">Mode</th>
                                <th class="font-medium pb-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr class="border-t border-curema-border customer-row cursor-pointer hover:bg-curema-bg/60 transition"
                                    data-search="{{ strtolower($log['customer'] . ' ' . $log['issue'] . ' ' . $log['ticket_id']) }}"
                                    onclick="window.location='{{ route('customers.communication', $log['customer_id']) }}?ticket={{ $log['ticket_id'] }}'">
                                    <td class="py-2.5 font-medium text-curema-purple">{{ $log['ticket_id'] }}</td>
                                    <td class="flex items-center gap-2">
                                        <span class="w-7 h-7 rounded-full bg-curema-bg flex items-center justify-center text-xs shrink-0">👤</span>
                                        {{ $log['customer'] }}
                                    </td>
                                    <td>{{ $log['issue'] }}</td>
                                    <td class="max-w-xs truncate">{{ $log['details'] }}</td>
                                    <td>{{ $log['date'] }}</td>
                                    <td>{{ $log['mode'] }}</td>
                                    <td>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClass[$log['status']] ?? 'bg-curema-bg' }}">
                                            {{ $log['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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
    const cvEl = document.getElementById('caseVolumeChart');
    new Chart(cvEl, {
        type: 'bar',
        data: {
            labels: JSON.parse(cvEl.dataset.labels),
            datasets: [
                { label: 'Open', data: JSON.parse(cvEl.dataset.open), backgroundColor: '#B0B4EC', borderRadius: 4 },
                { label: 'Resolved', data: JSON.parse(cvEl.dataset.resolved), backgroundColor: '#120F34', borderRadius: 4 },
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
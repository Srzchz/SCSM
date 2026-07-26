@extends('layouts.app')

@section('title', 'Communication Logs')

@php
    $active = 'Communication Logs';
    $statusClass = [
        'Open' => 'bg-curema-bluesoft text-curema-blue',
        'Closed' => 'bg-curema-greensoft text-curema-green',
    ];
@endphp

@section('content')

    @include('partials.topbar')

    <div class="flex flex-col lg:flex-row items-start gap-4">

        <div class="flex-1 flex flex-col gap-4 min-w-0 w-full">

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
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="bg-curema-bluesoft rounded-xl py-4">
                            <p class="text-xl font-extrabold text-curema-blue">{{ $caseStats['open'] }}</p>
                            <p class="text-[11px] text-curema-sub mt-1">Open</p>
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
                                <th class="font-medium pb-3 px-3">Ticket ID</th>
                                <th class="font-medium pb-3 px-3">Customer</th>
                                <th class="font-medium pb-3 px-3">Issue</th>
                                <th class="font-medium pb-3 px-3">Details</th>
                                <th class="font-medium pb-3 px-3">Date</th>
                                <th class="font-medium pb-3 px-3">Mode</th>
                                <th class="font-medium pb-3 px-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr class="border-t border-curema-border customer-row cursor-pointer hover:bg-curema-bg/60 transition"
                                    data-search="{{ strtolower($log['customer'] . ' ' . $log['issue'] . ' ' . $log['ticket_id']) }}"
                                    onclick="window.location='{{ route('customers.communication', $log['customer_id']) }}?ticket={{ $log['ticket_id'] }}'">
                                    <td class="py-2.5 px-3 font-medium text-curema-purple">{{ $log['ticket_id'] }}</td>
                                    <td class="self-center px-3">
                                        {{ $log['customer'] }}
                                    </td>
                                    <td class="px-3">{{ $log['issue'] }}</td>
                                    <td class="max-w-xs truncate px-3">{{ $log['details'] }}</td>
                                    <td class="px-3">{{ $log['date'] }}</td>
                                    <td class="px-3">{{ $log['mode'] }}</td>
                                    <td class="px-3">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClass[$log['status']] ?? 'bg-curema-bg' }}">
                                            {{ $log['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="pagination-bar">
                        <span class="pagination-summary">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}</span>
                        @include('partials.pagination', ['paginator' => $logs])
                    </div>
                @endif
            </div>
        </div>

        <div class="w-full lg:w-[220px] shrink-0 flex flex-col gap-4">
            @include('partials.customer-insight')
            @include('partials.upcoming-followups')
        </div>
    </div>

    <style>
        .pagination-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:14px;padding-top:14px;border-top:1px solid rgba(18,15,52,0.06);}
        .pagination-summary{font-size:0.82rem;color:#5B5876;}
        .pager{display:flex;align-items:center;gap:4px;flex-wrap:wrap;}
        .pager-btn{display:inline-flex;align-items:center;justify-content:center;min-width:30px;height:30px;padding:0 8px;border-radius:8px;font-size:0.82rem;font-weight:700;color:#120F34;text-decoration:none;border:1px solid transparent;}
        .pager-btn:hover{background:#E9EBFC;}
        .pager-btn-active{background:#120F34;color:#fff;}
        .pager-btn-active:hover{background:#120F34;}
        .pager-btn-disabled{color:rgba(18,15,52,0.25);cursor:default;}
        .pager-btn-disabled:hover{background:transparent;}
        .pager-ellipsis{padding:0 4px;color:#5B5876;font-size:0.82rem;}
    </style>

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

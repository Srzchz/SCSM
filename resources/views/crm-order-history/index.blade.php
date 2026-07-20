@extends('crm.layouts.app')

@section('title', 'Orders')

@php
    $active = 'Orders';
    $statusClass = [
        'Delivered' => 'bg-curema-greensoft text-curema-green',
        'Refunded' => 'bg-curema-purplesoft text-curema-ink',
        'Pending' => 'bg-curema-coral text-curema-ink',
    ];
@endphp

@section('content')

    @include('crm.partials.topbar')

    <div class="flex items-start gap-4">

        <div class="flex-1 flex flex-col gap-4 min-w-0">

            <div class="bg-curema-card rounded-2xl border border-curema-border p-5">
                <h2 class="font-semibold mb-4">Order History</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" data-export="true">
                        <thead>
                            <tr class="text-left text-curema-sub text-xs">
                                <th class="font-medium pb-3">Customer</th>
                                <th class="font-medium pb-3">Order ID</th>
                                <th class="font-medium pb-3">Date</th>
                                <th class="font-medium pb-3">Quantity</th>
                                <th class="font-medium pb-3">Price</th>
                                <th class="font-medium pb-3">Total</th>
                                <th class="font-medium pb-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $o)
                                <tr class="border-t border-curema-border customer-row" data-search="{{ strtolower($o['customer']) }}">
                                    <td class="py-2.5 flex items-center gap-2">
                                        <span class="w-7 h-7 rounded-full bg-curema-bg flex items-center justify-center text-xs shrink-0">👤</span>
                                        {{ $o['customer'] }}
                                    </td>
                                    <td>{{ $o['order_id'] }}</td>
                                    <td>{{ $o['date'] }}</td>
                                    <td class="font-medium">{{ $o['total'] }}</td>
                                    <td>{{ $o['payment_status'] }}</td>
                                    <td>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClass[$o['status']] ?? 'bg-curema-bg' }}">
                                            {{ $o['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-curema-card rounded-2xl border border-curema-border p-5">
                <h2 class="font-semibold mb-4">Refund History</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-curema-sub text-xs">
                                <th class="font-medium pb-3">Customer</th>
                                <th class="font-medium pb-3">Order ID</th>
                                <th class="font-medium pb-3">Date</th>
                                <th class="font-medium pb-3">Quantity</th>
                                <th class="font-medium pb-3">Price</th>
                                <th class="font-medium pb-3">Total</th>
                                <th class="font-medium pb-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($refunds as $r)
                                <tr class="border-t border-curema-border">
                                    <td class="py-2.5 flex items-center gap-2">
                                        <span class="w-7 h-7 rounded-full bg-curema-bg flex items-center justify-center text-xs shrink-0">👤</span>
                                        {{ $r['customer'] }}
                                    </td>
                                    <td>{{ $r['order_id'] }}</td>
                                    <td>{{ $r['date'] }}</td>
                                    <td class="font-medium">{{ $r['total'] }}</td>
                                    <td>{{ $r['payment_status'] }}</td>
                                    <td>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClass[$r['status']] ?? 'bg-curema-bg' }}">
                                            {{ $r['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-5">
                    <a href="{{ route('sales-order') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-curema-purplesoft text-curema-ink text-sm font-semibold">
                        Sales Order Management →
                    </a>
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
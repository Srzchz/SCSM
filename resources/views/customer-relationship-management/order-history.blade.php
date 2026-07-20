@extends('crm.layouts.app')

@section('title', $customer['full_name'] . ' - Order History')

@php
    $active = 'Customers';
    $activeTab = 'order-history';

    $statusClass = [
        'Delivered' => 'bg-curema-greensoft text-curema-green',
        'Pending' => 'bg-curema-coral text-curema-ink',
        'Cancelled' => 'bg-curema-coral text-curema-ink',
    ];
@endphp

@section('content')

    @include('crm.partials.topbar')

    <div class="flex items-start gap-4">
        <div class="flex-1 min-w-0 flex flex-col gap-4">
            @include('customer-relationship-management.partials.profile-header', ['customer' => $customer, 'activeTab' => $activeTab])
            <div class="bg-curema-card rounded-2xl border border-curema-border p-5">
                <h2 class="font-semibold mb-4">Order History</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" data-export="true">
                        <thead>
                            <tr class="text-left text-curema-sub text-xs">
                                <th class="font-medium pb-3">Order ID</th>
                                <th class="font-medium pb-3">Date</th>
                                <th class="font-medium pb-3">Total</th>
                                <th class="font-medium pb-3">Payment</th>
                                <th class="font-medium pb-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customer['order_history'] as $o)
                                <tr class="border-t border-curema-border">
                                    <td class="py-3 flex items-center gap-2">
                                        <span class="w-7 h-7 rounded-lg bg-curema-bg flex items-center justify-center text-xs">🧾</span>
                                        {{ $o['id'] }}
                                    </td>
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

            @include('crm.partials.customer-table', [
                'tableTitle' => 'All Customers',
                'tableCustomers' => $tableCustomers,
                'showViewAllLink' => false,
            ])
        </div>

        <div class="w-[220px] shrink-0 flex flex-col gap-4">
            @include('crm.partials.customer-insight')
            @include('crm.partials.upcoming-followups')
            @include('crm.partials.recent-activities')
        </div>
    </div>

@endsection
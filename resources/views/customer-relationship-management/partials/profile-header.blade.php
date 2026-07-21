@php
    $badgeClass = \App\Support\DemoCustomers::segmentBadgeClasses();
@endphp

<div class="bg-curema-card rounded-2xl border border-curema-border p-5 mb-4">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <p class="font-bold text-lg leading-tight">{{ $customer['full_name'] }}</p>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClass[$customer['segment']] ?? 'bg-curema-bg' }}">
                    {{ $customer['segment'] }}
                </span>
            </div>
            <p class="text-sm text-curema-purple font-medium mt-1">{{ $customer['email'] }}</p>
            <p class="text-xs text-curema-sub mt-1">{{ $customer['phone'] ?: '—' }}</p>
            <p class="text-xs text-curema-sub mt-1">Customer since {{ $customer['customer_since'] }}</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-10 gap-y-4">
            <div>
                <p class="text-[11px] text-curema-sub">Total Orders</p>
                <p class="font-bold">{{ $customer['total_orders'] }}</p>
            </div>
            <div>
                <p class="text-[11px] text-curema-sub">Total Spent</p>
                <p class="font-bold">{{ $customer['total_spent'] }}</p>
            </div>
            <div>
                <p class="text-[11px] text-curema-sub">Avg. Order Value</p>
                <p class="font-bold">{{ $customer['avg_order_value'] }}</p>
            </div>
            <div>
                <p class="text-[11px] text-curema-sub">Last Ordered</p>
                <p class="font-bold">{{ $customer['last_ordered'] }}</p>
            </div>
            <div>
                <p class="text-[11px] text-curema-sub">Customer Lifetime Value</p>
                <p class="font-bold">{{ $customer['clv'] }}</p>
            </div>
        </div>
    </div>

    <div class="flex gap-6 mt-5 border-t border-curema-border pt-3 text-sm">
        @php
            $tabs = [
                'overview' => ['label' => 'Overview', 'route' => route('customers.show', $customer['id'])],
                'order-history' => ['label' => 'Order History', 'route' => route('customers.orders', $customer['id'])],
                'communication' => ['label' => 'Communication', 'route' => route('customers.communication', $customer['id'])],
            ];
        @endphp
        @foreach ($tabs as $key => $tab)
            <a href="{{ $tab['route'] }}"
               class="pb-2 -mb-[13px] border-b-2 font-medium transition
                      {{ $activeTab === $key ? 'border-curema-purple text-curema-purple' : 'border-transparent text-curema-sub hover:text-curema-ink' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>
</div>

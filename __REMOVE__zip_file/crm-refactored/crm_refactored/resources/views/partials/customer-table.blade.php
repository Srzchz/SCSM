@php
    $badgeClass = \App\Support\DemoCustomers::segmentBadgeClasses();
    $showViewAllLink = $showViewAllLink ?? true;
    $tableId = $tableId ?? 'customer-table-body';
    $renderExtras = $renderExtras ?? ($tableTitle === 'All Customers');
@endphp

<div class="bg-curema-card rounded-2xl border border-curema-border p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold">{{ $tableTitle }}</h2>
        @if ($showViewAllLink)
            <a href="{{ route('customers.index') }}" class="text-xs text-curema-purple font-medium">View all customers</a>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm border-separate" style="border-spacing: 0;" data-export="true">
            <thead>
                <tr class="text-left text-curema-sub text-xs">
                    <th class="font-medium pb-3 pr-4">Customer</th>
                    <th class="font-medium pb-3 pr-4 whitespace-nowrap">Total Orders</th>
                    <th class="font-medium pb-3 pr-4 whitespace-nowrap">Total Spent</th>
                    <th class="font-medium pb-3 pr-4 whitespace-nowrap">CLV</th>
                    <th class="font-medium pb-3 pr-4 whitespace-nowrap">Last Order</th>
                    <th class="font-medium pb-3 pr-4 whitespace-nowrap">Segment</th>
                    <th class="pb-3"></th>
                </tr>
            </thead>
            <tbody id="{{ $tableId }}">
                @foreach ($tableCustomers as $c)
                    <tr class="border-t border-curema-border hover:bg-curema-bg/60 transition cursor-pointer customer-row"
                        data-search="{{ strtolower($c['name'] . ' ' . $c['email']) }}"
                        onclick="window.location='{{ route('customers.show', $c['id']) }}'">
                        <td class="py-3 pr-4">
                            <a href="{{ route('customers.show', $c['id']) }}" class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-curema-bg flex items-center justify-center shrink-0">👤</div>
                                <div class="min-w-0">
                                    <p class="font-medium leading-tight hover:text-curema-purple truncate">{{ $c['name'] }}</p>
                                    <p class="text-xs text-curema-sub truncate">{{ $c['email'] }}</p>
                                </div>
                            </a>
                        </td>
                        <td class="pr-4 whitespace-nowrap">{{ $c['orders'] }}</td>
                        <td class="pr-4 whitespace-nowrap">{{ $c['spent'] }}</td>
                        <td class="pr-4 whitespace-nowrap">{{ $c['clv'] }}</td>
                        <td class="pr-4 whitespace-nowrap">{{ $c['last'] }}</td>
                        <td class="pr-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap {{ $badgeClass[$c['segment']] ?? 'bg-curema-bg' }}">
                                {{ $c['segment'] }}
                            </span>
                        </td>
                        <td class="text-curema-sub" onclick="event.stopPropagation()">⋮</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($showViewAllLink)
        <div class="text-center mt-4">
            <a href="{{ route('customers.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-curema-purplesoft text-curema-purple text-sm font-semibold">
                View all customers →
            </a>
        </div>
    @endif
</div>

@if ($renderExtras)
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Curema.customers.renderExtraRows('{{ $tableId }}', @json($badgeClass));
        });
    </script>
@endif
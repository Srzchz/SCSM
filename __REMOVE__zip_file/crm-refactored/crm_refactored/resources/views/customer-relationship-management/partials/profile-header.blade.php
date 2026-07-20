<div class="bg-curema-card rounded-2xl border border-curema-border p-5 mb-4"
     x-data="{ confirmingDelete: false }">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-curema-purplesoft flex items-center justify-center text-2xl shrink-0">👤</div>
            <div>
                <p class="font-bold text-lg leading-tight">{{ $customer['full_name'] }}</p>
                <p class="text-xs text-curema-sub flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
                    <span>✉️ {{ $customer['email'] }}</span>
                    <span>📞 {{ $customer['phone'] }}</span>
                </p>
                <p class="text-xs text-curema-sub mt-1">Customer since {{ $customer['customer_since'] }}</p>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:flex lg:items-center gap-x-8 gap-y-3 lg:gap-x-10">
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

            <button type="button" @click="confirmingDelete = true"
                    title="Delete this customer"
                    class="w-9 h-9 rounded-xl border border-curema-border flex items-center justify-center text-curema-coral shrink-0 hover:bg-curema-coral/20 transition">
                🗑️
            </button>
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

    {{-- Delete confirmation modal --}}
    <div x-show="confirmingDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/30" @click="confirmingDelete = false"></div>
        <div x-show="confirmingDelete" x-transition
             class="relative bg-curema-card rounded-2xl border border-curema-border shadow-xl w-full max-w-sm p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-curema-coral/40 flex items-center justify-center text-2xl mx-auto mb-4">🗑️</div>
            <h2 class="font-bold text-lg mb-2">Delete this customer?</h2>
            <p class="text-sm text-curema-sub mb-6">
                This permanently removes <strong>{{ $customer['full_name'] }}</strong> and every order, communication
                log, and chat message tied to them. This can't be undone.
            </p>
            <div class="flex gap-3">
                <button type="button" @click="confirmingDelete = false"
                        class="flex-1 py-2.5 rounded-xl border border-curema-border text-sm font-semibold">
                    Cancel
                </button>
                <form method="POST" action="{{ route('customers.destroy', $customer['id']) }}" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-curema-coral text-curema-ink text-sm font-semibold">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
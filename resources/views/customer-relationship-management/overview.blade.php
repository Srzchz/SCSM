@extends('crm.layouts.app')

@section('title', $customer['full_name'] . ' - Overview')

@php
    $active = 'Customers';
    $activeTab = 'overview';
@endphp

@section('content')

    @include('crm.partials.topbar')

    <div class="flex items-start gap-4">
        <div class="flex-1 min-w-0 flex flex-col gap-4">
            @include('customer-relationship-management.partials.profile-header', ['customer' => $customer, 'activeTab' => $activeTab])

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-curema-card rounded-2xl border border-curema-border p-5" x-data="{ editing: {{ $errors->any() ? 'true' : 'false' }} }">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold">Customer Information</h2>
                        <button type="button" @click="editing = !editing" class="text-xs text-curema-purple font-medium">
                            <span x-show="!editing">Edit ✎</span>
                            <span x-show="editing">Cancel</span>
                        </button>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 px-3 py-2.5 rounded-xl bg-curema-coral/40 text-xs text-curema-ink">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <dl class="space-y-3 text-sm" x-show="!editing">
                        <div>
                            <dt class="text-xs text-curema-sub">Full Name</dt>
                            <dd class="font-medium">{{ $customer['full_name'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-curema-sub">Email</dt>
                            <dd class="font-medium">{{ $customer['email'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-curema-sub">Address</dt>
                            <dd class="font-medium">{{ $customer['address'] ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-curema-sub">Phone</dt>
                            <dd class="font-medium">{{ $customer['phone'] ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-curema-sub">Date of Birth</dt>
                            <dd class="font-medium">{{ $customer['dob_display'] ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-curema-sub">Customer Type</dt>
                            <dd class="font-medium">{{ $customer['customer_type'] ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-curema-sub">Preferred Channel</dt>
                            <dd class="font-medium">{{ $customer['preferred_channel'] ?: '—' }}</dd>
                        </div>
                    </dl>

                    <form method="POST" action="{{ route('customers.update', $customer['id']) }}" class="space-y-3" x-show="editing" x-cloak>
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs text-curema-sub mb-1">Full Name</label>
                            <input type="text" name="full_name" value="{{ old('full_name', $customer['full_name']) }}"
                                   class="w-full px-3 py-2 rounded-lg bg-curema-bg border border-curema-border text-sm focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
                        </div>
                        <div>
                            <label class="block text-xs text-curema-sub mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $customer['email']) }}"
                                   class="w-full px-3 py-2 rounded-lg bg-curema-bg border border-curema-border text-sm focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
                        </div>
                        <div>
                            <label class="block text-xs text-curema-sub mb-1">Address</label>
                            <input type="text" name="address" value="{{ old('address', $customer['address']) }}"
                                   class="w-full px-3 py-2 rounded-lg bg-curema-bg border border-curema-border text-sm focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
                        </div>
                        <div>
                            <label class="block text-xs text-curema-sub mb-1">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $customer['phone']) }}"
                                   class="w-full px-3 py-2 rounded-lg bg-curema-bg border border-curema-border text-sm focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
                        </div>
                        <div>
                            <label class="block text-xs text-curema-sub mb-1">Date of Birth</label>
                            <input type="date" name="dob" value="{{ old('dob', $customer['dob']) }}"
                                   class="w-full px-3 py-2 rounded-lg bg-curema-bg border border-curema-border text-sm focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
                        </div>
                        <div>
                            <label class="block text-xs text-curema-sub mb-1">Customer Type</label>
                            <select name="customer_type" class="w-full px-3 py-2 rounded-lg bg-curema-bg border border-curema-border text-sm focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
                                @foreach (['New Customer', 'Repeat Buyer', 'VIP', 'Inactive'] as $type)
                                    <option value="{{ $type }}" @selected(old('customer_type', $customer['customer_type']) === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-curema-sub mb-1">Preferred Channel</label>
                            <input type="text" name="preferred_channel" value="{{ old('preferred_channel', $customer['preferred_channel']) }}" placeholder="Email, SMS"
                                   class="w-full px-3 py-2 rounded-lg bg-curema-bg border border-curema-border text-sm focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
                        </div>

                        <div class="flex gap-2 pt-1">
                            <button type="button" @click="editing = false" class="flex-1 py-2 rounded-lg border border-curema-border text-sm font-medium">
                                Cancel
                            </button>
                            <button type="submit" class="flex-1 py-2 rounded-lg bg-curema-purple text-white text-sm font-semibold">
                                Save
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-curema-card rounded-2xl border border-curema-border p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold">Recent Orders</h2>
                        <a href="{{ route('customers.orders', $customer['id']) }}" class="text-xs text-curema-purple font-medium">View all orders</a>
                    </div>
                    <ul class="divide-y divide-curema-border">
                        @foreach ($customer['recent_orders'] as $o)
                            <li class="py-2.5 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-curema-bg flex items-center justify-center text-xs shrink-0">🧾</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium leading-tight">Order #{{ $o['id'] }}</p>
                                    <p class="text-xs text-curema-sub">{{ $o['date'] }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-semibold">{{ $o['amount'] }}</p>
                                    <p class="text-[11px] text-curema-green">{{ $o['status'] }}</p>
                                </div>
                            </li>
                        @endforeach
                        @if (empty($customer['recent_orders']))
                            <li class="py-4 text-sm text-curema-sub text-center">No orders yet.</li>
                        @endif
                    </ul>
                </div>
            </div>

            @include('crm.partials.customer-table', [
                'tableTitle' => 'All Customers',
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
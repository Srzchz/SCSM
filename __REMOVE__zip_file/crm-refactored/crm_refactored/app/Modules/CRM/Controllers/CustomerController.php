<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Modules\CRM\Models\CustomerInsight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        return view('customer-relationship-management.index', [
            'tableCustomers' => $this->allCustomersTable(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'dob' => ['nullable', 'date'],
        ]);

        [$firstName, $lastName] = $this->splitName($validated['full_name']);

        DB::transaction(function () use ($validated, $firstName, $lastName) {
            $customer = Customer::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $validated['email'],
                'password' => Hash::make(str()->random(16)), // placeholder — real signup flow lives in E-commerce
                'phone_number' => $validated['phone'] ?? null,
                'status' => 'Active',
                'role' => 'customer',
            ]);

            $customer->insight()->create([
                'address' => $validated['address'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'customer_since' => now(),
                'customer_type' => 'New Customer',
            ]);
        });

        return redirect()->route('customers.index')->with('success', 'Customer added successfully.');
    }

    public function update(Request $request, int $customer)
    {
        $c = Customer::findOrFail($customer);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email,' . $c->customer_id . ',customer_id'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'dob' => ['nullable', 'date'],
            'customer_type' => ['nullable', 'string', 'max:50'],
            'preferred_channel' => ['nullable', 'string', 'max:100'],
        ]);

        [$firstName, $lastName] = $this->splitName($validated['full_name']);

        DB::transaction(function () use ($c, $validated, $firstName, $lastName) {
            $c->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $validated['email'],
                'phone_number' => $validated['phone'] ?? null,
            ]);

            $c->insight()->updateOrCreate([], [
                'address' => $validated['address'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'customer_type' => $validated['customer_type'] ?? null,
                'preferred_channel' => $validated['preferred_channel'] ?? null,
            ]);
        });

        return redirect()->route('customers.show', $c->customer_id)->with('success', 'Customer information updated.');
    }

    /**
     * Deletes the customer row itself. crm_customer_insights cascades via
     * its FK. orders/communication_logs/chat_messages/follow_ups/activities
     * also cascade via their own FKs into `customers`.
     */
    public function destroy(int $customer)
    {
        $c = Customer::findOrFail($customer);
        $name = $c->full_name;
        $c->delete();

        return redirect()->route('customers.index')->with('success', "\"{$name}\" was deleted.");
    }

    public function show(int $customer)
    {
        return view('customer-relationship-management.overview', [
            'customer' => $this->buildCustomerArray($customer),
            'tableCustomers' => $this->allCustomersTable(),
        ]);
    }

    public function orders(int $customer)
    {
        return view('customer-relationship-management.order-history', [
            'customer' => $this->buildCustomerArray($customer),
            'tableCustomers' => $this->allCustomersTable(),
        ]);
    }

    public function communication(int $customer)
    {
        return view('customer-relationship-management.communication', [
            'customer' => $this->buildCustomerArray($customer),
            'tableCustomers' => $this->allCustomersTable(),
        ]);
    }

    protected function allCustomersTable()
    {
        return Customer::withCount('orders')
            ->withSum('orders', 'grand_total')
            ->withMax('orders', 'created_at')
            ->with('insight')
            ->orderByDesc('orders_sum_grand_total')
            ->get()
            ->map(function ($c) {
                $lastOrderDate = $c->orders_max_created_at ? \Carbon\Carbon::parse($c->orders_max_created_at) : null;
                $totalSpent = (float) ($c->orders_sum_grand_total ?? 0);

                return [
                    'id' => $c->customer_id,
                    'name' => $c->full_name,
                    'email' => $c->email,
                    'orders' => $c->orders_count,
                    'spent' => '₱' . number_format($totalSpent, 2),
                    'clv' => '₱' . number_format($c->insight->clv ?? 0, 2),
                    'last' => $lastOrderDate?->format('M j, Y') ?? '—',
                    'segment' => Customer::computeSegment($c->orders_count, $totalSpent, $lastOrderDate),
                ];
            });
    }

    protected function buildCustomerArray(int $id): array
    {
        $c = Customer::with(['orders', 'communicationLogs.chatMessages', 'insight'])->findOrFail($id);

        // created_at stands in for "order date" — the canonical orders
        // table doesn't carry a separate order-date column.
        $orders = $c->orders->sortByDesc('created_at')->values();

        $realTotalOrders = $orders->count();
        $realTotalSpent = (float) $orders->sum('grand_total');
        $realAvgOrderValue = $realTotalOrders > 0 ? $realTotalSpent / $realTotalOrders : 0;
        $lastOrderDate = $orders->first()?->created_at;
        $segment = Customer::computeSegment($realTotalOrders, $realTotalSpent, $lastOrderDate);

        return [
            'id' => $c->customer_id,
            'full_name' => $c->full_name,
            'email' => $c->email,
            'phone' => $c->phone_number,
            'address' => $c->insight->address ?? null,
            'dob' => $c->insight->dob?->format('Y-m-d'),
            'dob_display' => $c->insight->dob?->format('F j, Y'),
            'customer_since' => $c->insight->customer_since?->format('F j, Y'),
            'customer_type' => $c->insight->customer_type ?? null,
            'preferred_channel' => $c->insight->preferred_channel ?? null,
            'segment' => $segment,
            'total_orders' => $realTotalOrders,
            'total_spent' => '₱' . number_format($realTotalSpent, 2),
            'avg_order_value' => '₱' . number_format($realAvgOrderValue, 0),
            'last_ordered' => $lastOrderDate?->format('M j, Y') ?? '—',
            'clv' => '₱' . number_format($c->insight->clv ?? 0, 2),

            // quantity/price-per-line have no home on the canonical orders
            // header row (no order_items table was provided) — dropped from
            // both arrays below. See change log.
            'recent_orders' => $orders->take(5)->map(fn ($o) => [
                'id' => $o->order_number,
                'date' => $o->created_at->format('M j, Y'),
                'amount' => '₱' . number_format($o->grand_total, 2),
                'status' => $o->status,
            ])->values()->all(),

            'order_history' => $orders->map(fn ($o) => [
                'id' => $o->order_number,
                'date' => $o->created_at->format('M j, Y'),
                'total' => '₱' . number_format($o->grand_total, 2),
                'status' => $o->status,
                'payment_status' => $o->payment_status,
            ])->values()->all(),

            'communication_logs' => $c->communicationLogs->sortByDesc('log_date')->map(fn ($l) => [
                'ticket_id' => $l->ticket_id,
                'issue' => $l->issue,
                'details' => $l->details,
                'date' => $l->log_date->format('M j, Y'),
                'mode' => $l->mode,
                'status' => $l->status,
                'chats' => $l->chatMessages->sortBy('sent_at')->map(fn ($m) => [
                    'from' => $m->sender,
                    'text' => $m->message,
                    'time' => $m->sent_at->format('g:i A'),
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    private function splitName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);

        return [$parts[0], $parts[1] ?? ''];
    }
}

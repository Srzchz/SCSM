<?php

namespace App\Support;

use App\Models\Customer;

class CustomerInsightService
{
    public static function segments(): array
    {
        $enriched = Customer::withCount('orders')
            ->withSum('orders', 'grand_total')
            ->withMax('orders', 'created_at')
            ->get()
            ->map(function ($c) {
                $totalOrders = $c->orders_count;
                $totalSpent = (float) ($c->orders_sum_grand_total ?? 0);
                $lastOrderDate = $c->orders_max_created_at ? \Carbon\Carbon::parse($c->orders_max_created_at) : null;

                return (object) [
                    'segment' => Customer::computeSegment($totalOrders, $totalSpent, $lastOrderDate),
                ];
            });

        $total = $enriched->count();
        $pct = fn (int $count) => $total > 0 ? round(($count / $total) * 100) : 0;

        $repeatBuyer = $enriched->where('segment', 'Repeat Buyer')->count();
        $vip = $enriched->where('segment', 'VIP')->count();
        $newCustomer = $enriched->where('segment', 'New Customer')->count();
        $inactive = $enriched->where('segment', 'Inactive')->count();

        $segments = [
            ['label' => 'Repeat Buyer', 'pct' => $pct($repeatBuyer), 'color' => '#9CFF9F'],
            ['label' => 'VIP', 'pct' => $pct($vip), 'color' => '#AD9EFF'],
            ['label' => 'New Customer', 'pct' => $pct($newCustomer), 'color' => '#7ED8FF'],
            ['label' => 'Inactive', 'pct' => $pct($inactive), 'color' => '#B0B4EC'],
        ];

        $topSegment = collect($segments)->sortByDesc('pct')->first();

        return [
            'hint' => $topSegment ? "{$topSegment['label']} is your largest segment right now." : '',
            'segments' => $segments,
        ];
    }
}
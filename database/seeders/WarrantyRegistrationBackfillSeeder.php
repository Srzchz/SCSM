<?php

namespace Database\Seeders;

use App\Models\OrderItem;
use App\Modules\ASCM\Models\WarrantyRegistration;
use Illuminate\Database\Seeder;

/**
 * Registers warranty coverage retroactively for every order item that
 * doesn't already have one.
 *
 * Live purchases get a WarrantyRegistration via
 * CustomerMockController::placeOrder() -> POST /api/ascm/warranty-registrations
 * (see WarrantyRegistrationApiController), but that call only ever fires
 * for orders placed through the actual mock checkout flow. Every order
 * from OrderSeeder / HistoricalSalesOrderSeeder / SalesOrderSeeder (or
 * anything else inserted directly, bypassing that flow) never gets a
 * registration -- which meant filing a "Warranty" case against any
 * pre-existing/seeded order in CaseController::store() always silently
 * skipped creating a WarrantyClaim, since there was nothing eligible to
 * match against. Run this after every seeder that creates orders.
 *
 * Unlike the live API (which always starts coverage "now", since it's
 * registering a fresh purchase), this uses the order's own created_at as
 * coverage_start -- more correct for backfilling historical data, and it
 * means older orders can end up realistically 'expired' rather than
 * every seeded record claiming to be freshly covered.
 */
class WarrantyRegistrationBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $alreadyCovered = WarrantyRegistration::whereNotNull('order_item_id')
            ->pluck('order_item_id')
            ->flip();

        OrderItem::with('order')
            ->chunkById(200, function ($items) use ($alreadyCovered) {
                foreach ($items as $item) {
                    if ($alreadyCovered->has($item->id) || ! $item->order) {
                        continue;
                    }

                    $start = ($item->order->created_at ?? now())->copy();
                    $end = $start->copy()->addMonths(12);

                    WarrantyRegistration::create([
                        'customer_id' => $item->order->customer_id,
                        'order_id' => $item->order_id,
                        'order_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'warranty_type' => 'standard',
                        'coverage_start' => $start,
                        'coverage_end' => $end,
                        'coverage_status' => $end->isPast() ? 'expired' : 'eligible',
                    ]);
                }
            });
    }
}

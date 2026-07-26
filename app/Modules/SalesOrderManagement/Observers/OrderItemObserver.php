<?php

namespace App\Modules\SalesOrderManagement\Observers;

use App\Models\OrderItem;
use App\Modules\SalesOrderManagement\Services\AutoQuotationService;
use Illuminate\Support\Facades\DB;

/**
 * Order items are usually written one-by-one right after the order itself
 * (see CustomerMockController::placeOrder()), outside of a single wrapping
 * transaction. Re-running the sync on every item write means the quotation
 * still ends up complete once the last item lands, without needing the
 * whole request to be transactional.
 */
class OrderItemObserver
{
    public function saved(OrderItem $item): void
    {
        DB::afterCommit(function () use ($item) {
            $order = $item->order()->first();

            if ($order) {
                app(AutoQuotationService::class)->syncFromOrder($order);
            }
        });
    }

    public function deleted(OrderItem $item): void
    {
        $this->saved($item);
    }
}

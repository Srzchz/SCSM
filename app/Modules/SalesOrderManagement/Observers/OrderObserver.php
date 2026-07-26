<?php

namespace App\Modules\SalesOrderManagement\Observers;

use App\Models\Order;
use App\Modules\SalesOrderManagement\Services\AutoQuotationService;
use Illuminate\Support\Facades\DB;

/**
 * Watches the CRM/e-commerce `orders` table (App\Models\Order) and keeps
 * SOM's Sales Quotations in sync automatically — no more manual
 * "+ New Quotation" clicks for orders that already exist in the CRM.
 */
class OrderObserver
{
    public function created(Order $order): void
    {
        $this->sync($order);
    }

    public function updated(Order $order): void
    {
        $this->sync($order);
    }

    protected function sync(Order $order): void
    {
        // Wait for the surrounding transaction (order + its items) to
        // actually commit before we try to price line items off it.
        DB::afterCommit(function () use ($order) {
            app(AutoQuotationService::class)->syncFromOrder($order->fresh());
        });
    }
}

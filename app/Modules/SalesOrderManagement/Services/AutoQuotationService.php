<?php

namespace App\Modules\SalesOrderManagement\Services;

use App\Models\Order;
use App\Models\TaxRegion;
use App\Models\User;
use App\Modules\SalesOrderManagement\Models\SalesQuotation;
use App\Modules\SalesOrderManagement\Models\SalesQuotationItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Keeps SOM's Sales Quotations in sync with the CRM's `orders` table.
 *
 * Whenever a customer places (or updates) an order on the CRM/e-commerce
 * side, this service creates the matching quotation automatically — status
 * mapped from the order's own status (pending -> Draft, processing -> Sent,
 * shipped/delivered -> Accepted, cancelled -> Rejected) — or, if one already
 * exists for that order, refreshes its line items, totals, and status. No
 * more pressing "+ New Quotation" by hand.
 *
 * Orders that originated FROM a quotation (see SalesOrderController::
 * acceptQuotation() + CustomerMockController::placeOrder(), which write the
 * mirrored `orders` row after the fact) are skipped, otherwise every
 * checkout would spawn a second, duplicate quotation for itself.
 */
class AutoQuotationService
{
    public function syncFromOrder(Order $order): ?SalesQuotation
    {
        $order->loadMissing('items.product');

        // Order came *from* SOM already (quotation accepted -> sales order ->
        // mirrored here). It already has a quotation behind it, so skip it.
        if ($this->originatedFromQuotation($order)) {
            return null;
        }

        if ($order->items->isEmpty()) {
            // Items are usually inserted right after the order in the same
            // request; nothing to price yet. Callers re-fire this on the
            // order_items side too, so it'll catch up.
            return null;
        }

        return DB::transaction(function () use ($order) {
            $existing = SalesQuotation::where('source_order_id', $order->order_id)->first();

            $subtotal = 0;
            $discountTotal = 0;
            $lines = [];

            foreach ($order->items as $item) {
                $unitPrice = (float) $item->unit_price;
                $qty = (int) $item->quantity;
                $lineGross = $unitPrice * $qty;

                $subtotal += $lineGross;
                $lines[] = [
                    'product_id'       => $item->product_id,
                    'quantity'         => $qty,
                    'unit_price'       => $unitPrice,
                    'discount_percent' => 0,
                    'line_total'       => round($lineGross, 2),
                ];
            }

            $taxRegion = TaxRegion::where('is_default', true)->first() ?? TaxRegion::firstOrFail();
            $taxable = $subtotal - $discountTotal;
            $tax = round($taxable * ((float) $taxRegion->vat_rate / 100), 2);

            $attrs = [
                'customer_id'      => $order->customer_id,
                'tax_region_id'    => $taxRegion->id,
                'quotation_date'   => now()->toDateString(),
                'valid_until'      => now()->addDays(15)->toDateString(),
                'status'           => $this->mapOrderStatus($order->status),
                'subtotal'         => round($subtotal, 2),
                'discount_amount'  => round($discountTotal, 2),
                'tax_amount'       => $tax,
                'total_amount'     => round($taxable + $tax, 2),
                'source_order_id'  => $order->order_id,
            ];

            if ($existing) {
                $existing->update($attrs);
                $existing->items()->delete();
                $quotation = $existing;
            } else {
                $quotation = SalesQuotation::create($attrs + [
                    'created_by' => Auth::id() ?? User::query()->value('id'),
                ]);
            }

            foreach ($lines as $line) {
                SalesQuotationItem::create($line + ['quotation_id' => $quotation->quotation_id]);
            }

            Log::info('AutoQuotationService: synced quotation from CRM order', [
                'order_id'      => $order->order_id,
                'quotation_id'  => $quotation->quotation_id,
            ]);

            return $quotation;
        });
    }

    protected function originatedFromQuotation(Order $order): bool
    {
        // CustomerMockController::placeOrder() stamps order_number with the
        // SOM sales order id it just created (e.g. "SO-0001").
        return is_string($order->order_number) && str_starts_with($order->order_number, 'SO-');
    }

    /**
     * Maps the CRM's order.status (pending/processing/shipped/delivered/
     * cancelled) to SOM's quotation status (Draft/Sent/Accepted/Rejected),
     * so a quotation synced from an already-fulfilled order doesn't sit
     * around looking like it's still awaiting action.
     */
    protected function mapOrderStatus(?string $orderStatus): string
    {
        return match (strtolower($orderStatus ?? '')) {
            'processing'          => 'Sent',
            'shipped', 'delivered' => 'Accepted',
            'cancelled'           => 'Rejected',
            default               => 'Draft', // pending / unknown
        };
    }
}

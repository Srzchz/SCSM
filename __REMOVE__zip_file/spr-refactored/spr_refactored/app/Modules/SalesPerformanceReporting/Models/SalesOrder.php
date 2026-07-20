<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * NOT SPR-owned. This is a read-only reference into Sales Order Management's
 * real `sales_orders` table (App\Modules\SalesOrderManagement\Models\SalesOrder),
 * kept here as a lightweight local model so SPR's reporting queries don't
 * need a cross-module import for a single aggregate count.
 *
 * SPR previously had its own duplicate `sales_orders` migration/model with
 * a different shape (single product_id/quantity/amount per order, a
 * closed_won/closed_lost/pending status enum, a `rep_id` pointing at SPR's
 * own SalesRep). That table and model are gone — this now points at the
 * real thing. Column differences from the old version:
 *   - PK is `sales_order_id`, not `id`
 *   - status column is `order_status`, not `status`
 *   - no closed_won value exists; 'Delivered' is the closest equivalent
 *     in the real enum (Pending, Processing, Shipped, Delivered, Cancelled)
 *   - no single product_id/quantity/amount on the order header — the real
 *     table is multi-line (see SalesOrderManagement\Models\SalesOrderItem)
 *     and totals live in `total_amount`
 *
 * If SPR ever needs more than a count (e.g. per-rep or per-product
 * reporting), don't extend this stub — import the real
 * SalesOrderManagement\Models\SalesOrder instead, since it owns the actual
 * relations (items, customer, salesRep, taxRegion).
 */
class SalesOrder extends Model
{
    protected $table = 'sales_orders';

    protected $primaryKey = 'sales_order_id';

    public $timestamps = false;

    protected $casts = [
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function scopeClosedWon($query)
    {
        return $query->where('order_status', 'Delivered');
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('order_date', [$start, $end]);
    }
}

<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use App\Models\Product;
use App\Modules\SalesPerformanceReporting\Models\Concerns\HasAttainment;
use Illuminate\Database\Eloquent\Model;

class ProductTarget extends Model
{
    use HasAttainment;

    public $timestamps = false;

    // Renamed per the SCSM monorepo migration: table is now module-prefixed.
    protected $table = 'sales_performance_reporting_product_targets';

    protected $fillable = ['product_id', 'period', 'target_amount', 'actual_amount'];

    /**
     * NOT SPR-owned. Points at the canonical Product model (App\Models\Product),
     * same one SOM/ASCM use — SPR previously had its own duplicate `products`
     * table (default `id` PK, `sku`/`status` fields). That's gone; the real
     * table's PK is `product_id`, not `id`.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}

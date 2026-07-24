<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'sku', 'status'];

    // salesOrders() relation removed — the real sales_orders table (owned by
    // Sales Order Management) has no product_id on the order header, it's
    // multi-line via SalesOrderItem. Wasn't used anywhere in this module.

    public function productTarget(string $period)
    {
        return $this->hasOne(ProductTarget::class)->where('period', $period);
    }
}

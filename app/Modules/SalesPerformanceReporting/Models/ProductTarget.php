<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use App\Modules\SalesPerformanceReporting\Models\Concerns\HasAttainment;
use Illuminate\Database\Eloquent\Model;

class ProductTarget extends Model
{
    use HasAttainment;

    protected $table = 'sales_performance_reporting_product_targets';

    public $timestamps = false;

    protected $fillable = ['product_id', 'period', 'target_amount', 'actual_amount'];

    /**
     * NOTE: assumed to be App\Models\Product, since the products table is
     * shared with Sales Order Management. If your product model lives
     * somewhere else (e.g. a module-specific namespace), update the
     * class reference below to match.
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }
}

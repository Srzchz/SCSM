<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use App\Modules\SalesPerformanceReporting\Models\Concerns\HasAttainment;
use Illuminate\Database\Eloquent\Model;

class ProductTarget extends Model
{
    use HasAttainment;

    public $timestamps = false;

    // Renamed per the SCSM monorepo migration: table is now module-prefixed.
    protected $table = 'sales_performance_reporting_product_targets';

    protected $fillable = ['product_id', 'period', 'target_amount', 'actual_amount'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

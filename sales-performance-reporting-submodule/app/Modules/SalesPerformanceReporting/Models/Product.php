<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'sku', 'status'];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function productTarget(string $period)
    {
        return $this->hasOne(ProductTarget::class)->where('period', $period);
    }
}

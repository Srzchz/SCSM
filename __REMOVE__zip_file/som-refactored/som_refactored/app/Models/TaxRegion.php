<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\SalesOrderManagement\Models\SalesOrder;
use App\Modules\SalesOrderManagement\Models\SalesQuotation;

class TaxRegion extends Model
{
    use HasFactory;

    protected $fillable = [
        'country',
        'vat_rate',
        'is_default',
    ];

    protected $casts = [
        'vat_rate'   => 'decimal:2',
        'is_default' => 'boolean',
    ];

    public function quotations()
    {
        return $this->hasMany(SalesQuotation::class, 'tax_region_id');
    }

    public function orders()
    {
        return $this->hasMany(SalesOrder::class, 'tax_region_id');
    }
}

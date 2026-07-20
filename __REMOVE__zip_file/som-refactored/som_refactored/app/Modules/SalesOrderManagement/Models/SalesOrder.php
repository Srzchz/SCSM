<?php

namespace App\Modules\SalesOrderManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\TaxRegion;
use App\Models\User;

class SalesOrder extends Model
{
    use HasFactory;

    protected $primaryKey = 'sales_order_id';

    protected $fillable = [
        'quotation_id',
        'customer_id',
        'tax_region_id',
        'sales_rep_id',
        'order_date',
        'order_status',
        'on_hold',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_fee',
        'total_amount',
    ];

    protected $casts = [
        'order_date' => 'date',
        'on_hold'    => 'boolean',
    ];

    public function quotation()
    {
        return $this->belongsTo(SalesQuotation::class, 'quotation_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function taxRegion()
    {
        return $this->belongsTo(TaxRegion::class, 'tax_region_id');
    }

    public function salesRep()
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class, 'sales_order_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'sales_order_id');
    }
}

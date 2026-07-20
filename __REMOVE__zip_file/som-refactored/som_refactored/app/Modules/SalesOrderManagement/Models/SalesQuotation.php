<?php

namespace App\Modules\SalesOrderManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\TaxRegion;
use App\Models\User;

class SalesQuotation extends Model
{
    use HasFactory;

    protected $table = 'sales_order_management_sales_quotations';

    protected $primaryKey = 'quotation_id';

    protected $fillable = [
        'customer_id',
        'tax_region_id',
        'quotation_date',
        'valid_until',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'created_by',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function taxRegion()
    {
        return $this->belongsTo(TaxRegion::class, 'tax_region_id');
    }

    public function items()
    {
        return $this->hasMany(SalesQuotationItem::class, 'quotation_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salesOrder()
    {
        return $this->hasOne(SalesOrder::class, 'quotation_id');
    }
}

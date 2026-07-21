<?php

namespace App\Modules\SalesOrderManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class SalesQuotationItem extends Model
{
    use HasFactory;

    protected $table = 'sales_order_management_sales_quotation_items';

    protected $primaryKey = 'quotation_item_id';

    protected $fillable = [
        'quotation_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount_percent',
        'line_total',
    ];

    public function quotation()
    {
        return $this->belongsTo(SalesQuotation::class, 'quotation_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}

<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    public $timestamps = false;

    protected $fillable = ['order_no', 'rep_id', 'product_id', 'quantity', 'amount', 'status', 'order_date'];

    protected $casts = [
        'order_date' => 'date',
        'amount'     => 'decimal:2',
    ];

    public function rep()
    {
        return $this->belongsTo(SalesRep::class, 'rep_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeClosedWon($query)
    {
        return $query->where('status', 'closed_won');
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('order_date', [$start, $end]);
    }
}

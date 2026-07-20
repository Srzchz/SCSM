<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

class SalesRep extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'name', 'region_id', 'hire_date', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    // salesOrders() relation removed — the real sales_orders table (owned by
    // Sales Order Management) has sales_rep_id -> User, not rep_id -> this
    // model. Wasn't used anywhere in this module.

    public function targets()
    {
        return $this->hasMany(RepTarget::class, 'rep_id');
    }

    public function targetForPeriod(string $period)
    {
        return $this->hasOne(RepTarget::class, 'rep_id')->where('period', $period);
    }
}

<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * This is the "display" record used for target-tracking (name, region,
 * status). Actual sold amounts are attributed on sales_orders via
 * sales_rep_id, which points at users.id — see the user_id column here for
 * the mapping used by TargetSyncService.
 */
class SalesRep extends Model
{
    protected $table = 'sales_reps';

    public $timestamps = false;

    protected $fillable = ['user_id', 'name', 'region_id', 'hire_date', 'status'];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
}

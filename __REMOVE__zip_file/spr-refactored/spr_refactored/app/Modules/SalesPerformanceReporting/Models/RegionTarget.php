<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use App\Modules\SalesPerformanceReporting\Models\Concerns\HasAttainment;
use Illuminate\Database\Eloquent\Model;

class RegionTarget extends Model
{
    use HasAttainment;

    public $timestamps = false;

    // Renamed per the SCSM monorepo migration: table is now module-prefixed.
    protected $table = 'sales_performance_reporting_region_targets';

    protected $fillable = ['region_id', 'period', 'target_amount', 'actual_amount'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}

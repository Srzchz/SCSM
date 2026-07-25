<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use App\Modules\SalesPerformanceReporting\Models\Concerns\HasAttainment;
use Illuminate\Database\Eloquent\Model;

class RepTarget extends Model
{
    use HasAttainment;

    protected $table = 'sales_performance_reporting_rep_targets';

    public $timestamps = false;

    protected $fillable = ['rep_id', 'period', 'target_amount', 'actual_amount'];

    public function rep()
    {
        return $this->belongsTo(SalesRep::class, 'rep_id');
    }
}

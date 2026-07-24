<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    public $timestamps = false; // table only has created_at, populated by DB default

    protected $fillable = ['name'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function salesReps()
    {
        return $this->hasMany(SalesRep::class);
    }

    public function regionTarget(string $period)
    {
        return $this->hasOne(RegionTarget::class)->where('period', $period);
    }
}

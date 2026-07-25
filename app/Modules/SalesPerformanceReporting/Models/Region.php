<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'regions';

    public $timestamps = false;

    protected $fillable = ['name'];

    public function reps()
    {
        return $this->hasMany(SalesRep::class, 'region_id');
    }
}

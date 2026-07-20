<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    public $timestamps = false;

    // Renamed per the SCSM monorepo migration: table is now module-prefixed.
    protected $table = 'sales_performance_reporting_alerts';

    protected $fillable = [
        'category', 'title', 'description', 'link_label', 'link_url',
        'related_type', 'related_id', 'is_read',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
    ];

    public function timeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function scopeCategory($query, string $category)
    {
        return $category === 'all' ? $query : $query->where('category', $category);
    }
}

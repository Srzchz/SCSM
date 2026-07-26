<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Alert extends Model
{
    protected $table = 'sales_performance_reporting_alerts';

    public $timestamps = false;

    protected $fillable = [
        'dedupe_key', 'category', 'title', 'description',
        'link_label', 'link_url', 'related_type', 'related_id',
        'is_read', 'created_at',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
    ];

    public function timeAgo(): string
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function icon(): string
    {
        return match ($this->category) {
            'critical' => '⚠️',
            'warning'  => '❗',
            'positive' => '📈',
            default    => 'ℹ️',
        };
    }
}

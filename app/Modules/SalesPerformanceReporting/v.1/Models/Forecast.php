<?php

namespace App\Modules\SalesPerformanceReporting\Models;

use Illuminate\Database\Eloquent\Model;

class Forecast extends Model
{
    public const METHOD_LINEAR = 'linear_regression';
    public const METHOD_WMA = 'weighted_moving_average';

    protected $table = 'sales_performance_reporting_forecasts';

    protected $fillable = ['period_month', 'method', 'forecasted_amount'];

    protected $casts = [
        'period_month' => 'date',
        'forecasted_amount' => 'float',
    ];

    public function methodLabel(): string
    {
        return match ($this->method) {
            self::METHOD_LINEAR => 'Linear Regression',
            self::METHOD_WMA    => 'Weighted Moving Average',
            default             => $this->method,
        };
    }
}

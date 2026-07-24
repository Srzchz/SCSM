<?php

namespace App\Modules\SalesPerformanceReporting\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The old controllers hardcoded `private const PERIOD = '2026-Q2';` in
 * three separate places. That's the opposite of automated — it would need
 * to be manually bumped by hand every quarter. Instead, the "current"
 * period is derived from the data itself: the quarter containing the most
 * recent sales order (falling back to today's quarter if there's no data
 * yet at all).
 */
class PeriodHelper
{
    public static function current(): string
    {
        $latestOrderDate = DB::table('sales_orders')->max('order_date');

        $date = $latestOrderDate ? Carbon::parse($latestOrderDate) : Carbon::now();

        return $date->year . '-Q' . $date->quarter;
    }

    /**
     * @return array{0: Carbon, 1: Carbon} [start of quarter, end of quarter]
     */
    public static function bounds(string $period): array
    {
        [$year, $q] = explode('-Q', $period);

        $startMonth = ((int) $q - 1) * 3 + 1;
        $start = Carbon::create((int) $year, $startMonth, 1)->startOfDay();
        $end = (clone $start)->addMonths(3)->subDay()->endOfDay();

        return [$start, $end];
    }
}

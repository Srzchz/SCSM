<?php

namespace App\Modules\SalesPerformanceReporting\Models\Concerns;

trait HasAttainment
{
    public function attainmentPct(): float
    {
        if ((float) $this->target_amount <= 0) {
            return 0.0;
        }

        return round(((float) $this->actual_amount / (float) $this->target_amount) * 100, 1);
    }

    /** Capped at 100 so progress bars never overflow their track. */
    public function progressWidth(): float
    {
        return min(100, $this->attainmentPct());
    }

    public function attainmentStatus(): string
    {
        $pct = $this->attainmentPct();

        if ($pct >= 100) {
            return 'exceeded';
        }

        if ($pct >= 80) {
            return 'on-track';
        }

        return 'at-risk';
    }

    public function attainmentLabel(): string
    {
        return match ($this->attainmentStatus()) {
            'exceeded' => 'Exceeded',
            'on-track' => 'On Track',
            default    => 'At Risk',
        };
    }

    public function actualFormatted(): string
    {
        return '₱' . number_format((float) $this->actual_amount / 1000, 1) . 'K';
    }

    public function targetFormatted(): string
    {
        return '₱' . number_format((float) $this->target_amount / 1000, 1) . 'K';
    }
}

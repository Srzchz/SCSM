<?php

namespace App\Modules\SalesPerformanceReporting\Models\Concerns;

trait HasAttainment
{
    public function attainmentPct(): int
    {
        if ((float) $this->target_amount <= 0) {
            return 0;
        }
        return (int) round(((float) $this->actual_amount / (float) $this->target_amount) * 100);
    }

    public function attainmentStatus(): string
    {
        $pct = $this->attainmentPct();
        if ($pct >= 100) return 'exceeded';
        if ($pct >= 80) return 'on-track';
        return 'at-risk';
    }

    public function attainmentLabel(): string
    {
        $status = $this->attainmentStatus();
        $text = $status === 'on-track' ? 'On track' : ($status === 'at-risk' ? 'At risk' : 'Exceeded');
        return $this->attainmentPct() . '% - ' . $text;
    }

    public function progressWidth(): int
    {
        return min($this->attainmentPct(), 100);
    }

    public function formatMoney(float $amount, string $symbol = '₱'): string
    {
        return $amount >= 1000000
            ? $symbol . number_format($amount / 1000000, 2) . 'M'
            : $symbol . number_format($amount / 1000, 0) . 'K';
    }

    public function actualFormatted(string $symbol = '₱'): string
    {
        return $this->formatMoney((float) $this->actual_amount, $symbol);
    }

    public function targetFormatted(string $symbol = '₱'): string
    {
        return $this->formatMoney((float) $this->target_amount, $symbol);
    }
}

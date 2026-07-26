<?php

namespace App\Support;

use App\Modules\CRM\Models\Activity;
use App\Modules\CRM\Models\FollowUp;

class CustomerActivityService
{
    /**
     * Single source of truth for the "Upcoming Follow-ups" widget.
     * Not-yet-done follow-ups, due today or later, soonest first.
     */
    public static function upcomingFollowUps(int $limit = 5): array
    {
        return FollowUp::with('customer')
            ->where('status', '!=', 'Done')
            ->whereDate('due_date', '>=', now()->toDateString())
            ->orderBy('due_date')
            ->take($limit)
            ->get()
            ->map(fn ($f) => [
                'id' => $f->customer_id,
                'name' => $f->customer->full_name ?? '—',
                'note' => $f->note,
                'date' => $f->due_date->format('M j, Y'),
            ])
            ->values()
            ->all();
    }
}
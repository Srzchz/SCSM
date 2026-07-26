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
<<<<<<< HEAD
=======

    /**
     * Single source of truth for the "Recent Activities" widget.
     * Most recently logged activity first.
     */
    public static function recentActivities(int $limit = 5): array
    {
        return Activity::latest()
            ->take($limit)
            ->get()
            ->map(fn ($a) => [
                'title' => $a->title,
                'note' => $a->note,
                'time' => $a->created_at->diffForHumans(),
            ])
            ->values()
            ->all();
    }
>>>>>>> 07bdf13d5d768609bc852180cc69152ffadbf351
}
<?php

namespace App\Support;

use App\Modules\CRM\Models\Activity;
use App\Modules\CRM\Models\FollowUp;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Notifications built from real data: recent activity entries, plus
     * follow-ups that are overdue or due soon (acting as reminders).
     * No "read" state is persisted anywhere yet — see note in topbar.blade.php.
     */
    public static function recent(int $limit = 8): array
    {
        $activityNotifications = Activity::with('customer')
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn ($a) => (object) [
                'id' => 'activity-' . $a->id,
                'icon' => self::iconFor($a->type),
                'title' => $a->title,
                'note' => $a->note ?? ($a->customer->full_name ?? ''),
                'sortTime' => $a->created_at,
                'time' => $a->created_at->diffForHumans(),
            ]);

        $followUpNotifications = FollowUp::with('customer')
            ->where('status', '!=', 'Done')
            ->whereDate('due_date', '<=', now()->addDays(3)->toDateString())
            ->orderBy('due_date')
            ->take($limit)
            ->get()
            ->map(fn ($f) => (object) [
                'id' => 'followup-' . $f->id,
                'icon' => '⏰',
                'title' => 'Follow-up due: ' . ($f->customer->full_name ?? 'Customer'),
                'note' => $f->note,
                'sortTime' => $f->due_date,
                'time' => $f->due_date->isPast() ? 'Overdue' : $f->due_date->diffForHumans(),
            ]);

        return $activityNotifications
            ->concat($followUpNotifications)
            ->sortByDesc('sortTime')
            ->take($limit)
            ->map(fn ($n) => [
                'id' => $n->id,
                'icon' => $n->icon,
                'title' => $n->title,
                'note' => $n->note,
                'time' => $n->time,
                'read' => false,
            ])
            ->values()
            ->all();
    }

    private static function iconFor(string $type): string
    {
        return match ($type) {
            'order' => '🛒',
            'review' => '⭐',
            'support' => '💬',
            'customer' => '👤',
            default => '📌',
        };
    }
}
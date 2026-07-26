<?php

namespace App\Modules\CommunicationLogs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CommunicationLogs\Models\CommunicationLog;
use App\Support\CustomerActivityService;
use App\Support\CustomerInsightService;
use Carbon\Carbon;

class CommunicationLogController extends Controller
{
    public function index()
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();

        $rows = CommunicationLog::selectRaw('log_date, status, COUNT(*) as count')
            ->where('log_date', '>=', $startDate->toDateString())
            ->groupBy('log_date', 'status')
            ->get();

        $days = [];
        $open = [];
        $resolved = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateKey = $date->toDateString();
            $days[] = $date->format('M j');

            $dayRows = $rows->filter(fn ($r) => $r->log_date->toDateString() === $dateKey);
            $open[] = (int) $dayRows->where('status', 'Open')->sum('count');
            $resolved[] = (int) $dayRows->where('status', 'Closed')->sum('count');
        }

        $caseStats = [
            'open' => array_sum($open),
            'resolved' => array_sum($resolved),
        ];

        $logs = CommunicationLog::with('customer')
            ->orderByDesc('ticket_id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($l) => [
                'customer_id' => $l->customer_id,
                'customer' => $l->customer->full_name,
                'ticket_id' => $l->ticket_id,
                'issue' => $l->issue,
                'details' => $l->details,
                'date' => $l->log_date->format('M j, Y'),
                'mode' => $l->mode,
                'status' => $l->status,
            ]);

        $insights = CustomerInsightService::segments();
        $followUps = CustomerActivityService::upcomingFollowUps();

        return view('communication-logs.index', compact('days', 'open', 'resolved', 'caseStats', 'logs', 'insights', 'followUps'));
    }
}

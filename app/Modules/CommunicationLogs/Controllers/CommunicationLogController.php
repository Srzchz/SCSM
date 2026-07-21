<?php

namespace App\Modules\CommunicationLogs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CommunicationLogs\Models\CommunicationLog;

class CommunicationLogController extends Controller
{
    public function index()
    {
        $days = ['Jun 10', 'Jun 11', 'Jun 12', 'Jun 13', 'Jun 14', 'Jun 15', 'Jun 16'];
        $open = [8, 10, 6, 12, 9, 7, 11];
        $resolved = [10, 12, 9, 14, 12, 10, 13];
        $caseStats = ['open' => 67, 'inProgress' => 43, 'resolved' => 69];

        $logs = CommunicationLog::with('customer')
            ->orderByDesc('ticket_id')
            ->get()
            ->map(fn ($l) => [
                'customer_id' => $l->customer_id,
                'customer' => $l->customer->full_name,
                'ticket_id' => $l->ticket_id,
                'issue' => $l->issue,
                'details' => $l->details,
                'date' => $l->log_date->format('M j, Y'),
                'mode' => $l->mode,
                'status' => $l->status,
            ]);

        return view('communication-logs.index', compact('days', 'open', 'resolved', 'caseStats', 'logs'));
    }
}

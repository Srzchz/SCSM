<?php

namespace App\Modules\SalesPerformanceReporting\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\SalesPerformanceReporting\Models\Alert;
use App\Modules\SalesPerformanceReporting\Models\AlertSetting;
use App\Modules\SalesPerformanceReporting\Services\AlertGenerationService;
use App\Modules\SalesPerformanceReporting\Services\PeriodHelper;

class AlertsController extends Controller
{
    public function index(AlertGenerationService $service)
    {
        // Re-evaluate every alert condition against current data. No manual
        // authoring — this is the entire "alerts" feature now.
        $service->generate(PeriodHelper::current());

        $alerts = Alert::orderByDesc('created_at')->get()->map(function (Alert $a) {
            $a->timeAgo = $a->timeAgo();
            $a->iconChar = $a->icon();
            return $a;
        });

        $counts = $alerts->groupBy('category')->map->count();

        return view('sales-performance-reporting.pages.alerts', [
            'active' => 'alerts',
            'alerts' => $alerts,
            'counts' => [
                'critical' => $counts->get('critical', 0),
                'warning'  => $counts->get('warning', 0),
                'positive' => $counts->get('positive', 0),
                'info'     => $counts->get('info', 0),
            ],
            'settings' => AlertSetting::current(),
        ]);
    }

    // The only write action left: dismissing an alert as read. This is
    // user-side state, not alert authoring, so it stays.
    public function markRead(Alert $alert)
    {
        $alert->update(['is_read' => true]);

        return redirect()->route('sales-performance-reporting.alerts');
    }
}

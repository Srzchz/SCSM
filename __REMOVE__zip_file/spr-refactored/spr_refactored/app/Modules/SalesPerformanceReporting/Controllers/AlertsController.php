<?php

namespace App\Modules\SalesPerformanceReporting\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\SalesPerformanceReporting\Models\Alert;
use App\Modules\SalesPerformanceReporting\Models\AlertSetting;
use App\Modules\SalesPerformanceReporting\Requests\AlertRequest;

class AlertsController extends Controller
{
    private const ICONS = [
        'Visayas region below target' => '⚠️',
        'Rep M. Santos at risk'       => '👤',
        'LiteBundle quota shortfall'  => '❗',
        'Q2 close in 12 days'         => '⏰',
        'BasicKit declining'          => '📉',
        'EcoLine X2 outperforming'    => '📈',
        'Luzon exceeding quota'       => '🎯',
        'Forecast upgraded'           => '✨',
        'Q2 mid-quarter report ready' => '📄',
        'Forecast model updated'      => '🔄',
    ];

    public function index()
    {
        $alerts = Alert::orderByDesc('created_at')->get()->map(function ($a) {
            $a->icon = self::ICONS[$a->title] ?? match ($a->category) {
                'critical' => '⚠️', 'warning' => '❗', 'positive' => '📈', default => 'ℹ️',
            };
            $a->timeAgo = $a->timeAgo();
            return $a;
        });

        $counts = $alerts->groupBy('category')->map->count();

        return view('sales-performance-reporting.pages.alerts', [
            'active'    => 'alerts',
            'alerts'    => $alerts,
            'counts' => [
                'critical' => $counts->get('critical', 0),
                'warning'  => $counts->get('warning', 0),
                'positive' => $counts->get('positive', 0),
                'info'     => $counts->get('info', 0),
            ],
            'settings'  => AlertSetting::current(),
        ]);
    }

    // A second, smaller "update a record through the website" example:
    // marking a single alert as read. Route-model binding pulls the exact
    // row, we flip one column, and the redirect shows the unread dot and
    // the sidebar badge count both drop immediately — visible proof the
    // write landed in the database.
    public function markRead(Alert $alert)
    {
        $alert->update(['is_read' => true]);

        return redirect()->route('sales-performance-reporting.alerts');
    }

    // ---------- Full CRUD ----------

    // CREATE
    public function store(AlertRequest $request)
    {
        Alert::create($request->validated() + ['is_read' => false]);

        return redirect()->route('sales-performance-reporting.alerts')->with('success', 'Alert created.');
    }

    // EDIT / UPDATE
    public function update(AlertRequest $request, Alert $alert)
    {
        $alert->update($request->validated());

        return redirect()->route('sales-performance-reporting.alerts')->with('success', 'Alert updated.');
    }

    // DELETE
    public function destroy(Alert $alert)
    {
        $alert->delete();

        return redirect()->route('sales-performance-reporting.alerts')->with('success', 'Alert deleted.');
    }
}

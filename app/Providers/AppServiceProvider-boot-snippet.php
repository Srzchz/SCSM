<?php
/**
 * Add this to the boot() method of app/Providers/AppServiceProvider.php.
 * It runs before every render of
 * resources/views/sales-performance-reporting/layouts/app.blade.php,
 * so the sidebar badge and the Account/Settings overlays have real data
 * on every page without each controller needing to pass it manually.
 *
 * NOTE: User is this module's local copy of a table (`users`) flagged as
 * shared/core in the CHANGE LOG below. Once the canonical User model's
 * location is confirmed across teams, this import should point there
 * instead of at this module's own copy.
 */

use App\Modules\SalesPerformanceReporting\Models\Alert;
use App\Modules\SalesPerformanceReporting\Models\User;
use Illuminate\Support\Facades\View;

public function boot(): void
{
    View::composer('sales-performance-reporting.layouts.app', function ($view) {
        // Swap this for auth()->user() once real login is wired up.
        $currentUser = User::with(['region', 'settings'])
            ->where('role', 'manager')
            ->first() ?? User::with(['region', 'settings'])->first();

        $view->with([
            'alertCount'  => Alert::where('is_read', false)->count(),
            'accountUser' => $currentUser,
            'userSettings' => $currentUser?->settings,
        ]);
    });
}

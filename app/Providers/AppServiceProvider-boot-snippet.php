<?php
/**
 * Add this to the boot() method of app/Providers/AppServiceProvider.php.
 * It runs before every render of
 * resources/views/sales-performance-reporting/layouts/app.blade.php,
 * so the sidebar badge and the Account/Settings overlays have real data
 * on every page without each controller needing to pass it manually.
 *
 * UPDATE: real login is now wired up (see routes/auth.php,
 * AuthController). This previously faked a "logged in as the first
 * manager" user via a class, App\Modules\SalesPerformanceReporting\Models\User,
 * that didn't actually exist anywhere in the codebase — that's fixed below.
 */

use App\Modules\SalesPerformanceReporting\Models\Alert;
use Illuminate\Support\Facades\View;

public function boot(): void
{
    View::composer('sales-performance-reporting.layouts.app', function ($view) {
        $currentUser = auth()->user()?->loadMissing(['region', 'settings']);

        $view->with([
            'alertCount'  => Alert::where('is_read', false)->count(),
            'accountUser' => $currentUser,
            'userSettings' => $currentUser?->settings,
        ]);
    });
}

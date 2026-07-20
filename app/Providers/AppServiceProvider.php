<?php

namespace App\Providers;

use App\Modules\SalesPerformanceReporting\Models\Alert;
use App\Modules\SalesPerformanceReporting\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * NOTE: User is this module's local copy of a table (`users`) flagged as
     * shared/core in the CHANGE LOG. Once the canonical User model's
     * location is confirmed across teams, this import should point there
     * instead of at this module's own copy.
     */
    public function boot(): void
    {
        View::composer('sales-performance-reporting.layouts.app', function ($view) {
            // Swap this for auth()->user() once real login is wired up.
            $currentUser = User::with(['region', 'settings'])
                ->where('role', 'manager')
                ->first() ?? User::with(['region', 'settings'])->first();

            $view->with([
                'alertCount' => Alert::where('is_read', false)->count(),
                'accountUser' => $currentUser,
                'userSettings' => $currentUser?->settings,
            ]);
        });
    }
}

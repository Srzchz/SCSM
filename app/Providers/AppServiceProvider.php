<?php

namespace App\Providers;

use App\Modules\SalesPerformanceReporting\Models\Alert;
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
     */
    public function boot(): void
    {
        View::composer('sales-performance-reporting.layouts.app', function ($view) {
            // Real login is wired up now — this used to fake a "logged in
            // as the first manager" user via App\Modules\SalesPerformanceReporting\Models\User,
            // a class that didn't actually exist anywhere in the codebase.
            $currentUser = auth()->user()?->loadMissing(['region', 'settings']);

            $view->with([
                'alertCount' => Alert::where('is_read', false)->count(),
                'accountUser' => $currentUser,
                'userSettings' => $currentUser?->settings,
            ]);
        });
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\SalesPerformanceReporting\Models\Alert;
use App\Modules\SalesPerformanceReporting\Models\User;
use Illuminate\Support\Facades\View;

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
}

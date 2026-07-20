<?php

namespace App\Providers;

use App\Modules\SalesPerformanceReporting\Models\Alert;
use App\Modules\SalesPerformanceReporting\Models\User as SprUser;
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
        // SPR's sidebar badge / Account / Settings overlays need real data
        // on every page render — see the module's own note on why this
        // still uses its own local User model (App\Modules\SalesPerformanceReporting\Models\User,
        // aliased SprUser here) rather than the canonical App\Models\User:
        // it carries fields (role, region, avatar_initials, plan) that
        // only exist via the SPR-specific columns added onto the shared
        // `users` table. Swap for auth()->user() once real login exists.
        View::composer('sales-performance-reporting.layouts.app', function ($view) {
            $currentUser = SprUser::with(['region', 'settings'])
                ->where('role', 'manager')
                ->first() ?? SprUser::with(['region', 'settings'])->first();

            $view->with([
                'alertCount' => Alert::where('is_read', false)->count(),
                'accountUser' => $currentUser,
                'userSettings' => $currentUser?->settings,
            ]);
        });
    }
}

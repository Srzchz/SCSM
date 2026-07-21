<?php

use App\Modules\SalesPerformanceReporting\Controllers\AlertsController;
use App\Modules\SalesPerformanceReporting\Controllers\DashboardController;
use App\Modules\SalesPerformanceReporting\Controllers\GenerateReportController;
use App\Modules\SalesPerformanceReporting\Controllers\RevenueForecastController;
use App\Modules\SalesPerformanceReporting\Controllers\TargetsController;
use Illuminate\Support\Facades\Route;

/*
 * All routes for the Sales Performance Reporting sub-module live under
 * /sales-performance-reporting/*, with every route name prefixed
 * "sales-performance-reporting." to avoid collisions with the other SCSM
 * sub-modules sharing this monorepo's single route-name registry
 * (Sales Order Management, Customer Relationship Management,
 * After-Sales Support and Customer Service Management).
 */
Route::prefix('sales-performance-reporting')
    ->name('sales-performance-reporting.')
    ->group(function () {
        // This module's own landing redirect at /sales-performance-reporting/
        // — NOT a claim on the site root ("/"), which belongs to the shared
        // app shell in the monorepo.
        Route::get('/', fn () => redirect()->route('sales-performance-reporting.dashboard'))->name('index');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/generate-report', [GenerateReportController::class, 'index'])->name('generate-report');

        Route::get('/revenue-forecast', [RevenueForecastController::class, 'index'])->name('revenue-forecast');
        Route::post('/revenue-forecast', [RevenueForecastController::class, 'update'])->name('revenue-forecast.update');

        Route::get('/targets', [TargetsController::class, 'index'])->name('targets');

        Route::get('/alerts', [AlertsController::class, 'index'])->name('alerts');
        Route::post('/alerts', [AlertsController::class, 'store'])->name('alerts.store');
        Route::put('/alerts/{alert}', [AlertsController::class, 'update'])->name('alerts.update');
        Route::delete('/alerts/{alert}', [AlertsController::class, 'destroy'])->name('alerts.destroy');
        Route::post('/alerts/{alert}/read', [AlertsController::class, 'markRead'])->name('alerts.markRead');
    });

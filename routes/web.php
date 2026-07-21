<?php

use Illuminate\Support\Facades\Route;

use App\Modules\SalesPerformanceReporting\Controllers\AlertsController;
use App\Modules\SalesPerformanceReporting\Controllers\DashboardController;
use App\Modules\SalesPerformanceReporting\Controllers\GenerateReportController;
use App\Modules\SalesPerformanceReporting\Controllers\RevenueForecastController;
use App\Modules\SalesPerformanceReporting\Controllers\TargetsController;


Route::get('/', function () {
    return redirect()->route('dashboard');
});

require __DIR__.'/dashboard.php';
require __DIR__.'/ascm.php';
require __DIR__.'/communication-logs.php';
require __DIR__.'/customer-relationship-management.php';
require __DIR__.'/order-history.php';
require __DIR__.'/purchase-behavior.php';
require __DIR__.'/sales-order-management.php';
require __DIR__.'/sales-performance-reporting.php';

Route::get('/account', fn () => view('coming-soon', ['pageTitle' => 'Account']))->name('account');
Route::get('/after-sales-support', fn () => redirect()->route('ascm.index'))->name('after-sales-support');
Route::get('/sales-order', fn () => redirect()->route('sales-order-management.index'))->name('sales-order');
Route::get('/sales-report', fn () => redirect()->route('sales-performance-reporting.dashboard'))->name('sales-report');



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
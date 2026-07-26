<?php

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

require __DIR__.'/auth.php';
require __DIR__.'/dashboard.php';
require __DIR__.'/ascm.php';
require __DIR__.'/mock-ecommerce.php';
require __DIR__.'/communication-logs.php';
require __DIR__.'/customer-relationship-management.php';
require __DIR__.'/order-history.php';
require __DIR__.'/purchase-behavior.php';
require __DIR__.'/sales-order-management.php';
require __DIR__.'/sales-performance-reporting.php';

Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'show'])->name('account');
    Route::patch('/account', [AccountController::class, 'update'])->name('account.update');
});
Route::get('/after-sales-support', fn () => redirect()->route('ascm.index'))->name('after-sales-support');
Route::get('/sales-order', fn () => redirect()->route('sales-order-management.index'))->name('sales-order');
Route::get('/sales-report', fn () => redirect()->route('sales-performance-reporting.dashboard'))->name('sales-report');

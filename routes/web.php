<?php

use Illuminate\Support\Facades\Route;

//helo helo

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

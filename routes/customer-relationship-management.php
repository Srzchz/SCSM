<?php

use App\Modules\CRM\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer-relationship-management')->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('/customers/{customer}/orders', [CustomerController::class, 'orders'])->name('customers.orders');
    Route::get('/customers/{customer}/communication', [CustomerController::class, 'communication'])->name('customers.communication');
});

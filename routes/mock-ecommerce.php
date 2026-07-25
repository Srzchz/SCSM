<?php

use App\Http\Controllers\Mock\Ecommerce\CatalogMockController;
use App\Http\Controllers\Mock\Ecommerce\CustomerMockController;
use App\Http\Controllers\Mock\Ecommerce\OrderMockController;
use Illuminate\Support\Facades\Route;

Route::prefix('mock/ecommerce')->group(function () {
    Route::get('/customers', [CustomerMockController::class, 'index'])->name('mock.ecommerce.customers');
    Route::get('/customers/{customer}/orders', [CustomerMockController::class, 'orders'])->name('mock.ecommerce.customers.orders');
    Route::post('/customers/{customer}/orders', [CustomerMockController::class, 'placeOrder'])->name('mock.ecommerce.customers.place-order');

    Route::get('/catalog', [CatalogMockController::class, 'index'])->name('mock.ecommerce.catalog');
    Route::get('/orders/{order}', [OrderMockController::class, 'show'])->name('mock.ecommerce.orders.show');
    Route::post('/orders/{order}/request-support', [OrderMockController::class, 'requestSupport'])->name('mock.ecommerce.orders.request-support');
});

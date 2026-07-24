<?php

use App\Http\Controllers\Mock\Ecommerce\CatalogMockController;
use App\Http\Controllers\Mock\Ecommerce\OrderMockController;
use Illuminate\Support\Facades\Route;

Route::prefix('mock/ecommerce')->group(function () {
    Route::get('/catalog', [CatalogMockController::class, 'index'])->name('mock.ecommerce.catalog');
    Route::get('/orders/{order}', [OrderMockController::class, 'show'])->name('mock.ecommerce.orders.show');
    Route::post('/orders/{order}/request-support', [OrderMockController::class, 'requestSupport'])->name('mock.ecommerce.orders.request-support');
});

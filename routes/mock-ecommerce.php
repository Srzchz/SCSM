<?php

use App\Http\Controllers\Mock\Ecommerce\CatalogMockController;
use App\Http\Controllers\Mock\Ecommerce\CustomerMockController;
use App\Http\Controllers\Mock\Ecommerce\HelpDeskMockController;
use Illuminate\Support\Facades\Route;

Route::prefix('mock/ecommerce')->group(function () {
    Route::get('/customers', [CustomerMockController::class, 'index'])->name('mock.ecommerce.customers');
    Route::get('/customers/{customer}', [CustomerMockController::class, 'show'])->name('mock.ecommerce.customers.show');

    Route::get('/customers/{customer}/orders', [CustomerMockController::class, 'orders'])->name('mock.ecommerce.customers.orders');
    Route::post('/customers/{customer}/orders', [CustomerMockController::class, 'placeOrder'])->name('mock.ecommerce.customers.place-order');

    Route::get('/customers/{customer}/help-desk', [HelpDeskMockController::class, 'show'])->name('mock.ecommerce.customers.help-desk');
    Route::post('/customers/{customer}/help-desk/cases', [HelpDeskMockController::class, 'submitCase'])->name('mock.ecommerce.customers.help-desk.cases');
    Route::patch('/customers/{customer}/help-desk/satisfaction', [HelpDeskMockController::class, 'submitSatisfaction'])->name('mock.ecommerce.customers.help-desk.satisfaction');

    Route::get('/catalog', [CatalogMockController::class, 'index'])->name('mock.ecommerce.catalog');
});

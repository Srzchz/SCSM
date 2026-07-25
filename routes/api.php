<?php

use App\Http\Controllers\Mock\Ecommerce\EcommerceReadApiController;
use App\Modules\ASCM\Controllers\CaseController;
use Illuminate\Support\Facades\Route;

// Owned by ASCM
Route::prefix('ascm')->name('api.ascm.')->group(function () {
    Route::post('/cases', [CaseController::class, 'store'])->name('cases.store');
});

// Owned by e-commerce (mock, for now)
Route::prefix('ecommerce')->name('api.ecommerce.')->group(function () {
    Route::get('/orders/{orderNumber}', [EcommerceReadApiController::class, 'showByOrderNumber'])->name('orders.show');
});

<?php

use App\Modules\OrderHistory\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('order-history')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
});

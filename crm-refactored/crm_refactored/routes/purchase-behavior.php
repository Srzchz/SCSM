<?php

use App\Modules\PurchaseBehavior\Controllers\PurchaseBehaviorController;
use Illuminate\Support\Facades\Route;

Route::prefix('purchase-behavior')->group(function () {
    Route::get('/', [PurchaseBehaviorController::class, 'index'])->name('purchase-behavior');
});

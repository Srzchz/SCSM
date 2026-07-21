<?php

use App\Modules\ASCM\Controllers\CaseController;
use App\Modules\ASCM\Controllers\WarrantyController;
use Illuminate\Support\Facades\Route;

// The shell itself now lives at '/' (see web.php) and owns the 'dashboard'
// route name. 'ascm.dashboard' is kept as an alias to the same URI so the
// existing route('ascm.dashboard') calls in cases.blade.php/warranty.blade.php
// (used for the filter forms' action= target) keep working unchanged.
Route::get('/', function () {
    return view('ascm.cases');
})->name('index');

Route::prefix('ascm')->name('ascm.')->group(function () {
    Route::prefix('cases')->name('cases.')->group(function () {
        Route::patch('{case}/status', [CaseController::class, 'updateStatus'])->name('update-status');
        Route::post('{case}/notes', [CaseController::class, 'storeNote'])->name('notes.store');
        Route::patch('{case}/escalate', [CaseController::class, 'escalate'])->name('escalate');
        Route::patch('{case}/close', [CaseController::class, 'close'])->name('close');
    });

    Route::prefix('warranty')->name('warranty.')->group(function () {
        Route::patch('{claim}/decision', [WarrantyController::class, 'updateDecision'])->name('decision');
        Route::post('{claim}/notes', [WarrantyController::class, 'storeNote'])->name('notes.store');
        Route::post('{claim}/repair', [WarrantyController::class, 'storeRepair'])->name('repair.store');
    });
});

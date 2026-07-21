<?php

use App\Http\Controllers\AscmShellController;
use App\Modules\ASCM\Controllers\CaseController;
use App\Modules\ASCM\Controllers\WarrantyController;
use Illuminate\Support\Facades\Route;

Route::get('/ascm/cases', [AscmShellController::class, 'cases'])->name('ascm.cases');
Route::get('/ascm/warranty', [AscmShellController::class, 'warranty'])->name('ascm.warranty');

// Legacy alias: the old single-URL SPA shell used to live at /ascm. Kept as
// a redirect (rather than removed outright) in case anything still links to
// ascm.dashboard directly.
Route::get('/ascm', fn () => redirect()->route('ascm.cases'))->name('ascm.dashboard');

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
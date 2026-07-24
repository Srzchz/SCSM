<?php

use App\Modules\ASCM\Controllers\CaseController;
use Illuminate\Support\Facades\Route;

Route::prefix('ascm')->name('api.ascm.')->group(function () {
    Route::post('/cases', [CaseController::class, 'store'])->name('cases.store');
});

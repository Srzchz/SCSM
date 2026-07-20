<?php

use App\Modules\CommunicationLogs\Controllers\CommunicationLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('communication-logs')->group(function () {
    Route::get('/', [CommunicationLogController::class, 'index'])->name('communication-logs');
});

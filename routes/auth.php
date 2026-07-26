<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
 * Auth belongs to the shared `users` table (see the "SHARED / CORE TABLE"
 * note on its migration), so this lives at the app root alongside
 * routes/web.php rather than under any one sub-module.
 */
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

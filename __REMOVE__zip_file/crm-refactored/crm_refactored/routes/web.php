<?php

use Illuminate\Support\Facades\Route;

// The routes below are placeholders for other SCSM submodules that are not
// yet implemented in this codebase (Sales Order, After-Sales Support,
// Sales Report), plus a core Account route. They are intentionally left
// out of the routes/{module-slug}.php split above since there is no
// corresponding controller/module here to own them yet — move each into
// its own routes/{module-slug}.php as soon as that submodule is built out.
Route::get('/sales-order', fn () => view('coming-soon', ['pageTitle' => 'Sales Order']))->name('sales-order');
Route::get('/after-sales-support', fn () => view('coming-soon', ['pageTitle' => 'After-Sales Support']))->name('after-sales-support');
Route::get('/sales-report', fn () => view('coming-soon', ['pageTitle' => 'Sales Report']))->name('sales-report');
Route::get('/account', fn () => view('coming-soon', ['pageTitle' => 'Account']))->name('account');

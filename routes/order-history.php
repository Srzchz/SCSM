<?php

use Illuminate\Support\Facades\Route;

// Order History has been merged into Sales Order Management, which now
// tracks the full order lifecycle (Pending → Processing → Shipped →
// Delivered, hold/cancel, invoicing, payment status) in one place.
Route::get('/order-history/orders', function () {
    return redirect()->route('sales-order-management.index', ['tab' => 'orders']);
})->name('orders.index');
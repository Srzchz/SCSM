<?php

use App\Modules\SalesOrderManagement\Controllers\SalesOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('sales-order-management')->group(function () {

    // Module landing page (session/CSRF — same as the original web.php route)
    Route::middleware('web')->get('/', function () {
        return view('sales-order-management.index');
    })->name('sales-order-management.index');

    // Module API (stateless JSON — same as the original routes/api.php group)
    Route::prefix('api')->middleware('api')->group(function () {
        Route::get('/bootstrap', [SalesOrderController::class, 'bootstrap']);

        // Sales Quotations
        Route::post('/quotations', [SalesOrderController::class, 'storeQuotation']);
        Route::patch('/quotations/{quotation}/send', [SalesOrderController::class, 'sendQuotation']);
        Route::patch('/quotations/{quotation}/reject', [SalesOrderController::class, 'rejectQuotation']);
        Route::patch('/quotations/{quotation}/accept', [SalesOrderController::class, 'acceptQuotation']);

        // Sales Orders
        Route::patch('/orders/{order}/advance', [SalesOrderController::class, 'advanceOrder']);
        Route::patch('/orders/{order}/cancel', [SalesOrderController::class, 'cancelOrder']);
        Route::patch('/orders/{order}/hold', [SalesOrderController::class, 'holdOrder']);
        Route::patch('/orders/{order}/resume', [SalesOrderController::class, 'resumeOrder']);
        Route::post('/orders/{order}/generate-invoice', [SalesOrderController::class, 'generateInvoice']);

        // Invoices
        Route::patch('/invoices/{invoice}/pay', [SalesOrderController::class, 'payInvoice']);

        // Pricing Rules
        Route::post('/pricing-rules', [SalesOrderController::class, 'storePricingRule']);
        Route::patch('/pricing-rules/{pricingRule}/toggle', [SalesOrderController::class, 'togglePricingRule']);
        Route::delete('/pricing-rules/{pricingRule}', [SalesOrderController::class, 'deletePricingRule']);

        // Tax Regions are owned by the Finance module; SOM only reads them via
        // the tax_region_id foreign key on quotations/orders (see TaxRegion model).
    });
});

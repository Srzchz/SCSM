<?php

namespace App\Modules\ASCM\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ASCM\Models\WarrantyRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Inbound integration point: e-commerce calls this after a successful order
 * to register warranty coverage — same pattern as CaseController::store().
 * No per-product warranty term exists anywhere in the schema, so
 * coverage_months defaults to 12 unless the caller specifies otherwise.
 */
class WarrantyRegistrationApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => 'required|integer',
            'order_id' => 'required|integer',
            'order_item_id' => 'nullable|integer',
            'product_id' => 'required|integer',
            'coverage_months' => 'nullable|integer|min:1',
        ]);

        $months = $data['coverage_months'] ?? 12;
        $start = now();

        $registration = WarrantyRegistration::create([
            'customer_id' => $data['customer_id'],
            'order_id' => $data['order_id'],
            'order_item_id' => $data['order_item_id'] ?? null,
            'product_id' => $data['product_id'],
            'warranty_type' => 'standard',
            'coverage_start' => $start,
            'coverage_end' => $start->copy()->addMonths($months),
            'coverage_status' => 'eligible',
        ]);

        return response()->json([
            'warranty_registration_id' => $registration->id,
            'coverage_start' => $registration->coverage_start->toDateString(),
            'coverage_end' => $registration->coverage_end->toDateString(),
        ], 201);
    }
}

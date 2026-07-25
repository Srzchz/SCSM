<?php

namespace App\Modules\ASCM\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\ASCM\Models\CaseNote;
use App\Modules\ASCM\Models\CaseStatusHistory;
use App\Modules\ASCM\Models\SupportCase;
use App\Modules\ASCM\Models\WarrantyClaim;
use App\Modules\ASCM\Models\WarrantyRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CaseController extends Controller
{
    /**
     * Inbound integration point: any module (or, for now, the mock
     * e-commerce trigger) POSTs here to open a case against an existing
     * order. Takes only order_number + case details. customer_id,
     * order_id, order_item_id, and product_id are NOT trusted from the
     * request — they're resolved by calling e-commerce's own read API,
     * since those tables belong to e-commerce, not ASCM. This holds even
     * though both modules currently share one database: ASCM never reads
     * customers/orders/order_items directly, only through the API.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_number' => 'required|string',
            'category' => 'required|string|max:100',
            'priority' => 'nullable|in:low,medium,high,critical',
            'issue_description' => 'required|string|max:2000',
            'estimated_amount' => 'nullable|numeric|min:0',
            'source_module' => 'nullable|string|max:50',
        ]);

        $orderLookup = Http::get(url("/api/ecommerce/orders/{$data['order_number']}"));

        if ($orderLookup->failed() || ! $orderLookup->json('found')) {
            return response()->json([
                'message' => 'order_number not found via e-commerce API.',
            ], 422);
        }

        $order = $orderLookup->json();

        $case = SupportCase::create([
            'customer_id' => $order['customer_id'],
            'order_id' => $order['order_id'],
            'order_item_id' => $order['order_item_id'],
            'product_id' => $order['product_id'],
            'category' => $data['category'],
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'pending',
        ]);

        $warrantyClaim = null;

        // "Warranty" is a case category, but a real warranty claim is its
        // own record (ascm_warranty_claims) linked back to this case and
        // to an existing coverage registration — it doesn't exist just
        // because someone typed "Warranty" in a dropdown. Only create one
        // if there's actually an eligible registration to claim against.
        if (strcasecmp($data['category'], 'Warranty') === 0) {
            $registration = WarrantyRegistration::where('customer_id', $order['customer_id'])
                ->where('product_id', $order['product_id'])
                ->where('coverage_status', 'eligible')
                ->latest('coverage_start')
                ->first();

            if ($registration) {
                $warrantyClaim = WarrantyClaim::create([
                    'warranty_registration_id' => $registration->id,
                    'customer_id' => $order['customer_id'],
                    'case_id' => $case->id,
                    'issue_description' => $data['issue_description'],
                    'estimated_amount' => $data['estimated_amount'] ?? null,
                    'status' => 'submitted',
                ]);
            }
        }

        CaseNote::create([
            'case_id' => $case->id,
            'entry_type' => 'customer_note',
            'visibility' => 'customer_visible',
            'title' => 'Support request via ' . ($data['source_module'] ?? 'external module'),
            'body' => $data['issue_description'],
        ]);

        return response()->json([
            'case_id' => $case->id,
            'case_number' => $case->case_number,
            'status' => $case->status,
            'warranty_claim' => $warrantyClaim ? [
                'claim_id' => $warrantyClaim->id,
                'claim_number' => $warrantyClaim->claim_number,
                'status' => $warrantyClaim->status,
            ] : null,
        ], 201);
    }

    /**
     * Lets the mock help desk (or any module) list a customer's cases —
     * needed so satisfaction can be recorded against an existing case
     * without ASCM's cases table being queried directly from outside.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|integer',
        ]);

        $cases = SupportCase::where('customer_id', $request->query('customer_id'))
            ->orderByDesc('created_at')
            ->get(['id', 'case_number', 'category', 'priority', 'status', 'satisfaction_rating', 'created_at']);

        return response()->json(['cases' => $cases]);
    }

    /**
     * All four actions below redirect back to the dashboard route with
     * ?section=cases so the page lands back on the Cases tab instead of
     * defaulting to Overview. There's no auth system wired up yet, so
     * author_id/changed_by are left null (nullable on every table) rather
     * than faked.
     */
    public function updateStatus(Request $request, SupportCase $case): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,open,resolved,closed',
        ]);

        $from = $case->status;
        $to = $data['status'];

        if ($from !== $to) {
            $case->status = $to;
            if ($to === 'resolved' && ! $case->resolved_at) {
                $case->resolved_at = now();
            }
            if ($to === 'closed') {
                $case->closed_at = now();
            }
            $case->save();

            CaseStatusHistory::create([
                'case_id' => $case->id,
                'from_status' => $from,
                'to_status' => $to,
                'changed_by' => auth()->id(),
            ]);
        }

        return $this->backToCases("Case {$case->case_number} updated to " . ucfirst($to) . '.');
    }

    public function storeNote(Request $request, SupportCase $case): RedirectResponse
    {
        $data = $request->validate([
            'body' => 'required|string|max:2000',
            'visibility' => 'nullable|in:internal,customer_visible',
        ]);

        CaseNote::create([
            'case_id' => $case->id,
            'author_id' => auth()->id(),
            'entry_type' => 'internal_note',
            'visibility' => $data['visibility'] ?? 'internal',
            'title' => auth()->check() ? auth()->user()->name : null,
            'body' => $data['body'],
        ]);

        return $this->backToCases("Note added to {$case->case_number}.");
    }

    public function escalate(SupportCase $case): RedirectResponse
    {
        $case->update(['priority' => 'critical']);

        CaseNote::create([
            'case_id' => $case->id,
            'author_id' => auth()->id(),
            'entry_type' => 'system',
            'visibility' => 'internal',
            'title' => 'Escalated',
            'body' => 'Case escalated to L2 support and priority raised to Critical.',
        ]);

        return $this->backToCases("Case {$case->case_number} escalated.");
    }

    public function close(SupportCase $case): RedirectResponse
    {
        $from = $case->status;

        $case->update(['status' => 'closed', 'closed_at' => now()]);

        CaseStatusHistory::create([
            'case_id' => $case->id,
            'from_status' => $from,
            'to_status' => 'closed',
            'changed_by' => auth()->id(),
            'note' => 'Closed from the case list.',
        ]);

        return $this->backToCases("Case {$case->case_number} closed.");
    }

    /**
     * Records the c. item from the spec ("...and satisfaction levels") —
     * only allowed once the case is actually resolved or closed, since
     * rating an open case doesn't mean anything yet.
     */
    public function recordSatisfaction(Request $request, SupportCase $case): JsonResponse
    {
        if (! in_array($case->status, ['resolved', 'closed'], true)) {
            return response()->json([
                'message' => 'Case must be resolved or closed before recording satisfaction.',
            ], 422);
        }

        $data = $request->validate([
            'satisfaction_rating' => 'required|integer|min:1|max:5',
            'satisfaction_feedback' => 'nullable|string|max:1000',
        ]);

        $case->update([
            'satisfaction_rating' => $data['satisfaction_rating'],
            'satisfaction_feedback' => $data['satisfaction_feedback'] ?? null,
            'satisfaction_recorded_at' => now(),
        ]);

        return response()->json([
            'case_id' => $case->id,
            'case_number' => $case->case_number,
            'satisfaction_rating' => $case->satisfaction_rating,
        ]);
    }

    private function backToCases(string $message): RedirectResponse
    {
        $params = array_filter(
            request()->only(['cases_page', 'cases_status', 'cases_priority', 'cases_from', 'cases_to', 'cases_customer']),
            fn ($v) => $v !== null && $v !== ''
        );

        $params['section'] = 'cases';

        $url = route('dashboard', $params) . '#cases';

        return redirect($url)->with('status', $message);
    }
}

<?php

namespace App\Modules\ASCM\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Order;
use App\Models\OrderItem;
use App\Modules\ASCM\Models\CaseNote;
use App\Modules\ASCM\Models\CaseStatusHistory;
use App\Modules\ASCM\Models\SupportCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    /**
     * Inbound integration point: any module (or, for now, the mock
     * e-commerce trigger) POSTs here to open a case against an existing
     * order. Deliberately takes only order_number + case details — the
     * customer, product, and order_item are resolved server-side from the
     * order itself, since that's the source of truth already sitting in
     * the shared `orders` table. Keeps the payload small and impossible
     * to spoof with mismatched customer/product data.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_number' => 'required|string|exists:orders,order_number',
            'category' => 'required|string|max:100',
            'priority' => 'nullable|in:low,medium,high,critical',
            'issue_description' => 'required|string|max:2000',
            'source_module' => 'nullable|string|max:50',
        ]);

        $order = Order::where('order_number', $data['order_number'])->firstOrFail();
        $orderItem = OrderItem::where('order_id', $order->order_id)->first();

        $case = SupportCase::create([
            'customer_id' => $order->customer_id,
            'order_id' => $order->order_id,
            'order_item_id' => $orderItem?->id,
            'product_id' => $orderItem?->product_id,
            'category' => $data['category'],
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'pending',
        ]);

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
        ], 201);
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

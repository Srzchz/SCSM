<?php

namespace App\Modules\ASCM\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\ASCM\Models\WarrantyClaim;
use App\Modules\ASCM\Models\WarrantyClaimNote;
use App\Modules\ASCM\Models\WarrantyRepair;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    /**
     * Mirrors CaseController: every action redirects back to the dashboard
     * route with ?section=warranty (and the page you were on, if any) so
     * the page lands back on the Warranty tab instead of defaulting to
     * Overview. There's no auth system wired up yet, so author_id /
     * decision_by are left null (nullable on every table) rather than
     * faked.
     */
    public function updateDecision(Request $request, WarrantyClaim $claim): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:submitted,under_review,approved,rejected',
            'approved_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:2000',
        ]);

        $from = $claim->status;
        $to = $data['status'];

        $claim->status = $to;
        $claim->decision_by = auth()->id();
        $claim->decision_at = now();
        if (array_key_exists('approved_amount', $data) && $data['approved_amount'] !== null) {
            $claim->approved_amount = $data['approved_amount'];
        }
        $claim->save();

        WarrantyClaimNote::create([
            'warranty_claim_id' => $claim->id,
            'author_id' => auth()->id(),
            'note_type' => 'decision',
            'body' => $data['note'] ?? ('Status changed from ' . ucfirst($from) . ' to ' . ucfirst($to) . '.'),
        ]);

        return $this->backToWarranty($request, "Claim {$claim->claim_number} updated to " . ucfirst(str_replace('_', ' ', $to)) . '.');
    }

    public function storeNote(Request $request, WarrantyClaim $claim): RedirectResponse
    {
        $data = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        WarrantyClaimNote::create([
            'warranty_claim_id' => $claim->id,
            'author_id' => auth()->id(),
            'note_type' => 'internal',
            'body' => $data['body'],
        ]);

        return $this->backToWarranty($request, "Note added to {$claim->claim_number}.");
    }

    public function storeRepair(Request $request, WarrantyClaim $claim): RedirectResponse
    {
        WarrantyRepair::create([
            'warranty_claim_id' => $claim->id,
            'status' => 'scheduled',
            'technician_id' => null,
            'scheduled_at' => now(),
        ]);

        return $this->backToWarranty($request, "Repair created for {$claim->claim_number}.");
    }

    private function backToWarranty(Request $request, string $message): RedirectResponse
    {
        // Carry forward whatever filter/page query params were on the
        // request that triggered this action (forwarded from the row's
        // decision/repair form action URLs in warranty.blade.php), so
        // acting on a filtered/paginated view doesn't reset it.
        $params = array_filter(
            $request->only(['warranty_page', 'warranty_type', 'warranty_coverage', 'warranty_claim_status', 'warranty_asset', 'warranty_customer']),
            fn ($v) => $v !== null && $v !== ''
        );

        $params['section'] = 'warranty';

        // See the matching comment in CaseController::backToCases — the
        // SPA shell tracks the active tab via the URL hash, which a plain
        // redirect() drops, so we add it back manually.
        $url = route('dashboard', $params) . '#warranty';

        return redirect($url)->with('status', $message);
    }
}

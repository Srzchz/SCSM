<?php

namespace App\Modules\ASCM\Controllers;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Modules\ASCM\Models\CaseNote;
use App\Modules\ASCM\Models\WarrantyClaim;
use App\Modules\ASCM\Models\WarrantyClaimNote;
use App\Modules\ASCM\Models\WarrantyRepair;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    /**
     * Mirrors CaseController: every action redirects back to the real
     * warranty list at ascm.warranty (and the page you were on, if any).
     * author_id/decision_by come from auth()->id(), which is null for a
     * guest request (nullable on every table) rather than faked.
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

    /**
     * Reassigns who's currently working the claim. Mirrors
     * CaseController::assign, and cascades the other direction: a claim
     * belongs to at most one case (case_id is nullable, singular), so if
     * this claim is linked to a case, that case's assigned_to is synced
     * to match -- whoever's now handling the claim is treated as now
     * handling the case it came from too.
     */
    public function assign(Request $request, WarrantyClaim $claim): RedirectResponse
    {
        $data = $request->validate([
            'assigned_to' => 'nullable|integer|exists:users,id',
        ]);

        $from = $claim->assigned_to;
        $to = $data['assigned_to'] ?? null;

        if ($from == $to) {
            return $this->backToWarranty($request, "Claim {$claim->claim_number} is already assigned as requested.");
        }

        $claim->assigned_to = $to;
        $claim->save();

        $toName = $to ? (User::find($to)?->name ?? 'someone') : null;

        WarrantyClaimNote::create([
            'warranty_claim_id' => $claim->id,
            'author_id' => auth()->id(),
            'note_type' => 'assignment',
            'body' => $toName ? "Claim assigned to {$toName}." : 'Claim unassigned.',
        ]);

        if ($claim->case_id) {
            $case = $claim->case;
            $case->assigned_to = $to;
            $case->save();

            CaseNote::create([
                'case_id' => $case->id,
                'author_id' => auth()->id(),
                'entry_type' => 'assignment',
                'visibility' => 'internal',
                'title' => 'Assignment updated',
                'body' => $toName
                    ? "Assignment synced from linked claim {$claim->claim_number}: assigned to {$toName}."
                    : "Assignment synced from linked claim {$claim->claim_number}: unassigned.",
            ]);
        }

        return $this->backToWarranty($request, $toName ? "Claim {$claim->claim_number} assigned to {$toName}." : "Claim {$claim->claim_number} unassigned.");
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

        return redirect()->route('ascm.warranty', $params)->with('status', $message);
    }
}

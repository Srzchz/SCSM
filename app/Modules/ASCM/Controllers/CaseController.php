<?php

namespace App\Modules\ASCM\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\ASCM\Models\CaseNote;
use App\Modules\ASCM\Models\CaseStatusHistory;
use App\Modules\ASCM\Models\SupportCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    /**
     * All four actions below redirect back to the Cases page (ascm.cases)
     * so acting on a row lands you back where you were. There's no auth
     * system wired up yet, so author_id/changed_by are left null (nullable
     * on every table) rather than faked.
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
        // Carry forward whatever filter/page query params were on the
        // request that triggered this action (they're forwarded from the
        // row's escalate/close form action URLs in cases.blade.php), so
        // acting on a filtered/paginated view doesn't reset it.
        $params = array_filter(
            request()->only(['cases_page', 'cases_status', 'cases_priority', 'cases_from', 'cases_to', 'cases_customer']),
            fn ($v) => $v !== null && $v !== ''
        );

        return redirect()->route('ascm.cases', $params)->with('status', $message);
    }
}

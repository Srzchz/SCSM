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
     * Renders the Cases page for the ASCM module.
     *
     * Ported from the legacy App\Http\Controllers\DashboardController's
     * loadCases(), which used to feed this same view (resources/views/
     * ascm/cases.blade.php) as one section of the old single-page
     * dashboard. That controller is no longer routed to from here, so
     * this module now owns its own copy of that query/shaping logic.
     *
     * NOTE: cases.blade.php still assumes it's embedded inside the old
     * SPA shell (spa.blade.php) — its filter form and "Clear" link
     * still submit to route('dashboard')?section=cases#cases rather
     * than back to this route. That's a separate, more visible change
     * (changes user-facing URLs/behavior) left out of this fix pending
     * a decision on whether to fully decouple the view from the SPA
     * shell. Right now those links will round-trip through the Curema
     * dashboard rather than back to /ascm.
     */
    public function index(Request $request)
    {
        [$cases, $caseStats, $caseDetails, $caseFilters] = $this->loadCases($request);

        return view('ascm.cases', compact('cases', 'caseStats', 'caseDetails', 'caseFilters'));
    }

    /**
     * All four actions below redirect back to this module's index route
     * with ?section=cases preserved for the view's own use, rather than
     * to the old SPA dashboard route. There's no auth system wired up
     * yet, so author_id/changed_by are left null (nullable on every
     * table) rather than faked.
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

        // This module's own index route, not the old SPA dashboard.
        // (cases.blade.php's own filter form/"Clear" link still target
        // route('dashboard') — see the note on index() above — so this
        // redirect and those links currently point at two different
        // places. Worth reconciling once that's decided.)
        return redirect()->route('ascm.index', $params)->with('status', $message);
    }

    /**
     * Stats are computed against the full (unfiltered) table so the
     * "Open / Pending / Resolved" counters stay a stable overall
     * reference no matter what filter or page you're currently looking
     * at. Ported as-is from the legacy DashboardController.
     */
    private function loadCases(Request $request): array
    {
        $filters = [
            'status' => (string) $request->query('cases_status', 'All'),
            'priority' => (string) $request->query('cases_priority', 'All'),
            'from' => (string) $request->query('cases_from', ''),
            'to' => (string) $request->query('cases_to', ''),
            'customer' => (string) $request->query('cases_customer', ''),
        ];

        $caseStats = [
            'open' => SupportCase::where('status', 'open')->count(),
            'pending' => SupportCase::where('status', 'pending')->count(),
            'resolved' => SupportCase::where('status', 'resolved')->count(),
        ];

        $query = SupportCase::query();

        if ($filters['status'] !== '' && strtolower($filters['status']) !== 'all') {
            $query->where('status', strtolower($filters['status']));
        }

        if ($filters['priority'] !== '' && strtolower($filters['priority']) !== 'all') {
            $query->where('priority', strtolower($filters['priority']));
        }

        if ($filters['from'] !== '') {
            $query->whereDate('sla_due_at', '>=', $filters['from']);
        }

        if ($filters['to'] !== '') {
            $query->whereDate('sla_due_at', '<=', $filters['to']);
        }

        if ($filters['customer'] !== '') {
            $needle = $filters['customer'];
            $query->whereHas('customer', function ($q) use ($needle) {
                $q->where('name', 'like', "%{$needle}%")
                    ->orWhere('email', 'like', "%{$needle}%")
                    ->orWhere('phone', 'like', "%{$needle}%");
            });
        }

        $cases = $query->with([
            'customer',
            'order',
            'product',
            'orderItem.product',
            'assignee',
            'notes' => fn ($q) => $q->latest(),
            'notes.author',
            'attachments' => fn ($q) => $q->latest(),
            'attachments.uploadedBy',
            'statusHistory' => fn ($q) => $q->latest(),
            'statusHistory.changedBy',
        ])
            ->latest()
            ->paginate(10, ['*'], 'cases_page')
            // Every page link (and the redirect URLs built above) needs
            // to carry whatever filters are active, or the next page
            // load loses them. ->fragment() keeps the #cases hash that
            // cases.blade.php still expects (see the note on index()).
            ->appends(array_filter($this->prefixKeys($filters, 'cases_'), fn ($v) => $v !== null && $v !== ''))
            ->fragment('cases');

        // Pre-shaped per-case timeline/attachments/history so the Blade
        // view can hand it straight to the off-canvas panel as one JSON
        // blob per row, instead of running relationship queries inline
        // in the template. Only shaped for the current page's rows.
        $caseDetails = collect($cases->items())->mapWithKeys(function (SupportCase $case) {
            return [$case->id => [
                'notes' => $case->notes->map(fn ($note) => [
                    'title' => $note->title ?: ucfirst(str_replace('_', ' ', $note->entry_type)),
                    'meta' => $note->created_at->diffForHumans() . ($note->author ? ' • ' . $note->author->name : ''),
                    'text' => $note->body,
                    'visibility' => $note->visibility,
                ])->values(),
                'attachments' => $case->attachments->map(fn ($attachment) => [
                    'title' => $attachment->file_name,
                    'meta' => ($attachment->file_size ? number_format($attachment->file_size / 1024, 1) . ' KB • ' : '')
                        . $attachment->created_at->diffForHumans(),
                    'text' => null,
                ])->values(),
                'history' => $case->statusHistory->map(fn ($entry) => [
                    'title' => ($entry->from_status ? ucfirst($entry->from_status) . ' → ' : '') . ucfirst($entry->to_status),
                    'meta' => $entry->created_at->diffForHumans() . ($entry->changedBy ? ' • ' . $entry->changedBy->name : ''),
                    'text' => $entry->note,
                ])->values(),
            ]];
        });

        return [$cases, $caseStats, $caseDetails, $filters];
    }

    /**
     * ['status' => 'Open', ...] -> ['cases_status' => 'Open', ...], so the
     * filter array (used to repopulate the form inputs) can double as the
     * ?cases_xxx=... query params appended to every pagination link.
     */
    private function prefixKeys(array $filters, string $prefix): array
    {
        $prefixed = [];
        foreach ($filters as $key => $value) {
            $prefixed[$prefix . $key] = $value;
        }

        return $prefixed;
    }
}

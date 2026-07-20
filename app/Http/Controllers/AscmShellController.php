<?php

namespace App\Http\Controllers;

use App\Modules\ASCM\Models\SupportCase;
use App\Modules\ASCM\Models\WarrantyClaim;
use Illuminate\Http\Request;

class AscmShellController extends Controller
{
    /**
     * Cases and Warranty each now have their own dedicated route/view and
     * render inside the shared CRM shell (crm.layouts.app) instead of the
     * old single-URL SPA shell (spa.blade.php + app.js toggling [hidden]
     * sections). The Overview/Sales Order/Customer Relation/Sales
     * Report/Account/Settings placeholder sections that used to live
     * alongside Cases/Warranty in that shell are gone — those are either
     * dead demo content or already covered by real CRM/SOM/SPR pages
     * reachable from the main sidebar.
     */
    public function cases(Request $request)
    {
        [$cases, $caseStats, $caseDetails, $caseFilters] = $this->loadCases($request);

        return view('ascm.cases', compact('cases', 'caseStats', 'caseDetails', 'caseFilters'))
            ->with('active', 'Cases');
    }

    public function warranty(Request $request)
    {
        [$warrantyClaims, $warrantyStats, $warrantyDetails, $warrantyFilters] = $this->loadWarrantyClaims($request);

        return view('ascm.warranty', compact('warrantyClaims', 'warrantyStats', 'warrantyDetails', 'warrantyFilters'))
            ->with('active', 'Warranty');
    }

    /**
     * Stats are computed against the full (unfiltered) table so the
     * "Open / Pending / Resolved" counters stay a stable overall reference
     * no matter what filter or page you're currently looking at.
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
            // Every page link (and the redirect URLs built in CaseController)
            // needs to carry whatever filters are active, or the next page
            // load loses them.
            ->appends(array_filter($this->prefixKeys($filters, 'cases_'), fn ($v) => $v !== null && $v !== ''));

        // Pre-shaped per-case timeline/attachments/history so the Blade
        // view can hand it straight to the off-canvas panel as one JSON
        // blob per row, instead of running relationship queries inline in
        // the template. Only shaped for the current page's rows.
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

    private function loadWarrantyClaims(Request $request): array
    {
        $filters = [
            'type' => (string) $request->query('warranty_type', 'All'),
            'coverage' => (string) $request->query('warranty_coverage', 'All'),
            'claim_status' => (string) $request->query('warranty_claim_status', 'All'),
            'asset' => (string) $request->query('warranty_asset', ''),
            'customer' => (string) $request->query('warranty_customer', ''),
        ];

        $warrantyStats = [
            'open' => WarrantyClaim::whereIn('status', ['submitted', 'under_review'])->count(),
            'approved' => WarrantyClaim::where('status', 'approved')->count(),
            'rejected' => WarrantyClaim::where('status', 'rejected')->count(),
        ];

        $query = WarrantyClaim::query();

        if ($filters['type'] !== '' && strtolower($filters['type']) !== 'all') {
            $type = strtolower($filters['type']);
            $query->whereHas('warrantyRegistration', fn ($q) => $q->where('warranty_type', $type));
        }

        if ($filters['coverage'] !== '' && strtolower($filters['coverage']) !== 'all') {
            $coverage = str_replace(' ', '_', strtolower($filters['coverage']));
            $query->whereHas('warrantyRegistration', fn ($q) => $q->where('coverage_status', $coverage));
        }

        if ($filters['claim_status'] !== '' && strtolower($filters['claim_status']) !== 'all') {
            $status = str_replace(' ', '_', strtolower($filters['claim_status']));
            $query->where('status', $status);
        }

        if ($filters['asset'] !== '') {
            $needle = $filters['asset'];
            $query->whereHas('warrantyRegistration', function ($q) use ($needle) {
                $q->where('serial_number', 'like', "%{$needle}%")
                    ->orWhere('asset_tag', 'like', "%{$needle}%");
            });
        }

        if ($filters['customer'] !== '') {
            $needle = $filters['customer'];
            $query->whereHas('customer', fn ($q) => $q->where('name', 'like', "%{$needle}%"));
        }

        $warrantyClaims = $query->with([
            'customer',
            'warrantyRegistration.product',
            'warrantyRegistration.order',
            'case',
            'decisionBy',
            'notes' => fn ($q) => $q->latest(),
            'notes.author',
            'documents' => fn ($q) => $q->latest(),
            'documents.uploadedBy',
            'repairs' => fn ($q) => $q->latest(),
            'repairs.technician',
        ])
            ->latest()
            ->paginate(10, ['*'], 'warranty_page')
            ->appends(array_filter($this->prefixKeys($filters, 'warranty_'), fn ($v) => $v !== null && $v !== ''));

        $warrantyDetails = collect($warrantyClaims->items())->mapWithKeys(function (WarrantyClaim $claim) {
            $registration = $claim->warrantyRegistration;

            return [$claim->id => [
                'coverage' => [
                    'period' => $registration && $registration->coverage_start && $registration->coverage_end
                        ? ($registration->coverage_start->format('Y-m-d') . ' – ' . $registration->coverage_end->format('Y-m-d'))
                        : '—',
                    'eligibility' => $registration ? ucfirst($registration->coverage_status) : '—',
                    'linked_sales' => $registration?->order?->order_number ?? '—',
                ],
                'claim' => [
                    'issue' => $claim->issue_description ?: '—',
                    'requested_action' => $claim->requested_action ?: '—',
                    'estimated_amount' => $claim->estimated_amount ? '$' . number_format((float) $claim->estimated_amount, 2) : '—',
                ],
                'notes' => $claim->notes->map(fn ($note) => [
                    'title' => ucfirst(str_replace('_', ' ', $note->note_type ?: 'note')),
                    'meta' => $note->created_at->diffForHumans() . ($note->author ? ' • ' . $note->author->name : ''),
                    'text' => $note->body,
                ])->values(),
                'documents' => $claim->documents->map(fn ($doc) => [
                    'title' => $doc->file_name,
                    'meta' => ($doc->file_size ? number_format($doc->file_size / 1024, 1) . ' KB • ' : '')
                        . $doc->created_at->diffForHumans(),
                    'text' => null,
                ])->values(),
                'repairs' => $claim->repairs->map(fn ($repair) => [
                    'title' => 'Repair ' . ucfirst(str_replace('_', ' ', $repair->status)),
                    'meta' => ($repair->technician ? $repair->technician->name . ' • ' : '')
                        . $repair->created_at->diffForHumans(),
                    'text' => $repair->notes,
                ])->values(),
            ]];
        });

        return [$warrantyClaims, $warrantyStats, $warrantyDetails, $filters];
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

<?php

namespace App\Http\Controllers;

use App\Modules\ASCM\Models\SupportCase;
use App\Modules\ASCM\Models\WarrantyClaim;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Every section in resources/views/sections/*.blade.php is rendered up
     * front into resources/views/spa.blade.php and toggled client-side by
     * app.js (via [hidden]) — there's no per-section route. That means all
     * data every section needs has to be gathered here in one place and
     * passed down together, rather than one controller per page.
     *
     * Cases and Warranty are wired to real data. Overview, Sales Order,
     * Customer Relation, Sales Report, Account, and Settings still render
     * their static placeholder/demo content until the same pattern used
     * here is repeated for them.
     */
    public function index(Request $request)
    {
        // Lets write actions (see CaseController/WarrantyController) redirect
        // back to ?section=cases (or ?section=warranty) so the page reloads
        // onto the tab you were just working in instead of defaulting back
        // to Overview.
        $default = $request->query('section', 'overview');

        $sections = [
            'overview' => 'sections.overview',
            'cases' => 'ascm.cases',
            'warranty' => 'ascm.warranty',
            'sales-order' => 'sections.sales-order',
            'customer-relation' => 'sections.customer-relation',
            'sales-report' => 'sections.sales-report',
            'account' => 'sections.account',
            'settings' => 'sections.settings',
        ];

        [$cases, $caseStats, $caseDetails, $caseFilters] = $this->loadCases($request);
        [$warrantyClaims, $warrantyStats, $warrantyDetails, $warrantyFilters] = $this->loadWarrantyClaims($request);

        return view('spa', compact(
            'sections',
            'default',
            'cases',
            'caseStats',
            'caseDetails',
            'caseFilters',
            'warrantyClaims',
            'warrantyStats',
            'warrantyDetails',
            'warrantyFilters',
        ));
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
            // needs to carry ?section=cases plus whatever filters are active,
            // or the next page load falls back to the Overview tab and loses
            // the filter. ->fragment() also keeps the #cases hash the SPA
            // shell uses to track the active tab.
            ->appends(array_filter(array_merge(['section' => 'cases'], $this->prefixKeys($filters, 'cases_')), fn ($v) => $v !== null && $v !== ''))
            ->fragment('cases');

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
            ->appends(array_filter(array_merge(['section' => 'warranty'], $this->prefixKeys($filters, 'warranty_')), fn ($v) => $v !== null && $v !== ''))
            ->fragment('warranty');

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

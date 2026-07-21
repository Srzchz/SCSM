<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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
     * Cases, Warranty, and now Overview are wired to real data. Sales
     * Order, Customer Relation, Sales Report, Account, and Settings still
     * render their static placeholder/demo content until the same pattern
     * used here is repeated for them.
     */
    public function index(Request $request)
    {
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
        [$ovStats, $ovSegments, $ovCustomers, $ovGrowthLabels, $ovGrowthValues] = $this->loadOverview();

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
            'ovStats',
            'ovSegments',
            'ovCustomers',
            'ovGrowthLabels',
            'ovGrowthValues',
        ));
    }

    /**
     * Real CRM Overview data, replacing the previously hardcoded arrays in
     * sections/overview.blade.php.
     *
     * Definitions used (none of these existed in the codebase before —
     * chosen deliberately, flag for review if the business wants something
     * different):
     *   - CLV = lifetime spend to date (sum of grand_total across a
     *     customer's orders). Matches CustomerController's per-customer
     *     total_spent, so this fixes those pages too (they were reading
     *     the always-empty CustomerInsight.clv column).
     *   - Retention Rate = % of all customers with 2+ orders.
     *   - Customer Growth = new customers per calendar week, last 8 weeks,
     *     grouped by Customer.created_at.
     *   - "At Risk" segment removed — Customer::computeSegment() never
     *     produced it; the old Overview view showed it as a fabricated
     *     demo bucket only.
     *   - Stat-card "% change" subtext: only computed for Total Customers
     *     and Repeat Customers (30-day-vs-prior-30-day counts), since
     *     those have an unambiguous definition. Left blank for CLV and
     *     Retention Rate rather than inventing a comparison basis.
     */
    private function loadOverview(): array
    {
        $customers = Customer::withCount('orders')
            ->withSum('orders', 'grand_total')
            ->withMax('orders', 'created_at')
            ->get();

        $totalCustomers = $customers->count();

        $enriched = $customers->map(function ($c) {
            $totalOrders = $c->orders_count;
            $totalSpent = (float) ($c->orders_sum_grand_total ?? 0);
            $lastOrderDate = $c->orders_max_created_at ? \Carbon\Carbon::parse($c->orders_max_created_at) : null;

            return (object) [
                'name' => $c->full_name,
                'orders' => $totalOrders,
                'spent' => $totalSpent,
                'lastOrderDate' => $lastOrderDate,
                'segment' => Customer::computeSegment($totalOrders, $totalSpent, $lastOrderDate),
            ];
        });

        $repeatCustomers = $enriched->filter(fn ($c) => $c->orders >= 2);
        $customersWithOrders = $enriched->filter(fn ($c) => $c->orders > 0);
        $avgClv = $customersWithOrders->isNotEmpty() ? $customersWithOrders->avg('spent') : 0;

        $retentionRate = $totalCustomers > 0
            ? round(($repeatCustomers->count() / $totalCustomers) * 100, 1)
            : 0;

        // computeSegment() returns 'New Customer' (singular); the donut
        // legend uses 'New Customers' (plural) — aligned here rather than
        // changing the shared segment method's public return value.
        $segmentLabelMap = ['New Customer' => 'New Customers'];
        $segmentColors = [
            'VIP' => '#AD9EFF',
            'Repeat Buyer' => '#9CFF9F',
            'New Customers' => '#7ED8FF',
            'Inactive' => '#B0B4EC',
        ];
        $segmentCounts = array_fill_keys(array_keys($segmentColors), 0);

        foreach ($enriched as $c) {
            $label = $segmentLabelMap[$c->segment] ?? $c->segment;
            if (array_key_exists($label, $segmentCounts)) {
                $segmentCounts[$label]++;
            }
        }

        $ovSegments = collect($segmentCounts)->map(fn ($count, $label) => [
            'label' => $label,
            'value' => $count,
            'pct' => $totalCustomers > 0 ? round(($count / $totalCustomers) * 100, 1) . '%' : '0%',
            'color' => $segmentColors[$label],
        ])->values()->all();

        $ovCustomers = $enriched->sortByDesc('spent')->take(5)->map(fn ($c) => [
            'name' => $c->name,
            'segment' => $segmentLabelMap[$c->segment] ?? $c->segment,
            'orders' => $c->orders,
            'ltv' => '₱' . number_format($c->spent, 2),
            'last' => $c->lastOrderDate?->format('Y-m-d') ?? '—',
            'status' => $c->segment === 'Inactive' ? 'Inactive' : 'Active',
        ])->values()->all();

        $ovGrowthLabels = [];
        $ovGrowthValues = [];
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = (clone $weekStart)->endOfWeek();
            $ovGrowthLabels[] = $weekStart->format('M j');
            $ovGrowthValues[] = Customer::whereBetween('created_at', [$weekStart, $weekEnd])->count();
        }

        $periodChange = function (int $current, int $previous): string {
            if ($previous === 0) {
                return $current > 0 ? '+100%' : '+0%';
            }
            $pct = (($current - $previous) / $previous) * 100;

            return ($pct >= 0 ? '+' : '') . round($pct, 1) . '%';
        };

        $customersLast30 = Customer::where('created_at', '>=', now()->subDays(30))->count();
        $customersPrev30 = Customer::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count();
        $repeatLast30 = $enriched->filter(fn ($c) => $c->orders >= 2 && $c->lastOrderDate && $c->lastOrderDate->gte(now()->subDays(30)))->count();
        $repeatPrev30 = $enriched->filter(fn ($c) => $c->orders >= 2 && $c->lastOrderDate && $c->lastOrderDate->between(now()->subDays(60), now()->subDays(30)))->count();

        $ovStats = [
            ['label' => 'Total Customers', 'value' => number_format($totalCustomers), 'change' => $periodChange($customersLast30, $customersPrev30), 'tint' => 'tint-purple'],
            ['label' => 'Repeat Customers', 'value' => number_format($repeatCustomers->count()), 'change' => $periodChange($repeatLast30, $repeatPrev30), 'tint' => 'tint-green'],
            ['label' => 'CLV (Avg)', 'value' => '₱' . number_format($avgClv, 2), 'change' => null, 'tint' => 'tint-blue'],
            ['label' => 'Retention Rate', 'value' => $retentionRate . '%', 'change' => null, 'tint' => 'tint-coral'],
        ];

        return [$ovStats, $ovSegments, $ovCustomers, $ovGrowthLabels, $ovGrowthValues];
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
            ->appends(array_filter(array_merge(['section' => 'cases'], $this->prefixKeys($filters, 'cases_')), fn ($v) => $v !== null && $v !== ''))
            ->fragment('cases');

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

    private function prefixKeys(array $filters, string $prefix): array
    {
        $prefixed = [];
        foreach ($filters as $key => $value) {
            $prefixed[$prefix . $key] = $value;
        }

        return $prefixed;
    }
}
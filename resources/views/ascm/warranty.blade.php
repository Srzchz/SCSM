@extends('crm.layouts.app')

@section('title', 'Warranty')

@section('content')
{{--
    Warranty page — data comes from AscmShellController@warranty:
    $warrantyClaims (paginated Eloquent, 10 per page), $warrantyStats
    (counts across the whole table), $warrantyDetails (per-claim
    coverage/notes/documents/repairs for the rows on the current page,
    pre-shaped for the off-canvas panel's JS).

    Decision changes, notes, and repair creation are real form submissions
    — see WarrantyController. Every row's actions are collapsed into a
    single "..." menu, same pattern as Cases.
--}}

@php
    $coveragePill = ['eligible' => 'pill-green', 'expired' => 'pill-red', 'not_eligible' => 'pill-gray'];
    $claimStatusPill = ['submitted' => 'pill-blue', 'under_review' => 'pill-blue', 'approved' => 'pill-green', 'rejected' => 'pill-gray'];

    // Carried into every row's decision/repair form action URL so acting
    // on a row doesn't lose the current filter/page when
    // WarrantyController redirects back (see WarrantyController::backToWarranty).
    $currentWarrantyQuery = array_filter(
        request()->only(['warranty_page', 'warranty_type', 'warranty_coverage', 'warranty_claim_status', 'warranty_asset', 'warranty_customer']),
        fn ($v) => $v !== null && $v !== ''
    );

    $hasActiveWarrantyFilters = collect($warrantyFilters)->contains(fn ($v) => $v !== '' && strtolower((string) $v) !== 'all');

    // The off-canvas panel's decision/note/repair forms have their action
    // set dynamically by JS (shared across every row), so the current
    // filter/page state is handed to the script as a plain query string.
    $currentWarrantyQueryString = http_build_query($currentWarrantyQuery);
@endphp

<div class="warranty-wrapper">
    <div class="page-header">
        <div>
            <h1 class="section-title">Warranty</h1>
            <p class="section-hint">Claims, coverage, and decisions.</p>
        </div>

        <div class="page-header-actions">
            <button type="button" class="btn btn-primary" aria-label="New claim">New Claim</button>
        </div>
    </div>

    @if (session('status'))
        <div class="flash-banner">{{ session('status') }}</div>
    @endif

        <!-- Off-canvas warranty detail panel -->
        <div id="warranty-detail-overlay" class="offcanvas-overlay" hidden></div>
        <aside id="warranty-detail-panel" class="offcanvas-panel" aria-hidden="true">
            <div class="offcanvas-header">
                <div>
                    <div class="detail-case">
                        <div class="detail-case-title">—</div>
                        <div class="detail-case-meta">—</div>
                    </div>
                </div>
                <button id="warranty-detail-close" class="btn btn-ghost" aria-label="Close">Close</button>
            </div>

            <div class="offcanvas-body">
                <form class="detail-actions" id="panel-warranty-decision-form" method="POST" action="#">
                    @csrf
                    @method('PATCH')
                    <div class="field-inline">
                        <label class="filter-label" for="panel-warranty-decision">Decision</label>
                        <select id="panel-warranty-decision" name="status" class="input">
                            <option value="submitted">Submitted</option>
                            <option value="under_review">Under Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Decision</button>
                </form>

                <div class="detail-columns">
                    <div class="mini-card">
                        <div class="mini-title">Coverage Summary</div>
                        <div class="mini-row"><span class="mini-key">Coverage period</span><span class="mini-val" id="panel-warranty-period">—</span></div>
                        <div class="mini-row"><span class="mini-key">Eligibility</span><span class="mini-val" id="panel-warranty-eligibility">—</span></div>
                        <div class="mini-row"><span class="mini-key">Linked sales</span><span class="mini-val" id="panel-warranty-sales">—</span></div>
                    </div>

                    <div class="mini-card">
                        <div class="mini-title">Claim Summary</div>
                        <div class="mini-row"><span class="mini-key">Issue</span><span class="mini-val" id="panel-warranty-issue">—</span></div>
                        <div class="mini-row"><span class="mini-key">Requested action</span><span class="mini-val" id="panel-warranty-action">—</span></div>
                        <div class="mini-row"><span class="mini-key">Estimated amount</span><span class="mini-val" id="panel-warranty-amount">—</span></div>
                    </div>
                </div>

                <div class="tabs" role="tablist" aria-label="Warranty tabs">
                    <button type="button" class="tab tab-active" role="tab" aria-selected="true" data-tab="notes">Decision Notes</button>
                    <button type="button" class="tab" role="tab" aria-selected="false" data-tab="documents">Documents</button>
                    <button type="button" class="tab" role="tab" aria-selected="false" data-tab="repairs">Service Plan</button>
                </div>

                <div data-tab-content="notes" aria-label="Decision notes panel">
                    <div class="timeline" id="panel-warranty-notes"></div>

                    <form class="composer" id="panel-warranty-note-form" method="POST" action="#">
                        @csrf
                        <div class="composer-header">Add a note</div>
                        <textarea name="body" class="textarea" placeholder="Add reasoning for approve/reject, or requested follow-ups..." required></textarea>
                        <div class="composer-actions">
                            <button type="submit" class="btn btn-primary">Post Note</button>
                            <button type="button" class="btn btn-ghost" id="panel-warranty-note-cancel">Cancel</button>
                        </div>
                    </form>
                </div>

                <div data-tab-content="documents" aria-label="Documents panel" hidden>
                    <div class="timeline" id="panel-warranty-documents"></div>
                </div>

                <div data-tab-content="repairs" aria-label="Service plan panel" hidden>
                    <div class="timeline" id="panel-warranty-repairs"></div>

                    <form class="composer" id="panel-warranty-repair-form" method="POST" action="#">
                        @csrf
                        <div class="composer-header">Repairs</div>
                        <button type="submit" class="btn btn-primary">Create Repair</button>
                    </form>
                </div>
            </div>
        </aside>

    <div class="module-grid">
        {{-- Left: claims list / filters --}}
        <section class="module-card" aria-label="Warranty claims list">
            <div class="card-header">
                <h2 class="card-title">Claims & Coverage</h2>
                <span class="card-subtitle">Track eligibility and manage outcomes.</span>
            </div>

            <div class="stats-row" aria-label="Warranty overview">
                <div class="stat">
                    <div class="stat-value">{{ $warrantyStats['open'] }}</div>
                    <div class="stat-label">Open Claims</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $warrantyStats['approved'] }}</div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $warrantyStats['rejected'] }}</div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>

            <form id="warranty-filter-form" class="filters" aria-label="Warranty filters" method="GET" action="{{ route('ascm.warranty') }}">

                <div class="filter filter-select">
                    <label class="filter-label" for="warranty-type">Type</label>
                    <select id="warranty-type" name="warranty_type" class="input">
                        @foreach (['All', 'Standard', 'Extended', 'Commercial'] as $option)
                            <option value="{{ $option }}" @selected(strcasecmp($warrantyFilters['type'], $option) === 0)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter filter-select">
                    <label class="filter-label" for="warranty-coverage">Coverage</label>
                    <select id="warranty-coverage" name="warranty_coverage" class="input">
                        @foreach (['All', 'Eligible', 'Expired', 'Not Eligible'] as $option)
                            <option value="{{ $option }}" @selected(strcasecmp($warrantyFilters['coverage'], $option) === 0)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter filter-select">
                    <label class="filter-label" for="warranty-claim-status">Claim Status</label>
                    <select id="warranty-claim-status" name="warranty_claim_status" class="input">
                        @foreach (['All', 'Submitted', 'Under Review', 'Approved', 'Rejected'] as $option)
                            <option value="{{ $option }}" @selected(strcasecmp($warrantyFilters['claim_status'], $option) === 0)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter filter-grow">
                    <label class="filter-label" for="warranty-asset">Serial / Asset</label>
                    <input id="warranty-asset" name="warranty_asset" type="text" class="input" placeholder="Search by serial, asset tag..." value="{{ $warrantyFilters['asset'] }}" />
                </div>

                <div class="filter filter-grow">
                    <label class="filter-label" for="warranty-customer">Customer</label>
                    <input id="warranty-customer" name="warranty_customer" type="text" class="input" placeholder="Search by customer..." value="{{ $warrantyFilters['customer'] }}" />
                </div>

                <div class="filter filter-buttons">
                    <button type="submit" class="btn btn-ghost btn-compact">Apply</button>
                    @if ($hasActiveWarrantyFilters)
                        <a href="{{ route('ascm.warranty') }}" class="btn btn-ghost btn-compact">Clear</a>
                    @endif
                </div>
            </form>

            <div class="table-wrap" aria-label="Warranty claims table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Claim #</th>
                            <th>Customer</th>
                            <th>Coverage</th>
                            <th>Claim Status</th>
                            <th>Amount</th>
                            <th style="width:56px;text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($warrantyClaims as $claim)
                            @php
                                $registration = $claim->warrantyRegistration;
                                $productName = optional($registration)->product->name ?? null;
                                $serial = $registration->serial_number ?? $registration->asset_tag ?? null;
                                $coverageKey = $registration ? str_replace(' ', '_', strtolower($registration->coverage_status)) : null;
                                $claimMeta = ($claim->customer->name ?? '—') . ($productName ? ' • ' . $productName : '') . ($serial ? ' • ' . $serial : '');
                                // See the matching note in cases.blade.php: the @json Blade
                                // directive splits on every top-level comma, which mangles a
                                // multi-key array literal default like the one below. Encode by
                                // hand with the HEX_* flags instead so it's safe inside a
                                // single-quoted HTML attribute.
                                $claimPayload = json_encode(
                                    $warrantyDetails[$claim->id] ?? ['coverage' => [], 'claim' => [], 'notes' => [], 'documents' => [], 'repairs' => []],
                                    JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG
                                );
                            @endphp
                            <tr>
                                <td><span class="mono">{{ $claim->claim_number }}</span></td>
                                <td>
                                    <div class="cell-primary">{{ $claim->customer->name ?? '—' }}</div>
                                    @if ($productName || $serial)
                                        <div class="cell-sub">{{ $productName ?? '—' }}@if($serial) • {{ $serial }} @endif</div>
                                    @endif
                                </td>
                                <td><span class="pill {{ $coveragePill[$coverageKey] ?? 'pill-gray' }}">{{ $registration ? ucfirst(str_replace('_', ' ', $registration->coverage_status)) : '—' }}</span></td>
                                <td><span class="pill {{ $claimStatusPill[$claim->status] ?? 'pill-gray' }}">{{ ucfirst(str_replace('_', ' ', $claim->status)) }}</span></td>
                                <td>{{ $claim->approved_amount ? '$' . number_format((float) $claim->approved_amount, 2) : ($claim->estimated_amount ? '$' . number_format((float) $claim->estimated_amount, 2) : '—') }}</td>
                                <td>
                                    <div class="action-menu">
                                        <button type="button" class="action-menu-trigger" data-menu-trigger aria-haspopup="true" aria-expanded="false" aria-label="Actions for {{ $claim->claim_number }}">
                                            <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="2.1"/><circle cx="12" cy="12" r="2.1"/><circle cx="19" cy="12" r="2.1"/></svg>
                                        </button>

                                        <div class="action-menu-list" role="menu" hidden>
                                            <button type="button" class="action-menu-item"
                                                    data-action="view-warranty"
                                                    data-claim-pk="{{ $claim->id }}"
                                                    data-claim-id="{{ $claim->claim_number }}"
                                                    data-claim-status="{{ $claim->status }}"
                                                    data-claim-meta="{{ $claimMeta }}"
                                                    data-claim-payload='{!! $claimPayload !!}'>View details</button>

                                            <button type="button" class="action-menu-item"
                                                    data-action="view-warranty"
                                                    data-focus-note="1"
                                                    data-claim-pk="{{ $claim->id }}"
                                                    data-claim-id="{{ $claim->claim_number }}"
                                                    data-claim-status="{{ $claim->status }}"
                                                    data-claim-meta="{{ $claimMeta }}"
                                                    data-claim-payload='{!! $claimPayload !!}'>Add note</button>

                                            @if (! in_array($claim->status, ['approved', 'rejected']))
                                                <form method="POST" action="{{ route('ascm.warranty.decision', array_merge(['claim' => $claim], $currentWarrantyQuery)) }}" onsubmit="return confirm('Approve {{ $claim->claim_number }}?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="action-menu-item">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('ascm.warranty.decision', array_merge(['claim' => $claim], $currentWarrantyQuery)) }}" onsubmit="return confirm('Reject {{ $claim->claim_number }}?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="action-menu-item danger">Reject</button>
                                                </form>
                                            @endif

                                            @if ($claim->status === 'approved')
                                                <form method="POST" action="{{ route('ascm.warranty.repair.store', array_merge(['claim' => $claim], $currentWarrantyQuery)) }}">
                                                    @csrf
                                                    <button type="submit" class="action-menu-item">Create repair</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;color:var(--color-text-muted);padding:28px;">
                                    No warranty claims yet — once your seeder runs, they'll show up here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($warrantyClaims->total() > 0)
                <div class="pagination-bar">
                    <span class="pagination-summary">Showing {{ $warrantyClaims->firstItem() }}–{{ $warrantyClaims->lastItem() }} of {{ $warrantyClaims->total() }}</span>
                    @include('ascm.partials.pagination', ['paginator' => $warrantyClaims])
                </div>
            @endif
        </section>

        {{-- Right detail is shown in an off-canvas panel when a row's View button is clicked. --}}
    </div>
</div>

<style>
    /* These custom properties mirror Curema's Tailwind color tokens
       (see tailwind.config in crm.layouts.app) so this page's hand-rolled
       CSS doesn't depend on ascm's now-unused public/css/app.css. */
    :root{
        --color-bg:#E9EBFC;
        --color-text:#120F34;
        --color-text-muted:#5B5876;
        --color-primary:#120F34;
        --color-primary-light:#CFD2F9;
        --color-indicator-text-green:#00630F;
        --color-indicator-text-blue:#004169;
        --color-indicator-text-red:#8A2A1F;
        --color-indicator-text-yellow:#7A5B12;
    }

    .warranty-wrapper{padding-top:8px;}
    .page-header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid rgba(18,15,52,0.08);}
    .page-header-actions{display:flex;align-items:center;gap:10px;}

    .flash-banner{background:rgba(156,255,159,0.35);color:var(--color-indicator-text-green);border:1px solid rgba(0,99,15,0.15);border-radius:14px;padding:12px 16px;font-weight:700;font-size:0.85rem;margin-bottom:16px;}

    .module-grid{display:grid;grid-template-columns:1.15fr 0.85fr;gap:16px;min-width:0;}
    @media (max-width: 980px){.module-grid{grid-template-columns:1fr;}}

    /* when only one column is present, let it fill the area */
    .module-grid.module-grid--single{grid-template-columns:1fr !important}

    .module-card{background:#ffffff;border:1px solid rgba(18,15,52,0.08);border-radius:18px;padding:18px;box-shadow:0 10px 30px rgba(18,15,52,0.04);min-width:0;overflow:hidden;}
    .card-header{margin-bottom:12px;}
    .card-title{margin:0;font-size:1rem;font-weight:800;}
    .card-subtitle{display:block;margin-top:4px;color:var(--color-text-muted);font-size:0.85rem;}

    .stats-row{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:12px 0 16px;}
    .stat{background:var(--color-bg);border:1px solid rgba(18,15,52,0.06);border-radius:14px;padding:12px 14px;}
    .stat-value{font-weight:800;font-size:1.25rem;}
    .stat-label{color:var(--color-text-muted);font-size:0.85rem;margin-top:4px;}

    /* Compact, single-line filter bar — same pattern as Cases. */
    .filters{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px;margin:10px 0 16px;}
    .filter{display:flex;flex-direction:column;gap:5px;flex:1 1 120px;min-width:104px;}
    .filter-select{flex:1 1 120px;}
    .filter-grow{flex:2 1 180px;}
    .filter-buttons{flex:0 0 auto;flex-direction:row;gap:8px;}
    .btn-compact{padding:8px 14px;font-size:0.8rem;}
    .filter-label{display:block;font-size:0.72rem;color:var(--color-text-muted);margin-bottom:0;white-space:nowrap;}
    .input{width:100%;padding:8px 10px;border-radius:9px;border:1px solid rgba(18,15,52,0.12);background:#fdfdff;font-family:inherit;font-size:0.82rem;}
    @media (max-width: 640px){.filters{flex-direction:column;align-items:stretch;} .filter{flex:1 1 auto;}}

    .table-wrap{background:#fff;border:1px solid rgba(18,15,52,0.06);border-radius:18px;overflow-x:auto;box-shadow:none;max-width:100%;}
    .data-table{width:100%;border-collapse:collapse;min-width:0;}
    .data-table th{font-size:0.72rem;color:var(--color-text-muted);text-align:left;padding:14px 16px;border-bottom:1px solid rgba(18,15,52,0.08);background:transparent;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;}
    .data-table td{padding:14px 16px;border-bottom:1px solid rgba(18,15,52,0.05);vertical-align:middle;font-size:0.9rem;}
    .data-table tbody tr:last-child td{border-bottom:none;}
    .data-table tbody tr:hover{background:rgba(233,235,252,0.45);}
    .mono{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;font-size:0.9rem;}

    .cell-primary{font-weight:700;}
    .cell-sub{color:var(--color-text-muted);font-size:0.78rem;margin-top:2px;}

    /* Action dropdown ("...") menu — same behavior/script as Cases. */
    .action-menu{position:relative;display:inline-flex;justify-content:flex-end;}
    .action-menu-trigger{width:32px;height:32px;flex-shrink:0;border-radius:999px;border:1px solid rgba(18,15,52,0.12);background:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;color:var(--color-text);padding:0;}
    .action-menu-trigger:hover, .action-menu-trigger[aria-expanded="true"]{background:var(--color-bg);}
    .action-menu-trigger svg{width:18px;height:18px;}
    .action-menu-list{display:flex;flex-direction:column;min-width:170px;background:#fff;border:1px solid rgba(18,15,52,0.1);border-radius:14px;box-shadow:0 16px 36px rgba(18,15,52,0.18);padding:6px;z-index:500;}
    .action-menu-list[hidden]{display:none;}
    .action-menu-list form{display:block;width:100%;margin:0;}
    .action-menu-item{all:unset;box-sizing:border-box;display:block;width:100%;padding:9px 12px;border-radius:9px;font-size:0.85rem;font-weight:600;color:var(--color-text);cursor:pointer;font-family:inherit;}
    .action-menu-item:hover{background:var(--color-bg);}
    .action-menu-item.danger{color:var(--color-indicator-text-red);}

    .pagination-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:14px;padding-top:14px;border-top:1px solid rgba(18,15,52,0.06);}
    .pagination-summary{font-size:0.82rem;color:var(--color-text-muted);}

    /* Compact pagination control (see partials/pagination.blade.php) */
    .pager{display:flex;align-items:center;gap:4px;flex-wrap:wrap;}
    .pager-btn{display:inline-flex;align-items:center;justify-content:center;min-width:30px;height:30px;padding:0 8px;border-radius:8px;font-size:0.82rem;font-weight:700;color:var(--color-text);text-decoration:none;border:1px solid transparent;}
    .pager-btn:hover{background:var(--color-bg);}
    .pager-btn-active{background:var(--color-primary, #120F34);color:#fff;}
    .pager-btn-active:hover{background:var(--color-primary, #120F34);}
    .pager-btn-disabled{color:rgba(18,15,52,0.25);cursor:default;}
    .pager-btn-disabled:hover{background:transparent;}
    .pager-ellipsis{padding:0 4px;color:var(--color-text-muted);font-size:0.82rem;}

    .detail-header{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:12px;}
    .detail-case-title{font-size:1.1rem;font-weight:800;}
    .detail-case-meta{margin-top:4px;color:var(--color-text-muted);font-size:0.9rem;}
    .detail-actions{display:flex;align-items:flex-end;gap:10px;min-width:280px;flex-wrap:wrap;}
    .field-inline{display:flex;flex-direction:column;gap:6px;}

    .detail-columns{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:10px 0 14px;}
    @media (max-width: 540px){.detail-columns{grid-template-columns:1fr;}}

    .mini-card{background:var(--color-bg);border:1px solid rgba(18,15,52,0.06);border-radius:14px;padding:12px 14px;}
    .mini-title{font-weight:900;margin-bottom:10px;}
    .mini-row{display:flex;justify-content:space-between;gap:10px;margin:8px 0;color:rgba(18,15,52,0.9);}
    .mini-key{color:var(--color-text-muted);font-size:0.85rem;}
    .mini-val{font-weight:700;font-size:0.9rem;text-align:right;}

    .tabs{display:flex;gap:8px;margin:12px 0;flex-wrap:wrap;}
    .tab{border:none;background:rgba(233,235,252,0.7);border-radius:999px;padding:9px 16px;cursor:pointer;font-weight:700;font-size:0.85rem;color:var(--color-text-muted);}
    .tab-active{background:var(--color-primary-light, #CFD2F9);color:var(--color-text);}

    .timeline{display:flex;flex-direction:column;gap:14px;margin-top:8px;}
    .timeline-item{display:flex;gap:12px;align-items:flex-start;}
    .timeline-dot{width:12px;height:12px;border-radius:50%;background:var(--color-primary);margin-top:6px;flex-shrink:0;}
    .timeline-title{font-weight:800;}
    .timeline-meta{color:var(--color-text-muted);font-size:0.85rem;margin-top:2px;}
    .timeline-text{margin-top:8px;color:rgba(18,15,52,0.85);line-height:1.45;}
    .empty-hint{color:var(--color-text-muted);font-size:0.85rem;margin:4px 0 0;}

    .composer{border-top:1px solid rgba(18,15,52,0.08);margin-top:18px;padding-top:14px;}
    .composer-header{font-weight:900;margin-bottom:10px;}
    .textarea{width:100%;min-height:90px;resize:vertical;border-radius:12px;border:1px solid rgba(18,15,52,0.12);padding:12px;font-family:inherit;font-size:0.9rem;background:#fff;}
    .composer-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:10px;}

    .pill{display:inline-flex;align-items:center;padding:6px 14px;border-radius:999px;font-weight:700;font-size:0.78rem;border:none;}
    .pill-blue{background:rgba(126,216,255,0.35);color:var(--color-indicator-text-blue);}
    .pill-green{background:rgba(156,255,159,0.4);color:var(--color-indicator-text-green);}
    .pill-red{background:rgba(255,154,145,0.4);color:var(--color-indicator-text-red);}
    .pill-yellow{background:rgba(249,223,170,0.5);color:var(--color-indicator-text-yellow);}
    .pill-gray{background:rgba(126,122,154,0.25);color:rgba(90,86,120,0.95);}

    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:999px;padding:10px 20px;font-weight:700;font-size:0.85rem;cursor:pointer;border:1px solid transparent;font-family:inherit;}
    .btn-primary{background:var(--color-primary, #120F34);color:#ffffff;}
    .btn-primary:hover{opacity:0.92;}
    .btn-ghost{background:#ffffff;color:var(--color-text);border-color:rgba(18,15,52,0.14);}
    .btn-ghost:hover{background:var(--color-bg);}
</style>
<script>
    // make module-grid expand when it contains only one module-card
    (function(){
        document.addEventListener('DOMContentLoaded', function(){
            document.querySelectorAll('.module-grid').forEach(function(grid){
                const cards = grid.querySelectorAll('section.module-card');
                if(cards.length <= 1) grid.classList.add('module-grid--single');
            });
        });
    })();

    // Auto-submit the filter form when a select changes, so applying a
    // filter doesn't require an extra click on "Apply". The Serial/Asset
    // and Customer text fields still submit normally (Enter, or the
    // Apply button) rather than firing on every keystroke.
    (function () {
        const form = document.getElementById('warranty-filter-form');
        if (!form) return;
        form.querySelectorAll('select').forEach(function (el) {
            el.addEventListener('change', function () { form.submit(); });
        });
    })();
</script>

<style>
    /* Off-canvas panel styles (matching cases) */
    .offcanvas-overlay{
        position:fixed;inset:0;background:rgba(0,0,0,0.35);z-index:50;opacity:0;transition:opacity .2s ease;pointer-events:none;
    }
    .offcanvas-overlay[hidden]{display:none}
    .offcanvas-overlay.active{opacity:1;pointer-events:auto}

    .offcanvas-panel{
        position:fixed;right:0;top:0;bottom:0;width:420px;max-width:100vw;background:#fff;box-shadow:-20px 0 40px rgba(18,15,52,0.12);z-index:60;transform:translateX(110%);transition:transform .25s ease;display:flex;flex-direction:column;overflow:auto;padding:18px;
    }
    .offcanvas-panel.open{transform:translateX(0)}
    .offcanvas-header{display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid rgba(18,15,52,0.06);padding-bottom:12px}
    .offcanvas-body{padding-top:12px}
    @media (max-width: 700px){.offcanvas-panel{width:100%;}}
</style>

<script>
    (function(){
        const overlay = document.getElementById('warranty-detail-overlay');
        const panel = document.getElementById('warranty-detail-panel');
        const closeBtn = document.getElementById('warranty-detail-close');
        const decisionForm = document.getElementById('panel-warranty-decision-form');
        const decisionSelect = document.getElementById('panel-warranty-decision');
        const noteForm = document.getElementById('panel-warranty-note-form');
        const noteTextarea = noteForm.querySelector('textarea[name="body"]');
        const noteCancelBtn = document.getElementById('panel-warranty-note-cancel');
        const repairForm = document.getElementById('panel-warranty-repair-form');
        const currentQuery = {!! json_encode($currentWarrantyQueryString, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) !!};

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str == null ? '' : String(str);
            return div.innerHTML;
        }

        function renderTimeline(containerId, items, emptyText) {
            const container = document.getElementById(containerId);
            if (!container) return;
            container.innerHTML = '';

            if (!items || !items.length) {
                container.innerHTML = '<p class="empty-hint">' + escapeHtml(emptyText) + '</p>';
                return;
            }

            items.forEach(function (item) {
                const wrap = document.createElement('div');
                wrap.className = 'timeline-item';
                wrap.innerHTML =
                    '<div class="timeline-dot"></div>' +
                    '<div class="timeline-body">' +
                        '<div class="timeline-title">' + escapeHtml(item.title) + '</div>' +
                        (item.meta ? '<div class="timeline-meta">' + escapeHtml(item.meta) + '</div>' : '') +
                        (item.text ? '<div class="timeline-text">' + escapeHtml(item.text) + '</div>' : '') +
                    '</div>';
                container.appendChild(wrap);
            });
        }

        function resetTabs() {
            panel.querySelectorAll('.tab').forEach(function (b) {
                b.classList.remove('tab-active');
                b.setAttribute('aria-selected', 'false');
            });
            const first = panel.querySelector('.tab[data-tab="notes"]');
            if (first) { first.classList.add('tab-active'); first.setAttribute('aria-selected', 'true'); }

            panel.querySelectorAll('[data-tab-content]').forEach(function (c) {
                c.hidden = c.getAttribute('data-tab-content') !== 'notes';
            });
        }

        function setText(id, value) {
            const el = document.getElementById(id);
            if (el) el.textContent = value || '—';
        }

        function openPanel(btn) {
            const pk = btn.getAttribute('data-claim-pk');
            const id = btn.getAttribute('data-claim-id') || '—';
            const meta = btn.getAttribute('data-claim-meta') || '';
            const status = btn.getAttribute('data-claim-status') || 'submitted';

            let payload = {};
            try { payload = JSON.parse(btn.getAttribute('data-claim-payload') || '{}'); } catch (e) { payload = {}; }
            const notes = payload.notes || [];
            const coverage = payload.coverage || {};
            const claimInfo = payload.claim || {};

            panel.querySelector('.detail-case-title').textContent = id;
            panel.querySelector('.detail-case-meta').textContent = meta;

            if (decisionSelect) decisionSelect.value = status;
            if (decisionForm) decisionForm.action = '{{ url('/ascm/warranty') }}/' + pk + '/decision' + (currentQuery ? ('?' + currentQuery) : '');
            if (noteForm) noteForm.action = '{{ url('/ascm/warranty') }}/' + pk + '/notes' + (currentQuery ? ('?' + currentQuery) : '');
            if (repairForm) repairForm.action = '{{ url('/ascm/warranty') }}/' + pk + '/repair' + (currentQuery ? ('?' + currentQuery) : '');

            setText('panel-warranty-period', coverage.period);
            setText('panel-warranty-eligibility', coverage.eligibility);
            setText('panel-warranty-sales', coverage.linked_sales);
            setText('panel-warranty-issue', claimInfo.issue);
            setText('panel-warranty-action', claimInfo.requested_action);
            setText('panel-warranty-amount', claimInfo.estimated_amount);

            renderTimeline('panel-warranty-notes', notes, 'No notes yet.');
            renderTimeline('panel-warranty-documents', payload.documents || [], 'No documents.');
            renderTimeline('panel-warranty-repairs', payload.repairs || [], 'No repairs scheduled yet.');

            resetTabs();

            overlay.hidden = false; overlay.classList.add('active');
            panel.classList.add('open'); panel.setAttribute('aria-hidden', 'false');

            if (btn.getAttribute('data-focus-note') === '1' && noteTextarea) {
                setTimeout(function () { noteTextarea.focus(); }, 260);
            }
        }

        function closePanel(){
            overlay.classList.remove('active');
            panel.classList.remove('open'); panel.setAttribute('aria-hidden','true');
            setTimeout(function () { overlay.hidden = true; }, 250);
        }

        document.addEventListener('click', function(e){
            const btn = e.target.closest('[data-action="view-warranty"]');
            if(btn) openPanel(btn);
        });

        overlay.addEventListener('click', closePanel);
        closeBtn.addEventListener('click', closePanel);

        noteCancelBtn.addEventListener('click', function () {
            if (noteTextarea) noteTextarea.value = '';
            closePanel();
        });

        panel.querySelectorAll('.tab').forEach(function (tabBtn) {
            tabBtn.addEventListener('click', function () {
                panel.querySelectorAll('.tab').forEach(function (b) {
                    b.classList.remove('tab-active');
                    b.setAttribute('aria-selected', 'false');
                });
                tabBtn.classList.add('tab-active');
                tabBtn.setAttribute('aria-selected', 'true');

                const key = tabBtn.getAttribute('data-tab');
                panel.querySelectorAll('[data-tab-content]').forEach(function (c) {
                    c.hidden = c.getAttribute('data-tab-content') !== key;
                });
            });
        });
    })();

    // Generic "..." action-menu dropdown — identical behavior to Cases,
    // duplicated here since every section is self-contained.
    (function () {
        function closeAllMenus() {
            document.querySelectorAll('.action-menu-list.open').forEach(function (menu) {
                menu.classList.remove('open');
                menu.hidden = true;
                menu.style.position = '';
                menu.style.top = '';
                menu.style.left = '';
                menu.style.minWidth = '';
                if (menu.dataset.homeParent) {
                    const home = document.getElementById(menu.dataset.homeParent);
                    if (home) home.appendChild(menu);
                }
            });
            document.querySelectorAll('.action-menu-trigger[aria-expanded="true"]').forEach(function (t) {
                t.setAttribute('aria-expanded', 'false');
            });
        }

        function openMenu(trigger) {
            const host = trigger.parentElement;
            const menu = host.querySelector('.action-menu-list');
            if (!menu) return;

            if (!host.id) host.id = 'menu-host-' + Math.random().toString(36).slice(2, 9);
            menu.dataset.homeParent = host.id;

            document.body.appendChild(menu);
            menu.hidden = false;
            menu.style.position = 'fixed';

            const rect = trigger.getBoundingClientRect();
            const menuRect = menu.getBoundingClientRect();

            let top = rect.bottom + 6;
            if (top + menuRect.height > window.innerHeight - 8) {
                top = Math.max(8, rect.top - menuRect.height - 6);
            }
            let left = rect.right - menuRect.width;
            if (left < 8) left = 8;
            if (left + menuRect.width > window.innerWidth - 8) left = window.innerWidth - menuRect.width - 8;

            menu.style.top = top + 'px';
            menu.style.left = left + 'px';
            menu.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');
        }

        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-menu-trigger]');
            if (trigger) {
                const host = trigger.parentElement;
                const menu = host.querySelector('.action-menu-list');
                const wasOpen = menu && menu.classList.contains('open');
                closeAllMenus();
                if (!wasOpen) openMenu(trigger);
                e.stopPropagation();
                return;
            }

            if (e.target.closest('.action-menu-list')) {
                setTimeout(closeAllMenus, 0);
                return;
            }

            closeAllMenus();
        });

        window.addEventListener('scroll', closeAllMenus, true);
        window.addEventListener('resize', closeAllMenus);
    })();
</script>
@endsection

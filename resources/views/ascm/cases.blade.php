@extends('layouts.app')

@section('title', 'Cases')

@section('content')
{{--
    Cases page — data comes from AscmShellController@cases: $cases
    (paginated Eloquent, 10 per page), $caseStats (counts across the whole
    table), $caseDetails (per-case notes/attachments/history for the rows
    on the current page, pre-shaped for the off-canvas panel's JS).

    Status updates, notes, escalate, and close are real form submissions —
    see CaseController. Every row's actions are collapsed into a single
    "..." menu (see the .action-menu markup + script at the bottom).
    "New Case" is still a non-functional stub; it isn't wired to a create
    form/route yet.
--}}

@php
    $priorityPill = ['low' => 'pill-green', 'medium' => 'pill-green', 'high' => 'pill-yellow', 'critical' => 'pill-red'];
    $statusPill = ['pending' => 'pill-blue', 'open' => 'pill-green', 'resolved' => 'pill-gray', 'closed' => 'pill-gray'];

    // Carried into every row's escalate/close form action URL so acting on
    // a row doesn't lose the current filter/page when CaseController
    // redirects back (see CaseController::backToCases).
    $currentCaseQuery = array_filter(
        request()->only(['cases_page', 'cases_status', 'cases_priority', 'cases_from', 'cases_to', 'cases_customer']),
        fn ($v) => $v !== null && $v !== ''
    );

    $hasActiveCaseFilters = collect($caseFilters)->contains(fn ($v) => $v !== '' && strtolower((string) $v) !== 'all');

    // The off-canvas panel's status-update and note forms have their
    // action set dynamically by JS (they're shared across every row), so
    // the current filter/page state gets handed to the script as a plain
    // query string rather than baked into a per-row route() call.
    $currentCaseQueryString = http_build_query($currentCaseQuery);
@endphp

<div class="cases-wrapper">
    <div class="page-header">
        <div>
            <h1 class="section-title">Cases</h1>
            <p class="section-hint">Support tickets, SLAs, and service collaboration.</p>
        </div>

        <div class="page-header-actions">
            <button type="button" class="btn btn-primary" aria-label="New case">
                New Case
            </button>
        </div>
    </div>

    @if (session('status'))
        <div class="flash-banner">{{ session('status') }}</div>
    @endif

        <!-- Off-canvas case detail panel -->
        <div id="case-detail-overlay" class="offcanvas-overlay" hidden></div>
        <aside id="case-detail-panel" class="offcanvas-panel" aria-hidden="true">
            <div class="offcanvas-header">
                <div>
                    <div class="detail-case">
                        <div class="detail-case-title">—</div>
                        <div class="detail-case-meta">—</div>
                    </div>
                </div>
                <button id="case-detail-close" class="btn btn-ghost" aria-label="Close">Close</button>
            </div>

            <div class="offcanvas-body">
                <form class="detail-actions" id="panel-case-status-form" method="POST" action="#">
                    @csrf
                    @method('PATCH')
                    <div class="field-inline">
                        <label class="filter-label" for="panel-case-status">Status</label>
                        <select id="panel-case-status" name="status" class="input">
                            <option value="pending">Pending</option>
                            <option value="open">Open</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>

                <div class="tabs" role="tablist" aria-label="Case tabs">
                    <button type="button" class="tab tab-active" role="tab" aria-selected="true" data-tab="timeline">Timeline</button>
                    <button type="button" class="tab" role="tab" aria-selected="false" data-tab="attachments">Attachments</button>
                    <button type="button" class="tab" role="tab" aria-selected="false" data-tab="communication">Communication</button>
                    <button type="button" class="tab" role="tab" aria-selected="false" data-tab="history">History</button>
                </div>

                <div data-tab-content="timeline" aria-label="Timeline panel">
                    <div class="timeline" id="panel-case-timeline"></div>

                    <form class="composer" id="panel-case-note-form" method="POST" action="#">
                        @csrf
                        <div class="composer-header">Add a note</div>
                        <textarea name="body" class="textarea" placeholder="Write an internal or customer-visible message..." required></textarea>
                        <label class="composer-checkbox">
                            <input type="checkbox" name="visibility" value="customer_visible">
                            Visible to customer
                        </label>
                        <div class="composer-actions">
                            <button type="submit" class="btn btn-primary">Post Note</button>
                            <button type="button" class="btn btn-ghost" id="panel-case-note-cancel">Cancel</button>
                        </div>
                    </form>
                </div>

                <div data-tab-content="attachments" aria-label="Attachments panel" hidden>
                    <div class="timeline" id="panel-case-attachments"></div>
                </div>

                <div data-tab-content="communication" aria-label="Communication panel" hidden>
                    <div class="timeline" id="panel-case-communication"></div>
                </div>

                <div data-tab-content="history" aria-label="History panel" hidden>
                    <div class="timeline" id="panel-case-history"></div>
                </div>
            </div>
        </aside>

    <div class="module-grid">
        {{-- Left: list / filters --}}
        <section class="module-card" aria-label="Cases list">
            <div class="card-header">
                <h2 class="card-title">Case Pipeline</h2>
                <span class="card-subtitle">Manage, prioritize, and respond.</span>
            </div>

            <div class="stats-row" aria-label="Case status overview">
                <div class="stat">
                    <div class="stat-value">{{ $caseStats['open'] }}</div>
                    <div class="stat-label">Open</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $caseStats['pending'] }}</div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $caseStats['resolved'] }}</div>
                    <div class="stat-label">Resolved</div>
                </div>
            </div>

            <form id="cases-filter-form" class="filters" aria-label="Case filters" method="GET" action="{{ route('ascm.cases') }}">

                <div class="filter filter-date">
                    <label class="filter-label" for="cases-date-from">From</label>
                    <input id="cases-date-from" name="cases_from" type="date" class="input" value="{{ $caseFilters['from'] }}" />
                </div>
                <div class="filter filter-date">
                    <label class="filter-label" for="cases-date-to">To</label>
                    <input id="cases-date-to" name="cases_to" type="date" class="input" value="{{ $caseFilters['to'] }}" />
                </div>

                <div class="filter filter-select">
                    <label class="filter-label" for="cases-status">Status</label>
                    <select id="cases-status" name="cases_status" class="input">
                        @foreach (['All', 'Open', 'Pending', 'Resolved', 'Closed'] as $option)
                            <option value="{{ $option }}" @selected(strcasecmp($caseFilters['status'], $option) === 0)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter filter-select">
                    <label class="filter-label" for="cases-priority">Priority</label>
                    <select id="cases-priority" name="cases_priority" class="input">
                        @foreach (['All', 'Low', 'Medium', 'High', 'Critical'] as $option)
                            <option value="{{ $option }}" @selected(strcasecmp($caseFilters['priority'], $option) === 0)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter filter-grow">
                    <label class="filter-label" for="cases-customer">Customer</label>
                    <input id="cases-customer" name="cases_customer" type="text" class="input" placeholder="Search by customer, email, phone..." value="{{ $caseFilters['customer'] }}" />
                </div>

                <div class="filter filter-buttons">
                    <button type="submit" class="btn btn-ghost btn-compact">Apply</button>
                    @if ($hasActiveCaseFilters)
                        <a href="{{ route('ascm.cases') }}" class="btn btn-ghost btn-compact">Clear</a>
                    @endif
                </div>
            </form>

            <div class="table-wrap" aria-label="Cases table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Case #</th>
                            <th>Customer</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Activity</th>
                            <th style="width:56px;text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cases as $case)
                            @php
                                $productName = optional($case->product ?? optional($case->orderItem)->product)->name;
                                $caseMeta = ($case->customer ? $case->customer->first_name . ' ' . $case->customer->last_name : '—') . ' • ' . $case->category . ' • ' . ucfirst($case->priority) . ' priority';
                                // NOTE: don't use the @json Blade directive here — it splits its
                                // expression on every top-level comma (to separate the optional
                                // json_encode $options/$depth args), which mangles any array
                                // literal default value like ['notes' => [], ...] and produces
                                // broken compiled PHP. Encode by hand instead, with the HEX_*
                                // flags so the quotes inside the JSON are escaped and can't break
                                // out of the single-quoted HTML attribute below.
                                $casePayload = json_encode(
                                    $caseDetails[$case->id] ?? ['notes' => [], 'attachments' => [], 'history' => []],
                                    JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG
                                );
                            @endphp
                            <tr>
                                <td><span class="mono">{{ $case->case_number }}</span></td>
                                <td>
                                    <div class="cell-primary">{{ $case->customer ? $case->customer->first_name . ' ' . $case->customer->last_name : '—' }}</div>
                                    @if ($case->order || $productName)
                                        <div class="cell-sub">{{ $case->order->order_number ?? '—' }}@if($productName) • {{ $productName }} @endif</div>
                                    @endif
                                </td>
                                <td>{{ $case->category }}</td>
                                <td><span class="pill {{ $priorityPill[$case->priority] ?? 'pill-gray' }}">{{ ucfirst($case->priority) }}</span></td>
                                <td><span class="pill {{ $statusPill[$case->status] ?? 'pill-gray' }}">{{ ucfirst($case->status) }}</span></td>
                                <td>
                                    <div class="cell-primary">SLA {{ $case->sla_due_at?->format('M j, Y') ?? '—' }}</div>
                                    <div class="cell-sub">Updated {{ $case->updated_at->diffForHumans() }}</div>
                                </td>
                                <td>
                                    <div class="action-menu">
                                        <button type="button" class="action-menu-trigger" data-menu-trigger aria-haspopup="true" aria-expanded="false" aria-label="Actions for {{ $case->case_number }}">
                                            <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="2.1"/><circle cx="12" cy="12" r="2.1"/><circle cx="19" cy="12" r="2.1"/></svg>
                                        </button>

                                        <div class="action-menu-list" role="menu" hidden>
                                            <button type="button" class="action-menu-item"
                                                    data-action="view-case"
                                                    data-case-pk="{{ $case->id }}"
                                                    data-case-id="{{ $case->case_number }}"
                                                    data-case-status="{{ $case->status }}"
                                                    data-case-meta="{{ $caseMeta }}"
                                                    data-case-payload='{!! $casePayload !!}'>View details</button>

                                            <button type="button" class="action-menu-item"
                                                    data-action="view-case"
                                                    data-focus-note="1"
                                                    data-case-pk="{{ $case->id }}"
                                                    data-case-id="{{ $case->case_number }}"
                                                    data-case-status="{{ $case->status }}"
                                                    data-case-meta="{{ $caseMeta }}"
                                                    data-case-payload='{!! $casePayload !!}'>Add note</button>

                                            @if (! in_array($case->status, ['resolved', 'closed']))
                                                <form method="POST" action="{{ route('ascm.cases.escalate', array_merge(['case' => $case], $currentCaseQuery)) }}" onsubmit="return confirm('Escalate {{ $case->case_number }} to L2 and mark it Critical?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="action-menu-item">Escalate</button>
                                                </form>
                                            @endif

                                            @if ($case->status !== 'closed')
                                                <form method="POST" action="{{ route('ascm.cases.close', array_merge(['case' => $case], $currentCaseQuery)) }}" onsubmit="return confirm('Close {{ $case->case_number }}?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="action-menu-item danger">Close case</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center;color:var(--color-text-muted);padding:28px;">
                                    No cases yet — once your seeder runs, they'll show up here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($cases->total() > 0)
                <div class="pagination-bar">
                    <span class="pagination-summary">Showing {{ $cases->firstItem() }}–{{ $cases->lastItem() }} of {{ $cases->total() }}</span>
                    @include('ascm.partials.pagination', ['paginator' => $cases])
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

    .cases-wrapper{padding-top:8px;}
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

    /* Compact, single-line filter bar. Each filter is a flex item with a
       small basis so the whole row fits on one line on typical desktop
       widths, and wraps only if the viewport is genuinely too narrow. */
    .filters{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px;margin:10px 0 16px;}
    .filter{display:flex;flex-direction:column;gap:5px;flex:1 1 120px;min-width:104px;}
    .filter-date{flex:1 1 130px;}
    .filter-select{flex:1 1 110px;}
    .filter-grow{flex:2 1 200px;}
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

    /* Action dropdown ("...") menu — see script at bottom of file. Menus
       are repositioned to position:fixed and reparented to <body> while
       open, so they always float above the table instead of being
       clipped by .table-wrap's horizontal scroll container. */
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
    .composer-checkbox{display:flex;align-items:center;gap:8px;margin-top:10px;font-size:0.8rem;color:var(--color-text-muted);}
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

<style>
    /* Off-canvas panel styles */
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
        const overlay = document.getElementById('case-detail-overlay');
        const panel = document.getElementById('case-detail-panel');
        const closeBtn = document.getElementById('case-detail-close');
        const statusForm = document.getElementById('panel-case-status-form');
        const statusSelect = document.getElementById('panel-case-status');
        const noteForm = document.getElementById('panel-case-note-form');
        const noteTextarea = noteForm.querySelector('textarea[name="body"]');
        const noteCancelBtn = document.getElementById('panel-case-note-cancel');
        const currentQuery = {!! json_encode($currentCaseQueryString, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) !!};

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
            const first = panel.querySelector('.tab[data-tab="timeline"]');
            if (first) { first.classList.add('tab-active'); first.setAttribute('aria-selected', 'true'); }

            panel.querySelectorAll('[data-tab-content]').forEach(function (c) {
                c.hidden = c.getAttribute('data-tab-content') !== 'timeline';
            });
        }

        function openPanel(btn) {
            const pk = btn.getAttribute('data-case-pk');
            const id = btn.getAttribute('data-case-id') || '—';
            const meta = btn.getAttribute('data-case-meta') || '';
            const status = btn.getAttribute('data-case-status') || 'pending';

            let payload = {};
            try { payload = JSON.parse(btn.getAttribute('data-case-payload') || '{}'); } catch (e) { payload = {}; }
            const notes = payload.notes || [];

            panel.querySelector('.detail-case-title').textContent = id;
            panel.querySelector('.detail-case-meta').textContent = meta;

            if (statusSelect) statusSelect.value = status;
            if (statusForm) statusForm.action = '{{ url('/ascm/cases') }}/' + pk + '/status' + (currentQuery ? ('?' + currentQuery) : '');
            if (noteForm) noteForm.action = '{{ url('/ascm/cases') }}/' + pk + '/notes' + (currentQuery ? ('?' + currentQuery) : '');

            renderTimeline('panel-case-timeline', notes, 'No notes yet.');
            renderTimeline('panel-case-attachments', payload.attachments || [], 'No attachments.');
            renderTimeline('panel-case-communication', payload.communication || [], 'No customer-visible messages yet.');
            renderTimeline('panel-case-history', payload.history || [], 'No status changes yet.');

            resetTabs();

            overlay.hidden = false; overlay.classList.add('active');
            panel.classList.add('open'); panel.setAttribute('aria-hidden', 'false');

            if (btn.getAttribute('data-focus-note') === '1' && noteTextarea) {
                setTimeout(function () { noteTextarea.focus(); }, 260);
            }
        }

        function closePanel() {
            overlay.classList.remove('active');
            panel.classList.remove('open'); panel.setAttribute('aria-hidden', 'true');
            setTimeout(function () { overlay.hidden = true; }, 250);
        }

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-action="view-case"]');
            if (btn) openPanel(btn);
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

    // make module-grid expand when it contains only one module-card
    (function(){
        document.addEventListener('DOMContentLoaded', function(){
            document.querySelectorAll('.module-grid').forEach(function(grid){
                const cards = grid.querySelectorAll('section.module-card');
                if(cards.length <= 1) grid.classList.add('module-grid--single');
            });
        });
    })();

    // Auto-submit the filter form when a select/date changes, so applying
    // a filter doesn't require an extra click on "Apply". The Customer
    // text field still submits normally (Enter, or the Apply button)
    // rather than firing on every keystroke.
    (function () {
        const form = document.getElementById('cases-filter-form');
        if (!form) return;
        form.querySelectorAll('select, input[type="date"]').forEach(function (el) {
            el.addEventListener('change', function () { form.submit(); });
        });
    })();

    // Generic "..." action-menu dropdown. Shared behavior is duplicated in
    // warranty.blade.php (each section script-tag is self-contained since
    // every section is inlined into the same page by spa.blade.php).
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
                // Let the clicked item's own handler (view-case listener,
                // or a real form submit) run first, then tidy up the menu.
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
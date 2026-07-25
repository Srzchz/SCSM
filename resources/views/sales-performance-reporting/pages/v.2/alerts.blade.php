{{--
    resources/views/sales-performance-reporting/pages/alerts.blade.php
    Route: GET /sales-performance-reporting/alerts -> AlertsController@index

    CHANGED: alerts are now fully automated (see AlertGenerationService,
    run on every page load). "+ New Alert", Edit, and Delete are removed —
    this page is read-only except for "Mark as read", which is user-side
    state, not alert authoring.
--}}
@extends('layouts.app')

@section('title', 'Alerts')

@push('styles')
    @include('sales-performance-reporting.partials.styles')
@endpush

@push('scripts')
    @include('sales-performance-reporting.partials.scripts')
@endpush

@section('content')

<div class="spr-page">
    <div class="page-header">
        <div>
            <h1 class="section-title">Alerts</h1>
            <p class="section-hint">Critical breaches, warnings, and opportunities — detected automatically from your data.</p>
        </div>
    </div>

    {{-- SUMMARY STRIP --}}
    <section class="stat-grid">
        <div class="card stat-card">
            <div class="stat-label">&#9888;&#65039; Critical</div>
            <div class="stat-value" style="color:var(--accent-red);">{{ $counts['critical'] }}</div>
            <div class="stat-sub warn">Immediate action needed</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">&#9201;&#65039; Warnings</div>
            <div class="stat-value" style="color:var(--accent-amber);">{{ $counts['warning'] }}</div>
            <div class="stat-sub neutral">Monitor closely</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">&#8593; Positive</div>
            <div class="stat-value" style="color:var(--accent-green-dk);">{{ $counts['positive'] }}</div>
            <div class="stat-sub">Opportunities surfaced</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">&#8505;&#65039; Info</div>
            <div class="stat-value" style="color:var(--accent-blue);">{{ $counts['info'] }}</div>
            <div class="stat-sub neutral">No action required</div>
        </div>
    </section>

    {{-- ALL ALERTS --}}
    <section class="card panel">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; flex-wrap:wrap; gap:12px;">
            <h2 style="margin:0;">All alerts</h2>
            <div class="seg-tabs" id="alertTabs">
                <button class="seg-tab active" data-filter="all">All</button>
                <button class="seg-tab" data-filter="critical">Critical</button>
                <button class="seg-tab" data-filter="warning">Warnings</button>
                <button class="seg-tab" data-filter="positive">Positive</button>
                <button class="seg-tab" data-filter="info">Info</button>
            </div>
        </div>

        <div id="alertList">
            @forelse ($alerts as $alert)
                <div class="alert-card"
                     data-id="{{ $alert->id }}"
                     data-category="{{ $alert->category }}"
                     data-icon="{{ $alert->iconChar }}"
                     data-title="{{ $alert->title }}"
                     data-desc="{{ $alert->description }}"
                     data-time="{{ $alert->timeAgo }}"
                     data-link-label="{{ $alert->link_label }}"
                     data-link-url="{{ $alert->link_url ?: '#' }}"
                     data-is-read="{{ $alert->is_read ? '1' : '0' }}"
                     style="cursor:pointer;"
                     onclick="openAlertDetail(this)">
                    @if (! $alert->is_read)
                        <div class="unread-dot"></div>
                    @endif
                    <div class="alert-icon {{ $alert->category }}">{{ $alert->iconChar }}</div>
                    <div class="alert-body">
                        <div class="alert-title">{{ $alert->title }}</div>
                        <div class="alert-desc">{{ $alert->description }}</div>
                        <div class="alert-meta">{{ $alert->timeAgo }}</div>
                        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                            @if ($alert->link_label)
                                <a href="{{ $alert->link_url ?: '#' }}" class="alert-link" onclick="event.stopPropagation()">{{ $alert->link_label }} &rarr;</a>
                            @endif
                            @if (! $alert->is_read)
                                <form method="POST" action="{{ route('sales-performance-reporting.alerts.markRead', $alert) }}" style="margin:0;" onclick="event.stopPropagation()">
                                    @csrf
                                    <button type="submit" class="alert-link" style="background:none; border:none; cursor:pointer; padding:0; font:inherit;">Mark as read</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p style="color:var(--muted); text-align:center; padding:30px 0;">No alerts right now — everything's within threshold.</p>
            @endforelse
        </div>
    </section>

    {{-- ============ ALERT DETAIL POPUP (read-only) ============ --}}
    <div class="modal-overlay" id="alertDetailOverlay" onclick="if(event.target === this) closeModal('alertDetailOverlay')">
        <div class="modal-card">
            <div class="modal-head">
                <h3>Alert Details</h3>
                <button class="modal-close" onclick="closeModal('alertDetailOverlay')" aria-label="Close">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>
            <div style="padding:22px 24px;">
                <div style="display:flex; align-items:flex-start; gap:14px; margin-bottom:16px;">
                    <div class="alert-icon" id="alertModalIconWrap" style="width:44px; height:44px; font-size:1.2rem; flex-shrink:0;">
                        <span id="alertModalIcon"></span>
                    </div>
                    <div>
                        <div class="alert-title" id="alertModalTitle" style="font-size:1.05rem;"></div>
                        <div class="alert-meta" id="alertModalTime"></div>
                    </div>
                </div>
                <p class="alert-desc" id="alertModalDesc" style="font-size:.92rem; margin:0 0 16px 0;"></p>
                <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                    <a href="#" id="alertModalLink" class="alert-link"></a>
                    <button type="button" id="alertModalMarkRead" class="btn btn-secondary" style="display:none;" onclick="submitMarkReadFromModal()">Mark as read</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ALERT SETTINGS — these thresholds drive AlertGenerationService directly --}}
    <section class="card panel">
        <h2>Alert settings</h2>

        <div class="settings-row">
            <div>
                <div class="settings-title">Target breach alert</div>
                <div class="settings-sub">Notify when attainment drops below threshold</div>
            </div>
            <div class="select mini-select" id="selThreshold">
                <button type="button" class="select-btn" onclick="toggleSelect(this)">
                    <span class="select-value">Below {{ $settings->target_breach_threshold_pct }}%</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <div class="select-menu">
                    @foreach ([60, 70, 80, 90] as $threshold)
                        <div class="select-option {{ $settings->target_breach_threshold_pct == $threshold ? 'selected' : '' }}" data-value="{{ $threshold }}">Below {{ $threshold }}%</div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="settings-row">
            <div>
                <div class="settings-title">Inventory trigger</div>
                <div class="settings-sub">Alert when a product trends +{{ $settings->inventory_trigger_growth_pct }}% MoM for {{ $settings->inventory_trigger_months }}+ months</div>
            </div>
            <button class="toggle-switch {{ $settings->inventory_trigger_enabled ? 'active' : 'inactive' }}" onclick="toggleSetting(this)">{{ $settings->inventory_trigger_enabled ? 'Active' : 'Inactive' }}</button>
        </div>

        <div class="settings-row">
            <div>
                <div class="settings-title">Forecast deviation</div>
                <div class="settings-sub">Alert when actuals deviate from forecast by &plusmn;{{ $settings->forecast_deviation_pct }}%</div>
            </div>
            <button class="toggle-switch {{ $settings->forecast_deviation_enabled ? 'active' : 'inactive' }}" onclick="toggleSetting(this)">{{ $settings->forecast_deviation_enabled ? 'Active' : 'Inactive' }}</button>
        </div>
        <p style="color:var(--muted); font-size:0.8rem; margin:14px 0 0;">
            Note: these toggles update the display immediately but aren't wired to a save route yet —
            wire a <code>POST /sales-performance-reporting/alert-settings</code> route to
            <code>AlertSetting::current()->update(...)</code> if you want them to persist.
        </p>
    </section>
</div>
@endsection

@push('scripts')
<script>
    // ---------- Filter tabs ----------
    document.querySelectorAll('#alertTabs .seg-tab').forEach(tab => {
        tab.addEventListener('click', function(){
            document.querySelectorAll('#alertTabs .seg-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;
            document.querySelectorAll('#alertList .alert-card').forEach(card => {
                const show = (filter === 'all') || (card.dataset.category === filter);
                card.style.display = show ? 'flex' : 'none';
            });
        });
    });

    // ---------- Threshold dropdown ----------
    initSelect(document.getElementById('selThreshold'));
    // toggleSetting()/toggleSelect()/openModal()/closeModal() are defined
    // globally in the shared scripts partial.

    // ---------- Alert detail popup ----------
    let currentAlertCard = null;

    function openAlertDetail(card){
        currentAlertCard = card;

        document.getElementById('alertModalIconWrap').className = 'alert-icon ' + card.dataset.category;
        document.getElementById('alertModalIcon').textContent = card.dataset.icon;
        document.getElementById('alertModalTitle').textContent = card.dataset.title;
        document.getElementById('alertModalTime').textContent = card.dataset.time;
        document.getElementById('alertModalDesc').textContent = card.dataset.desc;

        const linkEl = document.getElementById('alertModalLink');
        if(card.dataset.linkLabel){
            linkEl.style.display = '';
            linkEl.textContent = card.dataset.linkLabel + ' →';
            linkEl.href = card.dataset.linkUrl;
        } else {
            linkEl.style.display = 'none';
        }

        document.getElementById('alertModalMarkRead').style.display = card.dataset.isRead === '0' ? '' : 'none';

        openModal('alertDetailOverlay');
    }

    function submitMarkReadFromModal(){
        if(!currentAlertCard) return;
        const form = currentAlertCard.querySelector('form[action*="/read"]');
        if(form) form.submit();
    }
</script>
@endpush

{{--
    resources/views/pages/alerts.blade.php
    Route: GET /alerts -> App\Http\Controllers\AlertsController@index
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
            <p class="section-hint">Critical breaches, warnings, and opportunities as they happen.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="card panel" style="background:#e9f5d3; border-color:#c9e6a0; color:#3a5c22; font-weight:700; margin-bottom:22px;">
            &#9989; {{ session('success') }}
        </div>
    @endif

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
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <div class="seg-tabs" id="alertTabs">
                    <button class="seg-tab active" data-filter="all">All</button>
                    <button class="seg-tab" data-filter="critical">Critical</button>
                    <button class="seg-tab" data-filter="warning">Warnings</button>
                    <button class="seg-tab" data-filter="positive">Positive</button>
                </div>
                <button class="btn btn-primary" onclick="openNewAlertForm()">+ New Alert</button>
            </div>
        </div>

        <div id="alertList">
            @forelse ($alerts as $alert)
                <div class="alert-card"
                     data-id="{{ $alert->id }}"
                     data-category="{{ $alert->category }}"
                     data-icon="{{ $alert->icon }}"
                     data-title="{{ $alert->title }}"
                     data-desc="{{ $alert->description }}"
                     data-time="{{ $alert->timeAgo }}"
                     data-link-label="{{ $alert->link_label }}"
                     data-link-url="{{ $alert->link_url ?: '#' }}"
                     data-is-read="{{ $alert->is_read ? '1' : '0' }}"
                     data-update-url="{{ route('sales-performance-reporting.alerts.update', $alert) }}"
                     data-delete-url="{{ route('sales-performance-reporting.alerts.destroy', $alert) }}"
                     style="cursor:pointer;"
                     onclick="openAlertDetail(this)">
                    @if (! $alert->is_read)
                        <div class="unread-dot"></div>
                    @endif
                    <div class="alert-icon {{ $alert->category }}">{{ $alert->icon }}</div>
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
                <p style="color:var(--muted); text-align:center; padding:30px 0;">No alerts yet.</p>
            @endforelse
        </div>
    </section>

    {{-- ============ ALERT DETAIL POPUP ============ --}}
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
                    <button type="button" class="btn btn-secondary" onclick="openEditAlertForm()">Edit</button>
                    <button type="button" class="btn btn-danger" onclick="submitDeleteFromModal()">Delete</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ CREATE / EDIT ALERT FORM ============ --}}
    <div class="modal-overlay" id="alertFormOverlay" onclick="if(event.target === this) closeModal('alertFormOverlay')">
        <div class="modal-card">
            <div class="modal-head">
                <h3 id="alertFormHeading">New Alert</h3>
                <button class="modal-close" onclick="closeModal('alertFormOverlay')" aria-label="Close">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form method="POST" id="alertForm" action="{{ route('sales-performance-reporting.alerts.store') }}" style="padding:22px 24px; display:flex; flex-direction:column; gap:14px;">
                @csrf
                <div id="alertFormMethodField"></div>

                <div>
                    <label class="field-label">Category</label>
                    <select name="category" id="alertFormCategory" required
                        style="width:100%; padding:11px 14px; border-radius:10px; border:1px solid var(--border-soft); background:var(--panel-white); font-size:.9rem; font-weight:600; color:var(--ink);">
                        <option value="critical">Critical</option>
                        <option value="warning">Warning</option>
                        <option value="positive">Positive</option>
                        <option value="info">Info</option>
                    </select>
                </div>

                <div>
                    <label class="field-label">Title</label>
                    <input type="text" name="title" id="alertFormTitleInput" required maxlength="150"
                        style="width:100%; padding:11px 14px; border-radius:10px; border:1px solid var(--border-soft); font-size:.9rem;">
                </div>

                <div>
                    <label class="field-label">Description</label>
                    <textarea name="description" id="alertFormDesc" required rows="3" maxlength="1000"
                        style="width:100%; padding:11px 14px; border-radius:10px; border:1px solid var(--border-soft); font-size:.9rem; font-family:inherit; resize:vertical;"></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label class="field-label">Link Label (optional)</label>
                        <input type="text" name="link_label" id="alertFormLinkLabel" maxlength="100" placeholder="View region report"
                            style="width:100%; padding:11px 14px; border-radius:10px; border:1px solid var(--border-soft); font-size:.9rem;">
                    </div>
                    <div>
                        <label class="field-label">Link URL (optional)</label>
                        <input type="text" name="link_url" id="alertFormLinkUrl" maxlength="255" placeholder="/regions/1"
                            style="width:100%; padding:11px 14px; border-radius:10px; border:1px solid var(--border-soft); font-size:.9rem;">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:6px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('alertFormOverlay')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="alertFormSubmit">Save Alert</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Hidden delete form — reused by both the card and the detail popup --}}
    <form method="POST" id="deleteAlertForm" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- ALERT SETTINGS --}}
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
    // Note: toggleSetting() for the Active/Inactive switches is defined once,
    // globally, in layouts/app.blade.php — no need to redeclare it here.
    // None of these settings persist to the DB yet on toggle — that needs a
    // POST /alert-settings route + a fetch() call here when you're ready.

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
        if(form) form.submit(); // real POST — same route as the card's own "Mark as read" link
    }

    // ---------- Create / Edit form ----------
    function resetAlertForm(){
        document.getElementById('alertForm').reset();
        document.getElementById('alertFormMethodField').innerHTML = '';
    }

    function openNewAlertForm(){
        resetAlertForm();
        document.getElementById('alertFormHeading').textContent = 'New Alert';
        document.getElementById('alertForm').action = "{{ route('sales-performance-reporting.alerts.store') }}";
        document.getElementById('alertFormSubmit').textContent = 'Save Alert';
        openModal('alertFormOverlay');
    }

    function openEditAlertForm(){
        if(!currentAlertCard) return;
        closeModal('alertDetailOverlay');
        resetAlertForm();

        document.getElementById('alertFormHeading').textContent = 'Edit Alert';
        document.getElementById('alertForm').action = currentAlertCard.dataset.updateUrl;
        document.getElementById('alertFormSubmit').textContent = 'Update Alert';
        // HTML forms can't send PUT natively — Laravel reads this hidden
        // field to route it to the update() method instead of store().
        document.getElementById('alertFormMethodField').innerHTML = '@method('PUT')';

        document.getElementById('alertFormCategory').value = currentAlertCard.dataset.category;
        document.getElementById('alertFormTitleInput').value = currentAlertCard.dataset.title;
        document.getElementById('alertFormDesc').value = currentAlertCard.dataset.desc;
        document.getElementById('alertFormLinkLabel').value = currentAlertCard.dataset.linkLabel || '';
        document.getElementById('alertFormLinkUrl').value = currentAlertCard.dataset.linkUrl === '#' ? '' : currentAlertCard.dataset.linkUrl;

        openModal('alertFormOverlay');
    }

    // ---------- Delete ----------
    function submitDeleteFromModal(){
        if(!currentAlertCard) return;
        if(!confirm('Delete "' + currentAlertCard.dataset.title + '"? This removes it from the database permanently.')) return;
        const form = document.getElementById('deleteAlertForm');
        form.action = currentAlertCard.dataset.deleteUrl;
        form.submit();
    }
</script>
@endpush

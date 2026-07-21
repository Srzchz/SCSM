{{--
    resources/views/pages/targets.blade.php
    Route: GET /targets -> App\Http\Controllers\TargetsController@index
--}}
@extends('layouts.app')

@section('title', 'Targets')

@push('styles')
    @include('sales-performance-reporting.partials.styles')
@endpush

@section('content')

<div class="spr-page">
    <div class="page-header">
        <div>
            <h1 class="section-title">Target</h1>
            <p class="section-hint">Attainment against quota by rep, region, and product.</p>
        </div>
    </div>

    <section class="stat-grid">
        <div class="card stat-card">
            <div class="stat-label">Reps on Track</div>
            <div class="stat-value">{{ $kpis['repsOnTrack'] }}</div>
            <div class="stat-sub neutral">&ge;80% attainment</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Regions on track</div>
            <div class="stat-value">{{ $kpis['regionsOnTrack'] }}</div>
            <div class="stat-sub neutral">&ge;80% attainment</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Products on track</div>
            <div class="stat-value">{{ $kpis['productsOnTrack'] }}</div>
            <div class="stat-sub neutral">&ge;80% of quota</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Overall attainment</div>
            <div class="stat-value">{{ $kpis['overallPct'] }}%</div>
            <div class="stat-sub neutral">&#8369;{{ number_format($kpis['overallActual'] / 1000000, 2) }}M of &#8369;{{ number_format($kpis['overallGoal'] / 1000000, 2) }}M</div>
        </div>
    </section>

    <div style="display:flex; align-items:center; justify-content:flex-end; margin-bottom:14px;">
        <div class="seg-tabs" id="targetTabs">
            <button class="seg-tab active" data-filter="all">All</button>
            <button class="seg-tab" data-filter="on-track">On track</button>
            <button class="seg-tab" data-filter="at-risk">At risk</button>
            <button class="seg-tab" data-filter="exceeded">Exceeded</button>
        </div>
    </div>

    <section class="content-grid">
        <div class="card table-panel" style="padding:26px 30px;">
            <h2>Rep Attainment</h2>
            <table class="data-table target-table">
                <thead>
                    <tr><th>Rep</th><th>Actual</th><th>Quota</th><th>Attainment</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($repTargets as $t)
                        <tr data-status="{{ $t->attainmentStatus() }}">
                            <td>{{ $t->rep->name }}</td>
                            <td>{{ $t->actualFormatted() }}</td>
                            <td>{{ $t->targetFormatted() }}</td>
                            <td><div class="progress-track"><div class="progress-fill {{ $t->attainmentStatus() }}" style="width:{{ $t->progressWidth() }}%"></div></div></td>
                            <td><span class="status-pill {{ $t->attainmentStatus() }}">{{ $t->attainmentLabel() }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="empty-msg" style="display:none; text-align:center; color:var(--muted); padding:20px 0; margin:0;">No rows match this filter.</p>
        </div>

        <div class="card table-panel" style="padding:26px 30px;">
            <h2>Region Attainment</h2>
            <table class="data-table target-table">
                <thead>
                    <tr><th>Region</th><th>Attainment</th></tr>
                </thead>
                <tbody>
                    @foreach ($regionTargets as $t)
                        <tr data-status="{{ $t->attainmentStatus() }}">
                            <td>{{ $t->region->name }}</td>
                            <td><div class="progress-track" style="width:100%;"><div class="progress-fill {{ $t->attainmentStatus() }}" style="width:{{ $t->progressWidth() }}%"></div></div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="empty-msg" style="display:none; text-align:center; color:var(--muted); padding:20px 0; margin:0;">No rows match this filter.</p>
        </div>
    </section>

    <section class="card table-panel">
        <h2>Product Quota Attainment</h2>
        <table class="data-table target-table">
            <thead>
                <tr>
                    <th>Product</th><th>Actual</th><th>Quota</th>
                    <th style="cursor:pointer; user-select:none;" id="sortAttainment">Attainment &#8597;</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="quotaTableBody">
                @foreach ($productTargets as $t)
                    <tr data-status="{{ $t->attainmentStatus() }}" data-pct="{{ $t->attainmentPct() }}">
                        <td>{{ $t->product->name }}</td>
                        <td>{{ $t->actualFormatted() }}</td>
                        <td>{{ $t->targetFormatted() }}</td>
                        <td><div class="progress-track"><div class="progress-fill {{ $t->attainmentStatus() }}" style="width:{{ $t->progressWidth() }}%"></div></div></td>
                        <td><span class="status-pill {{ $t->attainmentStatus() }}">{{ $t->attainmentLabel() }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="empty-msg" style="display:none; text-align:center; color:var(--muted); padding:20px 0; margin:0;">No rows match this filter.</p>
    </section>
</div>
@endsection

@push('scripts')
<script>
    // ---------- Shared status filter across all three tables ----------
    document.querySelectorAll('#targetTabs .seg-tab').forEach(tab => {
        tab.addEventListener('click', function(){
            document.querySelectorAll('#targetTabs .seg-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;

            document.querySelectorAll('.target-table').forEach(table => {
                let visibleCount = 0;
                table.querySelectorAll('tbody tr').forEach(row => {
                    const show = (filter === 'all') || (row.dataset.status === filter);
                    row.style.display = show ? '' : 'none';
                    if(show) visibleCount++;
                });
                const emptyMsg = table.closest('.table-panel').querySelector('.empty-msg');
                if(emptyMsg) emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
            });
        });
    });

    // ---------- Sortable "Attainment" column on the Product Quota table ----------
    let sortAsc = true;
    document.getElementById('sortAttainment').addEventListener('click', function(){
        const tbody = document.getElementById('quotaTableBody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort((a, b) => sortAsc
            ? a.dataset.pct - b.dataset.pct
            : b.dataset.pct - a.dataset.pct);
        rows.forEach(r => tbody.appendChild(r));
        sortAsc = !sortAsc;
        this.innerHTML = 'Attainment ' + (sortAsc ? '&#8593;' : '&#8595;');
    });
</script>
@endpush

{{--
    resources/views/pages/generate-report.blade.php
    Route: GET /generate-report -> App\Http\Controllers\GenerateReportController@index
--}}
@extends('layouts.app')

@section('title', 'Generate Report')


@section('content')

    
    <section class="card panel" style="margin-bottom:22px;">
        <h2 style="margin-bottom:4px;">Generate Report</h2>
        <p class="panel-sub" style="margin-top:0;">Filter by dimension, data range, and export format</p>

        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px 24px; margin-bottom:26px;">
            <div class="field">
                <label class="field-label">Report Type</label>
                <div class="select" id="selReportType">
                    <button type="button" class="select-btn" onclick="toggleSelect(this)">
                        <span class="select-value">Sales by Rep</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                    <div class="select-menu">
                        <div class="select-option selected" data-value="rep">Sales by Rep</div>
                        <div class="select-option" data-value="product">Sales by Product</div>
                        <div class="select-option" data-value="region">Sales by Region</div>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="field-label">Data Range</label>
                <div class="select" id="selDataRange">
                    <button type="button" class="select-btn" onclick="toggleSelect(this)">
                        <span class="select-value">Jan - Jun</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                    <div class="select-menu">
                        <div class="select-option selected" data-value="jan-jun">Jan - Jun</div>
                        <div class="select-option" data-value="jul-dec">Jul - Dec</div>
                        <div class="select-option" data-value="q1">Q1 Only</div>
                        <div class="select-option" data-value="q2">Q2 Only</div>
                        <div class="select-option" data-value="ytd">Year to Date</div>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="field-label">Compare Against</label>
                <div class="select" id="selCompare">
                    <button type="button" class="select-btn" onclick="toggleSelect(this)">
                        <span class="select-value">Forecast</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                    <div class="select-menu">
                        <div class="select-option selected" data-value="forecast">Forecast</div>
                        <div class="select-option" data-value="last-quarter">Last Quarter</div>
                        <div class="select-option" data-value="last-year">Last Year</div>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="field-label">Region Filter</label>
                <div class="select" id="selRegion">
                    <button type="button" class="select-btn" onclick="toggleSelect(this)">
                        <span class="select-value">ALL</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                    <div class="select-menu">
                        <div class="select-option selected" data-value="all">ALL</div>
                        @foreach ($regions as $region)
                            <div class="select-option" data-value="{{ strtolower($region->name) }}">{{ $region->name }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="field-label">Product Filter</label>
                <div class="select" id="selProduct">
                    <button type="button" class="select-btn" onclick="toggleSelect(this)">
                        <span class="select-value">ALL</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                    <div class="select-menu">
                        <div class="select-option selected" data-value="all">ALL</div>
                        @foreach ($products as $product)
                            <div class="select-option" data-value="{{ \Illuminate\Support\Str::slug($product->name) }}">{{ $product->name }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="field-label">Rep Filter</label>
                <div class="select" id="selRep">
                    <button type="button" class="select-btn" onclick="toggleSelect(this)">
                        <span class="select-value">ALL</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                    <div class="select-menu">
                        <div class="select-option selected" data-value="all">ALL</div>
                        @foreach ($reps as $rep)
                            <div class="select-option" data-value="{{ strtolower($rep->name) }}">{{ $rep->name }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex; justify-content:center; gap:16px;">
            <button class="btn btn-primary" id="btnGenerate" onclick="generateReport()">GENERATE REPORT</button>
            <button class="btn btn-secondary" id="btnClear" onclick="clearFilters()">CLEAR</button>
        </div>
    </section>

    <section class="card table-panel" id="reportResults">
        <div class="table-panel-head">
            <span style="width:90px;"></span>
            <h2 id="reportTitle">Sales by Representative</h2>
            <button class="btn btn-dark" onclick="downloadCsv()">Download</button>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th id="reportCol1">Rep</th>
                    <th>Region</th>
                    <th>Actual</th>
                    <th>Target</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="reportTableBody"></tbody>
        </table>
    </section>

    <p id="reportEmptyState" class="card panel" style="display:none; text-align:center; color:var(--muted); font-weight:600;">
        Filters cleared. Choose your filters above and click <strong style="color:var(--ink); margin:0 4px;">Generate Report</strong> to see results.
    </p>

    
@endsection

@section('extra-scripts')
<script>
    // ---------- Dataset per dimension, straight from the DB (see GenerateReportController) ----------
    const reportData = @json($reportData);

    function fmt(n){ return n >= 1000 ? '$' + (n/1000).toFixed(2).replace(/\.00$/, '') + 'M' : '$' + Math.round(n) + 'K'; }

    function currentFilters(){
        return {
            reportType: document.getElementById('selReportType').dataset.value || '',
            region:     document.getElementById('selRegion').dataset.value || '',
            product:    document.getElementById('selProduct').dataset.value || '',
            rep:        document.getElementById('selRep').dataset.value || ''
        };
    }

    function generateReport(){
        const f = currentFilters();

        // Reveal the results table and hide the "cleared" empty state, if visible.
        document.getElementById('reportResults').style.display = '';
        document.getElementById('reportEmptyState').style.display = 'none';

        // Guard: Report Type is required to know which dataset/columns to show.
        if(!f.reportType){
            document.getElementById('reportTitle').textContent = 'Sales Report';
            document.getElementById('reportCol1').textContent = '—';
            document.getElementById('reportTableBody').innerHTML =
                `<tr><td colspan="6" style="text-align:center; color:var(--muted); padding:30px 0;">Select a Report Type above, then click Generate Report.</td></tr>`;
            return;
        }

        const d = reportData[f.reportType];
        document.getElementById('reportTitle').textContent = d.title;
        document.getElementById('reportCol1').textContent = d.col1;

        let rows = d.rows.slice();

        if(f.reportType === 'rep'){
            if(f.rep && f.rep !== 'all'){
                rows = rows.filter(r => r.name.toLowerCase() === f.rep);
            }
            if(f.region && f.region !== 'all'){
                rows = rows.filter(r => r.region.toLowerCase() === f.region);
            }
        }
        if(f.reportType === 'region' && f.region && f.region !== 'all'){
            rows = rows.filter(r => r.name.toLowerCase() === f.region);
        }
        if(f.reportType === 'product' && f.product && f.product !== 'all'){
            rows = rows.filter(r => r.name.toLowerCase().replace(/\s+/g,'-') === f.product);
        }

        const body = document.getElementById('reportTableBody');
        if(rows.length === 0){
            body.innerHTML = `<tr><td colspan="6" style="text-align:center; color:var(--muted); padding:30px 0;">No results match the current filters.</td></tr>`;
            return;
        }
        body.innerHTML = rows.map(r => `
            <tr>
                <td>${r.name}</td>
                <td>${r.region}</td>
                <td>${fmt(r.actual)}</td>
                <td>${fmt(r.target)}</td>
                <td>
                    <div class="progress-track">
                        <div class="progress-fill ${r.status}" style="width:${r.pct}%"></div>
                    </div>
                </td>
                <td><span class="status-pill ${r.status}">${r.label}</span></td>
            </tr>
        `).join('');
    }

    function clearFilters(){
        // Reset every dropdown to a true blank / placeholder state — nothing selected.
        document.querySelectorAll('.select').forEach(sel => {
            sel.querySelectorAll('.select-option').forEach(o => o.classList.remove('selected'));
            sel.querySelector('.select-value').textContent = 'Select...';
            sel.dataset.value = '';
            sel.classList.remove('open');
        });

        // Hide the results table until the user clicks Generate Report again.
        document.getElementById('reportResults').style.display = 'none';
        document.getElementById('reportEmptyState').style.display = 'block';
    }

    function downloadCsv(){
        const rows = Array.from(document.querySelectorAll('#reportTableBody tr')).map(tr =>
            Array.from(tr.children).map(td => td.innerText.replace(/\n/g,' ').trim())
        );
        const headers = Array.from(document.querySelectorAll('.data-table thead th')).map(th => th.innerText);
        const csv = [headers, ...rows].map(r => r.map(c => `"${c}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type:'text/csv' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = document.getElementById('reportTitle').textContent.replace(/\s+/g,'-').toLowerCase() + '.csv';
        link.click();
    }

    // Dropdowns only update their own displayed value/state now — the table
    // is refreshed exclusively by the Generate Report button, so it stays
    // hidden after Clear until the user explicitly clicks Generate again.
    ['selReportType','selDataRange','selCompare','selRegion','selProduct','selRep'].forEach(id => {
        initSelect(document.getElementById(id));
    });

    generateReport();
</script>
@endsection

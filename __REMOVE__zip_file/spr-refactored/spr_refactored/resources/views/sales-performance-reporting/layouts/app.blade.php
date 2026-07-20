{{--
    resources/views/layouts/app.blade.php

    Shared shell for the Sales Performance Reporting and Forecasting app.
    The sidebar is position:fixed and never scrolls. The header is
    position:sticky so it stays put while each page's content scrolls
    underneath it — matching the reference screenshots.

    Every page view does:
        @extends('layouts.app')
        @section('title', 'Page Title')
        @section('active', 'dashboard')   // matches a nav-item's data-page
        @section('content') ... @endsection
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        // Apply saved theme before paint to avoid a flash of the wrong theme.
        (function(){
            try{
                if(localStorage.getItem('theme') === 'dark'){
                    document.documentElement.classList.add('dark-theme');
                }
            }catch(e){}
        })();
    </script>
    <title>@yield('title', 'Sales Performance Reporting and Forecasting') · ULTD</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        :root{
            --bg-lavender:      #eceafc;
            --panel-white:      #ffffff;
            --sidebar-bg:       #ece9fb;
            --ink:              #1f1147;
            --ink-soft:         #4b3f78;
            --muted:            #7a7398;
            --accent-indigo:    #5b4fd1;
            --accent-indigo-dk: #3d2f8f;
            --accent-lilac:     #d9d3fb;
            --accent-green:     #8bc34a;
            --accent-green-dk:  #5a8f2b;
            --accent-red:       #e0483e;
            --accent-red-bg:    #fde7e6;
            --accent-amber:     #e8b923;
            --accent-amber-bg:  #fdf6df;
            --accent-blue:      #4f7ddb;
            --accent-blue-bg:   #e6edfb;
            --line-forecast:    #3a3a4a;
            --border-soft:      #d9d5f5;
            --radius-lg:        22px;
            --radius-md:        16px;
            --shadow-card:      0 4px 18px rgba(93, 79, 209, 0.08);
            --sidebar-w:        250px;
        }
        *{ box-sizing:border-box; }
        html,body{ margin:0; padding:0; }
        body{
            font-family:'Segoe UI','Inter',-apple-system,BlinkMacSystemFont,sans-serif;
            background: var(--bg-lavender);
            color: var(--ink);
        }
        a{ text-decoration:none; }

        /* ============ SIDEBAR (fixed, never scrolls) ============ */
        .sidebar{
            position:fixed;
            top:0; left:0; bottom:0;
            width:var(--sidebar-w);
            background:var(--sidebar-bg);
            border-right:1px solid var(--border-soft);
            padding:28px 18px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            overflow:hidden;
            z-index:100;
        }
        .brand{
            display:flex; align-items:center; gap:10px;
            font-weight:800; font-size:1.35rem; letter-spacing:.5px;
            color:var(--ink); padding:0 8px 26px 8px;
        }
        nav.nav-primary, nav.nav-secondary{ display:flex; flex-direction:column; gap:4px; }
        nav.nav-primary{ margin-bottom:26px; }
        .nav-item{
            display:flex; align-items:center; gap:12px;
            padding:11px 14px; border-radius:12px;
            font-size:.93rem; font-weight:600; color:var(--ink-soft);
            transition:background .15s ease,color .15s ease;
        }
        .nav-item svg{ opacity:.75; flex-shrink:0; }
        .nav-item:hover{ background:rgba(91,79,209,.08); }
        .nav-item.active{ background:var(--panel-white); color:var(--accent-indigo-dk); box-shadow:var(--shadow-card); }
        .nav-item.active svg{ opacity:1; }
        .sidebar-footer{ border-top:1px solid var(--border-soft); padding-top:16px; display:flex; flex-direction:column; gap:4px; }

        /* ============ MAIN AREA ============ */
        .main{
            margin-left:var(--sidebar-w);
            min-height:100vh;
            padding:0 34px 48px 34px;
        }
        .page-header{
            position:sticky;
            top:0;
            z-index:20;
            overflow:hidden;
            background:linear-gradient(135deg,#f3f1ff 0%,#e7e3fb 100%);
            border-radius:0 0 var(--radius-lg) var(--radius-lg);
            padding:34px 38px;
            margin:0 0 26px 0;
            border:1px solid var(--border-soft);
            border-top:none;
        }
        .page-header h1{
            margin:0; font-size:1.6rem; letter-spacing:1px; font-weight:800;
            color:var(--ink); position:relative; z-index:2;
        }
        .header-mesh{ position:absolute; top:-40px; right:-40px; width:420px; height:220px; z-index:1; opacity:.55; }

        /* ============ SHARED CARD / GRID PRIMITIVES ============ */
        .card{ background:var(--panel-white); border-radius:var(--radius-md); box-shadow:var(--shadow-card); border:1px solid var(--border-soft); }
        .stat-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:18px; margin-bottom:22px; }
        .stat-card{ padding:20px 22px; }
        .stat-label{ display:flex; align-items:center; gap:8px; font-size:.82rem; font-weight:600; color:var(--muted); margin-bottom:10px; }
        .stat-value{ font-size:1.9rem; font-weight:800; color:var(--ink); margin-bottom:6px; }
        .stat-sub{ font-size:.78rem; font-weight:600; color:var(--accent-green-dk); }
        .stat-sub.neutral{ color:var(--muted); }
        .stat-sub.warn{ color:#a6362c; }

        .content-grid{ display:grid; grid-template-columns:1.6fr 1fr; gap:18px; margin-bottom:22px; align-items:stretch; }
        .panel{ padding:24px 26px; }
        .panel h2{ margin:0 0 18px 0; font-size:1.05rem; font-weight:700; color:var(--ink); }
        .panel .panel-sub{ margin:-14px 0 18px 0; font-size:.85rem; color:var(--muted); font-weight:500; }

        .chart-wrap{ height:280px; position:relative; }
        .legend-row{ display:flex; gap:22px; margin-top:12px; font-size:.82rem; font-weight:600; color:var(--ink-soft); }
        .legend-dot{ display:inline-block; width:22px; height:3px; border-radius:2px; margin-right:6px; vertical-align:middle; }

        .insight-list{ display:flex; flex-direction:column; gap:14px; }
        .insight{ display:flex; align-items:flex-start; gap:12px; padding:16px 18px; border-radius:14px; font-size:.9rem; font-weight:600; line-height:1.4; border-left:5px solid transparent; }
        .insight.risk{ background:var(--accent-red-bg); border-left-color:var(--accent-red); color:#7a2620; }
        .insight.opportunity{ background:#e9f5e0; border-left-color:var(--accent-green-dk); color:#3a5c22; }
        .insight.watch{ background:var(--accent-amber-bg); border-left-color:var(--accent-amber); color:#6b5410; }
        .insight .icon{ font-size:1.05rem; line-height:1.4; flex-shrink:0; }

        .table-panel{ padding:26px 30px 12px 30px; margin-bottom:22px; }
        .table-panel-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; }
        .table-panel h2{ text-align:center; margin:0 0 0 0; flex:1; }

        table.data-table{ width:100%; border-collapse:collapse; font-size:.88rem; }
        .data-table thead th{ text-align:left; color:var(--ink); font-weight:700; font-size:.85rem; padding:8px 10px 14px 10px; border-bottom:1px solid var(--border-soft); }
        .data-table tbody td{ padding:14px 10px; color:var(--ink-soft); border-bottom:1px solid #efedfc; font-weight:600; }
        .data-table tbody tr:last-child td{ border-bottom:none; }

        .progress-track{ width:140px; height:8px; border-radius:6px; background:#ece7fa; position:relative; overflow:hidden; }
        .progress-fill{ height:100%; border-radius:6px; }
        .progress-fill.on-track{ background:linear-gradient(90deg,#cfe86a,#e6d94a); }
        .progress-fill.at-risk{ background:linear-gradient(90deg,#e0483e,#2a1e46); }
        .progress-fill.exceeded{ background:var(--accent-green-dk); }

        .status-pill{ font-size:.78rem; font-weight:700; padding:4px 10px; border-radius:20px; display:inline-block; white-space:nowrap; }
        .status-pill.on-track{ color:#5a7a10; background:#eef7d8; }
        .status-pill.at-risk{ color:#a6362c; background:#fbe2e0; }
        .status-pill.exceeded{ color:#3a5c22; background:#dff0c7; }

        .btn{ border:none; cursor:pointer; font-weight:700; font-size:.82rem; border-radius:10px; padding:12px 26px; letter-spacing:.4px; }
        .btn-primary{ background:var(--accent-lilac); color:var(--accent-indigo-dk); box-shadow:0 2px 0 rgba(93,79,209,.15) inset; }
        .btn-primary:hover{ background:#cbc2f9; }
        .btn-secondary{ background:#e4e1f7; color:var(--ink-soft); }
        .btn-secondary:hover{ background:#d7d2f4; }
        .btn-dark{ background:var(--ink); color:#fff; padding:8px 18px; border-radius:20px; font-size:.72rem; }

        /* ============ CUSTOM SELECT (functional dropdown) ============ */
        .field-label{ font-size:.85rem; font-weight:700; color:var(--ink); margin-bottom:8px; display:block; }
        .field{ position:relative; }
        .select{ position:relative; }
        .select-btn{
            width:100%; display:flex; align-items:center; justify-content:space-between;
            background:var(--accent-lilac); border:1px solid #c9befa; border-radius:12px;
            padding:12px 16px; font-size:.92rem; font-weight:700; color:var(--ink);
            cursor:pointer;
        }
        .select-btn svg{ transition:transform .15s ease; flex-shrink:0; }
        .select.open .select-btn svg{ transform:rotate(180deg); }
        .select-menu{
            position:absolute; left:0; right:0; top:calc(100% + 8px);
            background:var(--panel-white); border:1px solid var(--border-soft);
            border-radius:14px; box-shadow:0 12px 30px rgba(60,40,140,.18);
            padding:8px; z-index:50; display:none; max-height:240px; overflow-y:auto;
        }
        .select.open .select-menu{ display:block; }
        .select-option{
            padding:10px 12px; border-radius:8px; font-size:.9rem; font-weight:600;
            color:var(--ink-soft); cursor:pointer;
        }
        .select-option:hover{ background:var(--accent-lilac); color:var(--ink); }
        .select-option.selected{ color:var(--accent-indigo-dk); }

        /* ============ RANGE SLIDER ============ */
        .slider-row{ display:flex; align-items:center; gap:26px; padding:14px 0; }
        .slider-row label{ width:170px; flex-shrink:0; font-size:.92rem; font-weight:700; color:var(--ink); }
        input[type=range]{
            -webkit-appearance:none; appearance:none; flex:1; height:5px;
            border-radius:4px; background:var(--ink); outline:none;
        }
        input[type=range]::-webkit-slider-thumb{
            -webkit-appearance:none; width:20px; height:20px; border-radius:50%;
            background:#8f88b0; border:3px solid #fff; box-shadow:0 1px 4px rgba(0,0,0,.3); cursor:pointer;
        }
        input[type=range]::-moz-range-thumb{
            width:20px; height:20px; border-radius:50%; background:#8f88b0; border:3px solid #fff; cursor:pointer;
        }
        .slider-value{ width:52px; text-align:right; font-weight:700; font-size:.88rem; color:var(--accent-indigo-dk); flex-shrink:0; }

        /* ============ SEGMENTED FILTER TABS (Alerts / Targets) ============ */
        .seg-tabs{ display:inline-flex; gap:4px; background:#e4e1f7; padding:4px; border-radius:12px; }
        .seg-tab{ border:none; background:transparent; padding:8px 16px; font-size:.82rem; font-weight:700; color:var(--ink-soft); border-radius:9px; cursor:pointer; }
        .seg-tab.active{ background:var(--panel-white); color:var(--accent-indigo-dk); box-shadow:var(--shadow-card); }

        /* ============ ALERTS ============ */
        .alert-card{ position:relative; display:flex; gap:16px; padding:20px 22px; border-radius:16px; background:var(--panel-white); border:1px solid var(--border-soft); margin-bottom:14px; }
        .alert-icon{ width:38px; height:38px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:1.05rem; }
        .alert-icon.critical{ background:var(--accent-red-bg); }
        .alert-icon.warning{ background:var(--accent-amber-bg); }
        .alert-icon.positive{ background:#e2f3d3; }
        .alert-icon.info{ background:var(--accent-blue-bg); }
        .alert-body{ flex:1; min-width:0; }
        .alert-title{ font-weight:700; font-size:.98rem; color:var(--ink); margin-bottom:4px; }
        .alert-desc{ font-size:.87rem; color:var(--ink-soft); line-height:1.5; margin-bottom:8px; }
        .alert-meta{ font-size:.76rem; color:var(--muted); margin-bottom:6px; }
        .alert-link{ font-size:.83rem; font-weight:700; color:var(--accent-indigo-dk); }
        .alert-link:hover{ text-decoration:underline; }
        .unread-dot{ position:absolute; top:20px; right:20px; width:9px; height:9px; border-radius:50%; background:var(--accent-indigo); }

        .toggle-switch{ border:none; cursor:pointer; font-size:.76rem; font-weight:800; padding:6px 16px; border-radius:20px; letter-spacing:.3px; }
        .toggle-switch.active{ background:#dff0c7; color:#3a5c22; }
        .toggle-switch.inactive{ background:#ece7fa; color:var(--muted); }

        .settings-row{ display:flex; align-items:center; justify-content:space-between; gap:20px; padding:16px 0; border-bottom:1px solid #efedfc; }
        .settings-row:last-child{ border-bottom:none; }
        .settings-title{ font-weight:700; font-size:.92rem; color:var(--ink); margin-bottom:3px; }
        .settings-sub{ font-size:.8rem; color:var(--muted); }
        .mini-select{ min-width:150px; }
        .mini-select .select-btn{ padding:9px 14px; font-size:.82rem; }

        @media (max-width:1180px){
            .stat-grid{ grid-template-columns:repeat(2,1fr); }
            .content-grid{ grid-template-columns:1fr; }
        }
        @media (max-width:760px){
            .sidebar{ display:none; }
            .main{ margin-left:0; padding:0 16px 40px 16px; }
            .stat-grid{ grid-template-columns:1fr; }
            table.data-table{ font-size:.78rem; }
            .slider-row{ flex-wrap:wrap; gap:8px; }
            .slider-row label{ width:100%; }
        }

        /* ============ DARK THEME (toggled from Settings) ============ */
        html.dark-theme{
            --bg-lavender:      #100e1c;
            --panel-white:      #1c1930;
            --sidebar-bg:       #16132a;
            --ink:              #f1eefc;
            --ink-soft:         #cabfe8;
            --muted:            #9088b8;
            --accent-lilac:     #3a3160;
            --border-soft:      #322b54;
            --shadow-card:      0 4px 18px rgba(0,0,0,.35);
        }
        html.dark-theme .page-header{ background:linear-gradient(135deg,#221d3d 0%,#171428 100%); border-color:var(--border-soft); }
        html.dark-theme .select-btn{ border-color:#443a72; }
        html.dark-theme .select-menu{ background:#221d3d; }
        html.dark-theme .select-option:hover{ background:#332c58; color:var(--ink); }
        html.dark-theme .progress-track{ background:#2b2648; }
        html.dark-theme .seg-tabs{ background:#221d3d; }
        html.dark-theme .seg-tab.active{ background:var(--panel-white); }
        html.dark-theme .btn-secondary{ background:#2b2648; color:var(--ink-soft); }
        html.dark-theme .btn-secondary:hover{ background:#362e5c; }
        html.dark-theme .toggle-switch.inactive{ background:#2b2648; color:var(--muted); }
        html.dark-theme .settings-row{ border-bottom-color:#2a2547; }
        html.dark-theme .data-table tbody td{ border-bottom-color:#2a2547; }
        html.dark-theme input[type=range]{ background:#443a72; }

        /* ============ MODAL OVERLAY (Account / Settings) ============ */
        .modal-overlay{
            position:fixed; inset:0; z-index:300;
            background:rgba(20,14,45,.55);
            display:none; align-items:center; justify-content:center;
            padding:20px;
        }
        .modal-overlay.open{ display:flex; }
        .modal-card{
            background:var(--panel-white); border-radius:22px;
            width:400px; max-width:100%;
            box-shadow:0 24px 60px rgba(20,14,45,.35);
            padding:28px 28px 24px 28px;
            position:relative;
            max-height:88vh; overflow-y:auto;
        }
        .modal-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
        .modal-head h3{ margin:0; font-size:1.15rem; font-weight:800; color:var(--ink); }
        .modal-close{
            border:none; background:var(--bg-lavender); color:var(--ink-soft);
            width:30px; height:30px; border-radius:50%; cursor:pointer;
            display:flex; align-items:center; justify-content:center; flex-shrink:0;
        }
        .modal-close:hover{ background:var(--accent-lilac); color:var(--accent-indigo-dk); }

        /* ---- Account modal specifics ---- */
        .account-head{ display:flex; align-items:center; gap:14px; margin-bottom:20px; }
        .avatar-circle{
            width:56px; height:56px; border-radius:50%; flex-shrink:0;
            background:linear-gradient(135deg,#5b4fd1,#8f7bf0);
            color:#fff; font-weight:800; font-size:1.1rem;
            display:flex; align-items:center; justify-content:center;
        }
        .account-name{ font-weight:800; font-size:1.05rem; color:var(--ink); }
        .account-role{ font-size:.83rem; color:var(--muted); font-weight:600; }
        .info-grid{ display:grid; grid-template-columns:1fr 1fr; gap:14px 10px; margin-bottom:22px; }
        .info-item .info-label{ font-size:.72rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; margin-bottom:3px; }
        .info-item .info-value{ font-size:.9rem; font-weight:700; color:var(--ink); }
        .modal-actions{ display:flex; gap:12px; }
        .btn-danger{ background:var(--accent-red-bg); color:var(--accent-red); }
        .btn-danger:hover{ background:#fbd7d5; }

        @yield('extra-style')
    </style>
</head>
<body>
<div class="app-shell">

    <aside class="sidebar">
        <div>
            <div class="brand">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2c-2.8 0-5 2.2-5 5 0 1.7.8 3.2 2 4.2V14l-3 3v3h12v-3l-3-3v-2.8c1.2-1 2-2.5 2-4.2 0-2.8-2.2-5-5-5z" fill="#1f1147"/>
                </svg>
                ULTD
            </div>

            @php($active = $active ?? 'dashboard')
            <nav class="nav-primary">
                <a href="{{ route('sales-performance-reporting.dashboard') }}" class="nav-item {{ $active === 'dashboard' ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" fill="currentColor"/><rect x="14" y="3" width="7" height="7" rx="1.5" fill="currentColor"/><rect x="3" y="14" width="7" height="7" rx="1.5" fill="currentColor"/><rect x="14" y="14" width="7" height="7" rx="1.5" fill="currentColor"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('sales-performance-reporting.generate-report') }}" class="nav-item {{ $active === 'generate-report' ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 20V10M12 20V4M20 20v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Generate Report
                </a>
                <a href="{{ route('sales-performance-reporting.revenue-forecast') }}" class="nav-item {{ $active === 'revenue-forecast' ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 17l5-5 4 4 8-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Revenue Forecast
                </a>
                <a href="{{ route('sales-performance-reporting.targets') }}" class="nav-item {{ $active === 'targets' ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                    Targets
                </a>
                <a href="{{ route('sales-performance-reporting.alerts') }}" class="nav-item {{ $active === 'alerts' ? 'active' : '' }}" style="position:relative;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.7 21a2 2 0 01-3.4 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Alerts
                    @if(($alertCount ?? 0) > 0)
                        <span style="margin-left:auto; background:var(--accent-red); color:#fff; font-size:.68rem; font-weight:800; padding:2px 7px; border-radius:20px;">{{ $alertCount }}</span>
                    @endif
                </a>
            </nav>

            <nav class="nav-secondary">
                <a href="#" class="nav-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="2"/><path d="M4 6l8 6 8-6" stroke="currentColor" stroke-width="2"/></svg>
                    Sales Order
                </a>
                <a href="#" class="nav-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 5a2 2 0 012-2h2l2 5-2 1a11 11 0 006 6l1-2 5 2v2a2 2 0 01-2 2A16 16 0 013 5z" stroke="currentColor" stroke-width="1.6"/></svg>
                    Customer Relation
                </a>
                <a href="#" class="nav-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/><path d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" stroke="currentColor" stroke-width="2"/></svg>
                    After-Sales Support
                </a>
            </nav>
        </div>

        <div class="sidebar-footer">
            <a href="#" class="nav-item" onclick="openModal('accountOverlay'); return false;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/><path d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" stroke="currentColor" stroke-width="2"/></svg>
                Account
            </a>
            <a href="#" class="nav-item" onclick="openModal('settingsOverlay'); return false;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/><path d="M19 12a7 7 0 00-.1-1.2l2-1.5-2-3.4-2.3.9a7 7 0 00-2-1.2L14 3h-4l-.6 2.6a7 7 0 00-2 1.2l-2.3-.9-2 3.4 2 1.5A7 7 0 005 12c0 .4 0 .8.1 1.2l-2 1.5 2 3.4 2.3-.9a7 7 0 002 1.2L10 21h4l.6-2.6a7 7 0 002-1.2l2.3.9 2-3.4-2-1.5c.1-.4.1-.8.1-1.2z" stroke="currentColor" stroke-width="1.5"/></svg>
                Settings
            </a>
        </div>
    </aside>

    {{-- ============ ACCOUNT MODAL ============ --}}
    <div class="modal-overlay" id="accountOverlay" onclick="if(event.target === this) closeModal('accountOverlay')">
        <div class="modal-card">
            <div class="modal-head">
                <h3>Account</h3>
                <button class="modal-close" onclick="closeModal('accountOverlay')" aria-label="Close">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>

            <div class="account-head">
                <div class="avatar-circle">{{ $accountUser?->initials() ?? '—' }}</div>
                <div>
                    <div class="account-name">{{ $accountUser?->name ?? 'No user found' }}</div>
                    <div class="account-role">{{ $accountUser?->department ?? 'Sales Operations' }}{{ $accountUser?->role ? ' · ' . ucfirst($accountUser->role) : '' }}</div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $accountUser?->email ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Employee ID</div>
                    <div class="info-value">{{ $accountUser?->employee_code ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Department</div>
                    <div class="info-value">{{ $accountUser?->department ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Region Access</div>
                    <div class="info-value">{{ $accountUser?->region?->name ?? 'All Regions' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Member Since</div>
                    <div class="info-value">{{ $accountUser?->created_at?->format('M Y') ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Plan</div>
                    <div class="info-value">{{ $accountUser?->plan ?? '—' }}</div>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn btn-secondary" style="flex:1;" onclick="alert('Edit profile isn\'t wired to a backend yet — hook this up to your user update route.')">Edit Profile</button>
                <button class="btn btn-danger" style="flex:1;" onclick="handleLogout()">Log Out</button>
            </div>
        </div>
    </div>

    {{-- ============ SETTINGS MODAL ============ --}}
    <div class="modal-overlay" id="settingsOverlay" onclick="if(event.target === this) closeModal('settingsOverlay')">
        <div class="modal-card">
            <div class="modal-head">
                <h3>Settings</h3>
                <button class="modal-close" onclick="closeModal('settingsOverlay')" aria-label="Close">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>

            @php($s = $userSettings)

            <div class="settings-row">
                <div>
                    <div class="settings-title">Notifications</div>
                    <div class="settings-sub">Get notified about critical alerts</div>
                </div>
                <button class="toggle-switch {{ ($s->notifications_enabled ?? true) ? 'active' : 'inactive' }}" onclick="toggleSetting(this)">{{ ($s->notifications_enabled ?? true) ? 'Active' : 'Inactive' }}</button>
            </div>

            <div class="settings-row">
                <div>
                    <div class="settings-title">Dark Mode</div>
                    <div class="settings-sub">Switch the whole app to a dark theme</div>
                </div>
                <button class="toggle-switch inactive" id="darkModeToggle" onclick="toggleDarkMode(this)">Inactive</button>
            </div>

            <div class="settings-row">
                <div>
                    <div class="settings-title">Quota Reminders</div>
                    <div class="settings-sub">Nudge reps nearing quota deadlines</div>
                </div>
                <button class="toggle-switch {{ ($s->quota_reminders_enabled ?? true) ? 'active' : 'inactive' }}" onclick="toggleSetting(this)">{{ ($s->quota_reminders_enabled ?? true) ? 'Active' : 'Inactive' }}</button>
            </div>

            <div class="settings-row" style="cursor:pointer;" onclick="alert('Privacy settings aren\'t built out yet.')">
                <div>
                    <div class="settings-title">Privacy</div>
                    <div class="settings-sub">Data sharing and visibility controls</div>
                </div>
                <span style="color:var(--muted);">&rsaquo;</span>
            </div>

            <div class="settings-row" style="cursor:pointer;" onclick="alert('Help & Support isn\'t built out yet.')">
                <div>
                    <div class="settings-title">Help &amp; Support</div>
                    <div class="settings-sub">FAQs, contact, and product docs</div>
                </div>
                <span style="color:var(--muted);">&rsaquo;</span>
            </div>

            <div class="settings-row" style="cursor:pointer; border-bottom:none;" onclick="handleLogout()">
                <div>
                    <div class="settings-title" style="color:var(--accent-red);">Log Out</div>
                    <div class="settings-sub">Sign out of this account</div>
                </div>
                <span style="color:var(--accent-red);">&rsaquo;</span>
            </div>
        </div>
    </div>

    <main class="main">
        <div class="page-header">
            <svg class="header-mesh" viewBox="0 0 420 220" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="dots" width="14" height="14" patternUnits="userSpaceOnUse">
                        <circle cx="1.2" cy="1.2" r="1.2" fill="#5b4fd1"/>
                    </pattern>
                </defs>
                <rect width="420" height="220" fill="url(#dots)"/>
            </svg>
            <h1>SALES PERFORMANCE REPORTING AND FORECASTING</h1>
        </div>

        @yield('content')
    </main>
</div>

<script>
    // ===== Generic functional dropdown (used across pages) =====
    function toggleSelect(btn){
        const el = btn.closest('.select');
        const wasOpen = el.classList.contains('open');
        document.querySelectorAll('.select.open').forEach(s => s.classList.remove('open'));
        if(!wasOpen) el.classList.add('open');
    }
    document.addEventListener('click', function(e){
        if(!e.target.closest('.select')){
            document.querySelectorAll('.select.open').forEach(s => s.classList.remove('open'));
        }
    });
    function initSelect(selectEl, onChange){
        selectEl.querySelectorAll('.select-option').forEach(opt => {
            opt.addEventListener('click', function(){
                selectEl.querySelectorAll('.select-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                selectEl.querySelector('.select-value').textContent = this.textContent.trim();
                selectEl.dataset.value = this.dataset.value;
                selectEl.classList.remove('open');
                if(onChange) onChange(this.dataset.value, this.textContent.trim());
            });
        });
    }

    // ===== Account / Settings modals =====
    function openModal(id){
        document.getElementById(id).classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id){
        document.getElementById(id).classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function(e){
        if(e.key === 'Escape'){
            document.querySelectorAll('.modal-overlay.open').forEach(m => closeModal(m.id));
        }
    });

    // ===== Generic Active/Inactive toggle switch (Notifications, Quota Reminders, Alert settings) =====
    function toggleSetting(btn){
        const isActive = btn.classList.contains('active');
        btn.classList.toggle('active', !isActive);
        btn.classList.toggle('inactive', isActive);
        btn.textContent = isActive ? 'Inactive' : 'Active';
    }

    // ===== Dark mode toggle (persists across pages via localStorage) =====
    function toggleDarkMode(btn){
        const isDark = document.documentElement.classList.toggle('dark-theme');
        try{ localStorage.setItem('theme', isDark ? 'dark' : 'light'); }catch(e){}
        btn.classList.toggle('active', isDark);
        btn.classList.toggle('inactive', !isDark);
        btn.textContent = isDark ? 'Active' : 'Inactive';
    }
    // Sync the toggle's visual state with whatever theme is currently applied
    document.addEventListener('DOMContentLoaded', function(){
        const isDark = document.documentElement.classList.contains('dark-theme');
        const toggle = document.getElementById('darkModeToggle');
        if(toggle){
            toggle.classList.toggle('active', isDark);
            toggle.classList.toggle('inactive', !isDark);
            toggle.textContent = isDark ? 'Active' : 'Inactive';
        }
    });

    // ===== Log out =====
    function handleLogout(){
        if(confirm('Are you sure you want to log out?')){
            // Replace with a real POST to your logout route, e.g.:
            // document.getElementById('logout-form').submit();
            window.location.href = '/';
        }
    }
</script>
@yield('extra-scripts')
</body>
</html>

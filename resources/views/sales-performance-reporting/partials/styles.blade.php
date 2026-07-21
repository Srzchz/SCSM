{{--
    Shared styling for the Sales Performance Reporting pages:
    Generate Report, Revenue Forecast, Alerts, Target.

    These 4 pages @extend the shared shell (layouts.app) rather than their
    own old standalone layout, so they need their component CSS pushed in
    directly. This partial is included once per page via @push('styles')
    so all four stay visually identical and in sync with one file.

    Color tokens mirror the Curema palette already used by the shell
    (see tailwind.config in layouts.app) and the hand-rolled CSS in
    ascm/cases.blade.php + ascm/warranty.blade.php, so these pages read as
    part of the same product rather than a bolted-on module. Custom
    property NAMES below (--ink, --muted, --accent-red, etc.) are kept
    from this module's original design so existing inline `style="color:
    var(--accent-red)"` usages in the page views keep working — only the
    color VALUES were changed to match Curema/ASCM.
--}}
<style>
    .spr-page{
        --bg-lavender: #E9EBFC;
        --panel-white: #FFFFFF;
        --ink: #120F34;
        --ink-soft: #3F3B63;
        --muted: #5B5876;
        --accent-indigo: #120F34;
        --accent-indigo-dk: #120F34;
        --accent-lilac: #CFD2F9;
        --accent-green: #9CFF9F;
        --accent-green-dk: #00630F;
        --accent-red: #8A2A1F;
        --accent-red-bg: rgba(255,154,145,0.35);
        --accent-amber: #7A5B12;
        --accent-amber-bg: rgba(249,223,170,0.5);
        --accent-blue: #004169;
        --accent-blue-bg: rgba(126,216,255,0.35);
        --line-forecast: #120F34;
        --border-soft: rgba(18,15,52,0.08);
        --radius-lg: 22px;
        --radius-md: 18px;
        --shadow-card: 0 10px 30px rgba(18,15,52,0.04);

        padding-top: 8px;
        color: var(--ink);
    }

    .spr-page a{ text-decoration:none; }

    /* ============ PAGE HEADER (matches ASCM Cases/Warranty) ============ */
    .spr-page .page-header{
        display:flex; align-items:center; justify-content:space-between; gap:16px;
        margin-bottom:20px; padding-bottom:16px;
        border-bottom:1px solid var(--border-soft);
    }
    .spr-page .page-header-actions{ display:flex; align-items:center; gap:10px; }
    .spr-page .section-title{ margin:0; font-size:1.3rem; font-weight:800; color:var(--ink); }
    .spr-page .section-hint{ margin:4px 0 0; color:var(--muted); font-size:0.9rem; }

    /* ============ SHARED CARD / GRID PRIMITIVES ============ */
    .spr-page .card{
        background:var(--panel-white);
        border-radius:var(--radius-md);
        box-shadow:var(--shadow-card);
        border:1px solid var(--border-soft);
    }
    .spr-page .stat-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:18px; margin-bottom:22px; }
    .spr-page .stat-card{ padding:20px 22px; }
    .spr-page .stat-label{ display:flex; align-items:center; gap:8px; font-size:0.82rem; font-weight:600; color:var(--muted); margin-bottom:10px; }
    .spr-page .stat-value{ font-size:1.9rem; font-weight:800; color:var(--ink); margin-bottom:6px; }
    .spr-page .stat-sub{ font-size:0.78rem; font-weight:600; color:var(--accent-green-dk); }
    .spr-page .stat-sub.neutral{ color:var(--muted); }
    .spr-page .stat-sub.warn{ color:var(--accent-red); }

    .spr-page .content-grid{ display:grid; grid-template-columns:1.6fr 1fr; gap:18px; margin-bottom:22px; align-items:stretch; }
    .spr-page .panel{ padding:24px 26px; }
    .spr-page .panel h2{ margin:0 0 18px 0; font-size:1.05rem; font-weight:800; color:var(--ink); }
    .spr-page .panel .panel-sub{ margin:-14px 0 18px 0; font-size:0.85rem; color:var(--muted); font-weight:500; }

    .spr-page .chart-wrap{ height:280px; position:relative; }
    .spr-page .legend-row{ display:flex; gap:22px; margin-top:12px; font-size:0.82rem; font-weight:600; color:var(--ink-soft); }
    .spr-page .legend-dot{ display:inline-block; width:22px; height:3px; border-radius:2px; margin-right:6px; vertical-align:middle; }

    /* ============ TABLES ============ */
    .spr-page .table-panel{ padding:26px 30px 12px 30px; margin-bottom:22px; }
    .spr-page .table-panel-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; }
    .spr-page .table-panel h2{ text-align:center; margin:0; flex:1; font-size:1.05rem; font-weight:800; color:var(--ink); }

    .spr-page table.data-table{ width:100%; border-collapse:collapse; font-size:0.88rem; }
    .spr-page .data-table thead th{ text-align:left; color:var(--muted); font-weight:700; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.04em; padding:14px 16px; border-bottom:1px solid var(--border-soft); }
    .spr-page .data-table tbody td{ padding:14px 16px; color:var(--ink-soft); font-weight:600; border-bottom:1px solid rgba(18,15,52,0.05); vertical-align:middle; }
    .spr-page .data-table tbody tr:last-child td{ border-bottom:none; }
    .spr-page .data-table tbody tr:hover{ background:rgba(233,235,252,0.45); }

    .spr-page .progress-track{ width:140px; height:8px; border-radius:6px; background:var(--bg-lavender); position:relative; overflow:hidden; }
    .spr-page .progress-fill{ height:100%; border-radius:6px; }
    .spr-page .progress-fill.on-track{ background:linear-gradient(90deg,#9CFF9F,#CFD2F9); }
    .spr-page .progress-fill.at-risk{ background:linear-gradient(90deg,#FF9A91,#8A2A1F); }
    .spr-page .progress-fill.exceeded{ background:var(--accent-green-dk); }

    .spr-page .status-pill{ font-size:0.78rem; font-weight:700; padding:6px 14px; border-radius:999px; display:inline-block; white-space:nowrap; border:none; }
    .spr-page .status-pill.on-track{ color:var(--accent-green-dk); background:rgba(156,255,159,0.4); }
    .spr-page .status-pill.at-risk{ color:var(--accent-red); background:var(--accent-red-bg); }
    .spr-page .status-pill.exceeded{ color:var(--accent-green-dk); background:rgba(156,255,159,0.4); }

    /* ============ BUTTONS ============ */
    .spr-page .btn{ display:inline-flex; align-items:center; justify-content:center; gap:8px; border:none; cursor:pointer; font-weight:700; font-size:0.85rem; border-radius:999px; padding:10px 22px; letter-spacing:0.2px; font-family:inherit; }
    .spr-page .btn-primary{ background:var(--accent-indigo, #120F34); color:#fff; }
    .spr-page .btn-primary:hover{ opacity:0.92; }
    .spr-page .btn-secondary{ background:#fff; color:var(--ink); border:1px solid rgba(18,15,52,0.14); }
    .spr-page .btn-secondary:hover{ background:var(--bg-lavender); }
    .spr-page .btn-dark{ background:var(--ink); color:#fff; padding:8px 18px; border-radius:999px; font-size:0.72rem; }
    .spr-page .btn-danger{ background:var(--accent-red-bg); color:var(--accent-red); }
    .spr-page .btn-danger:hover{ background:#fbd7d5; }

    /* ============ CUSTOM SELECT (functional dropdown) ============ */
    .spr-page .field-label{ font-size:0.85rem; font-weight:700; color:var(--ink); margin-bottom:8px; display:block; }
    .spr-page .field{ position:relative; }
    .spr-page .select{ position:relative; }
    .spr-page .select-btn{
        width:100%; display:flex; align-items:center; justify-content:space-between;
        background:var(--bg-lavender); border:1px solid var(--accent-lilac); border-radius:12px;
        padding:12px 16px; font-size:0.92rem; font-weight:700; color:var(--ink);
        cursor:pointer; font-family:inherit;
    }
    .spr-page .select-btn svg{ transition:transform 0.15s ease; flex-shrink:0; }
    .spr-page .select.open .select-btn svg{ transform:rotate(180deg); }
    .spr-page .select-menu{
        position:absolute; left:0; right:0; top:calc(100% + 8px);
        background:var(--panel-white); border:1px solid var(--border-soft);
        border-radius:14px; box-shadow:0 16px 36px rgba(18,15,52,0.18);
        padding:8px; z-index:50; display:none; max-height:240px; overflow-y:auto;
    }
    .spr-page .select.open .select-menu{ display:block; }
    .spr-page .select-option{ padding:10px 12px; border-radius:9px; font-size:0.9rem; font-weight:600; color:var(--ink-soft); cursor:pointer; }
    .spr-page .select-option:hover{ background:var(--bg-lavender); color:var(--ink); }
    .spr-page .select-option.selected{ color:var(--accent-indigo-dk); background:var(--accent-lilac); }
    .spr-page .mini-select{ min-width:150px; }
    .spr-page .mini-select .select-btn{ padding:9px 14px; font-size:0.82rem; }

    /* ============ RANGE SLIDER ============ */
    .spr-page .slider-row{ display:flex; align-items:center; gap:26px; padding:14px 0; }
    .spr-page .slider-row label{ width:170px; flex-shrink:0; font-size:0.92rem; font-weight:700; color:var(--ink); }
    .spr-page input[type=range]{ -webkit-appearance:none; appearance:none; flex:1; height:5px; border-radius:4px; background:var(--ink); outline:none; }
    .spr-page input[type=range]::-webkit-slider-thumb{ -webkit-appearance:none; width:20px; height:20px; border-radius:50%; background:var(--accent-lilac); border:3px solid #fff; box-shadow:0 1px 4px rgba(0,0,0,0.3); cursor:pointer; }
    .spr-page input[type=range]::-moz-range-thumb{ width:20px; height:20px; border-radius:50%; background:var(--accent-lilac); border:3px solid #fff; cursor:pointer; }
    .spr-page .slider-value{ width:52px; text-align:right; font-weight:700; font-size:0.88rem; color:var(--accent-indigo-dk); flex-shrink:0; }

    /* ============ SEGMENTED FILTER TABS (Alerts / Targets) ============ */
    .spr-page .seg-tabs{ display:inline-flex; gap:4px; background:var(--bg-lavender); padding:4px; border-radius:12px; }
    .spr-page .seg-tab{ border:none; background:transparent; padding:8px 16px; font-size:0.82rem; font-weight:700; color:var(--muted); border-radius:9px; cursor:pointer; font-family:inherit; }
    .spr-page .seg-tab.active{ background:var(--panel-white); color:var(--accent-indigo-dk); box-shadow:var(--shadow-card); }

    /* ============ ALERTS ============ */
    .spr-page .alert-card{ position:relative; display:flex; gap:16px; padding:20px 22px; border-radius:16px; background:var(--panel-white); border:1px solid var(--border-soft); margin-bottom:14px; }
    .spr-page .alert-icon{ width:38px; height:38px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:1.05rem; }
    .spr-page .alert-icon.critical{ background:var(--accent-red-bg); }
    .spr-page .alert-icon.warning{ background:var(--accent-amber-bg); }
    .spr-page .alert-icon.positive{ background:rgba(156,255,159,0.4); }
    .spr-page .alert-icon.info{ background:var(--accent-blue-bg); }
    .spr-page .alert-body{ flex:1; min-width:0; }
    .spr-page .alert-title{ font-weight:700; font-size:0.98rem; color:var(--ink); margin-bottom:4px; }
    .spr-page .alert-desc{ font-size:0.87rem; color:var(--ink-soft); line-height:1.5; margin-bottom:8px; }
    .spr-page .alert-meta{ font-size:0.76rem; color:var(--muted); margin-bottom:6px; }
    .spr-page .alert-link{ font-size:0.83rem; font-weight:700; color:var(--accent-indigo-dk); }
    .spr-page .alert-link:hover{ text-decoration:underline; }
    .spr-page .unread-dot{ position:absolute; top:20px; right:20px; width:9px; height:9px; border-radius:50%; background:var(--accent-indigo); }

    .spr-page .toggle-switch{ border:none; cursor:pointer; font-size:0.76rem; font-weight:800; padding:6px 16px; border-radius:999px; letter-spacing:0.3px; font-family:inherit; }
    .spr-page .toggle-switch.active{ background:rgba(156,255,159,0.4); color:var(--accent-green-dk); }
    .spr-page .toggle-switch.inactive{ background:var(--bg-lavender); color:var(--muted); }

    .spr-page .settings-row{ display:flex; align-items:center; justify-content:space-between; gap:20px; padding:16px 0; border-bottom:1px solid rgba(18,15,52,0.06); }
    .spr-page .settings-row:last-child{ border-bottom:none; }
    .spr-page .settings-title{ font-weight:700; font-size:0.92rem; color:var(--ink); margin-bottom:3px; }
    .spr-page .settings-sub{ font-size:0.8rem; color:var(--muted); }

    /* ============ MODALS (Alert detail / create-edit form) ============ */
    .spr-page .modal-overlay{ position:fixed; inset:0; z-index:300; background:rgba(18,15,52,0.45); display:none; align-items:center; justify-content:center; padding:20px; }
    .spr-page .modal-overlay.open{ display:flex; }
    .spr-page .modal-card{ background:var(--panel-white); border-radius:22px; width:400px; max-width:100%; box-shadow:0 24px 60px rgba(18,15,52,0.35); padding:28px 28px 24px; position:relative; max-height:88vh; overflow-y:auto; }
    .spr-page .modal-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
    .spr-page .modal-head h3{ margin:0; font-size:1.15rem; font-weight:800; color:var(--ink); }
    .spr-page .modal-close{ border:none; background:var(--bg-lavender); color:var(--ink-soft); width:30px; height:30px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .spr-page .modal-close:hover{ background:var(--accent-lilac); color:var(--accent-indigo-dk); }

    @media (max-width:1180px){
        .spr-page .stat-grid{ grid-template-columns:repeat(2,1fr); }
        .spr-page .content-grid{ grid-template-columns:1fr; }
    }
    @media (max-width:760px){
        .spr-page .stat-grid{ grid-template-columns:1fr; }
        .spr-page table.data-table{ font-size:0.78rem; }
        .spr-page .slider-row{ flex-wrap:wrap; gap:8px; }
        .spr-page .slider-row label{ width:100%; }
    }
</style>

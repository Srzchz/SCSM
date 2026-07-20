{{--
    Topbar — restyled to match the Curema "CRM Overview" top navigation:
    page title + subtitle on the left (kept in sync with whichever sidebar
    item is active), a pill-shaped search field, an Export button, a
    notification bell, and a primary action button.

    Search/Export/notifications are still presentational only — wire up
    real behavior once there's data to query.
--}}
<header class="app-topbar" id="app-topbar">
    <div class="topbar-heading">
        <h1 class="topbar-title" id="topbar-title">Overview</h1>
        <p class="topbar-subtitle" id="topbar-subtitle">Customer health and sales activity at a glance.</p>
    </div>

    <div class="topbar-center">
        <form class="topbar-search" onsubmit="return false;">
            <div class="topbar-search-field">
                <svg class="topbar-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" placeholder="Search customers, emails, orders, ..." class="topbar-search-input">
            </div>
        </form>
    </div>

    <div class="topbar-actions">
        <button type="button" class="topbar-export-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
            Export
        </button>

        <button class="topbar-bell" type="button" aria-label="Notifications">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </button>

        <button type="button" class="topbar-primary-btn" id="topbar-primary-btn">
            <span id="topbar-primary-btn-label">Add Customer</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
        </button>
    </div>
</header>

<style>
    .app-topbar{
        display:flex;
        align-items:center;
        gap:20px;
        flex-wrap:wrap;
        padding:14px 4px 22px;
        margin-bottom:8px;
    }
    .topbar-heading{flex:0 0 auto;min-width:180px;margin-right:auto;}
    .topbar-title{margin:0;font-size:1.4rem;font-weight:800;color:var(--color-text);line-height:1.2;}
    .topbar-subtitle{margin:4px 0 0;font-size:0.85rem;color:var(--color-text-muted);}

    .topbar-center{flex:1 1 320px;min-width:220px;max-width:520px;}
    .topbar-search{width:100%;}
    .topbar-search-field{
        display:flex;
        align-items:center;
        gap:10px;
        background:#ffffff;
        border:1px solid rgba(18,15,52,0.10);
        border-radius:999px;
        padding:10px 16px;
        box-shadow:0 6px 18px rgba(18,15,52,0.04);
    }
    .topbar-search-icon{width:18px;height:18px;color:var(--color-text-muted);flex-shrink:0;}
    .topbar-search-input{
        border:none;
        outline:none;
        background:transparent;
        flex:1;
        min-width:0;
        font-family:inherit;
        font-size:0.9rem;
        color:var(--color-text);
    }
    .topbar-search-input::placeholder{color:var(--color-text-muted);}

    .topbar-actions{display:flex;align-items:center;gap:10px;flex:0 0 auto;}

    .topbar-export-btn{
        display:flex;align-items:center;gap:8px;
        background:#ffffff;
        border:1px solid rgba(18,15,52,0.10);
        border-radius:999px;
        padding:10px 16px;
        font-weight:700;
        font-size:0.85rem;
        color:var(--color-text);
        cursor:pointer;
    }
    .topbar-export-btn svg{width:16px;height:16px;}
    .topbar-export-btn:hover{background:var(--color-bg);}

    .topbar-bell{
        display:flex;align-items:center;justify-content:center;
        width:40px;height:40px;
        border-radius:999px;
        border:1px solid rgba(18,15,52,0.10);
        background:#ffffff;
        cursor:pointer;
        flex-shrink:0;
    }
    .topbar-bell svg{width:18px;height:18px;color:var(--color-text);}
    .topbar-bell:hover{background:var(--color-bg);}

    .topbar-primary-btn{
        display:flex;align-items:center;gap:10px;
        background:var(--color-primary);
        color:#ffffff;
        border:none;
        border-radius:999px;
        padding:10px 18px;
        font-weight:700;
        font-size:0.85rem;
        cursor:pointer;
        white-space:nowrap;
    }
    .topbar-primary-btn svg{width:14px;height:14px;opacity:0.8;}
    .topbar-primary-btn:hover{opacity:0.92;}
    .topbar-primary-btn[hidden]{display:none;}

    @media (max-width: 720px){
        .app-topbar{gap:12px;}
        .topbar-heading{flex-basis:100%;order:0;}
        .topbar-center{order:2;flex-basis:100%;max-width:none;}
        .topbar-actions{order:1;margin-left:auto;}
        .topbar-export-btn span{display:none;}
    }
</style>

<script>
    (function () {
        // Kept in sync with the sidebar's active tab so the header reads
        // like a real page title (Curema-style) even though this is a
        // client-side toggled SPA rather than separate page loads.
        var topbarMeta = {
            'overview': { title: 'Overview', subtitle: 'Customer health and sales activity at a glance.', action: 'Add Customer' },
            'cases': { title: 'Cases', subtitle: 'Support tickets, SLAs, and service collaboration.', action: 'New Case' },
            'warranty': { title: 'Warranty', subtitle: 'Claims, coverage, and decisions.', action: 'New Claim' },
            'sales-order': { title: 'Sales Order', subtitle: 'Track orders from placement through fulfillment.', action: 'New Order' },
            'customer-relation': { title: 'Customer Relation', subtitle: 'Profiles, segments, and engagement history.', action: 'Add Customer' },
            'sales-report': { title: 'Sales Report', subtitle: 'Revenue performance across products and channels.', action: 'Export Report' },
            'account': { title: 'Account', subtitle: 'Your profile, role, and recent activity.', action: null },
            'settings': { title: 'Settings', subtitle: 'Workspace preferences, notifications, and data.', action: null }
        };

        var titleEl = document.getElementById('topbar-title');
        var subtitleEl = document.getElementById('topbar-subtitle');
        var primaryBtn = document.getElementById('topbar-primary-btn');
        var primaryLabel = document.getElementById('topbar-primary-btn-label');

        function applyMeta(key) {
            var meta = topbarMeta[key];
            if (!meta || !titleEl || !subtitleEl) return;

            titleEl.textContent = meta.title;
            subtitleEl.textContent = meta.subtitle;

            if (primaryBtn && primaryLabel) {
                if (meta.action) {
                    primaryLabel.textContent = meta.action;
                    primaryBtn.hidden = false;
                } else {
                    primaryBtn.hidden = true;
                }
            }
        }

        // Set initial state from whichever nav item is already active.
        var activeItem = document.querySelector('.nav-item.active');
        applyMeta(activeItem ? activeItem.getAttribute('data-target') : 'overview');

        // Update whenever a sidebar item is clicked. This only touches the
        // topbar text — the actual section show/hide logic still lives in
        // app.js and is untouched here.
        document.addEventListener('click', function (e) {
            var item = e.target.closest('.nav-item[data-target]');
            if (item) applyMeta(item.getAttribute('data-target'));
        });
    })();
</script>

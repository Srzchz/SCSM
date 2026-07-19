{{--
    Sidebar navigation.

    Each <li> is a "tab" for the SPA, not a link to a new page.
    - data-target must match a <section id="..."> in a sections/*.blade.php file
    - app.js still owns click-to-toggle behavior at runtime
    - $active (falling back to $default, then 'overview') sets which item
      is marked active on the initial page render, so a redirect back to
      ?section=cases lands with Cases already highlighted server-side,
      instead of always defaulting to Overview.
    Add new items by copying an <li> and pointing data-target at a new section key.
--}}
@php
    $active = $active ?? ($default ?? 'overview');
@endphp
<aside class="app-sidebar">

    <div class="sidebar-logo">
        <div class="sidebar-logo-mark">
            <svg fill="currentColor" viewBox="0 0 24 24">
                <path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
        </div>
        <span class="sidebar-logo-text">ask.ist</span>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-group">
            <li class="nav-item {{ $active === 'overview' ? 'active' : '' }}" data-target="overview">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16M4 14h16"/>
                </svg>
                <span class="nav-label">Overview</span>
            </li>
        </ul>

        <ul class="nav-group">
            <li class="nav-item {{ $active === 'cases' ? 'active' : '' }}" data-target="cases">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="nav-label">Cases</span>
            </li>
            <li class="nav-item {{ $active === 'warranty' ? 'active' : '' }}" data-target="warranty">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="nav-label">Warranty</span>
            </li>
        </ul>

        <ul class="nav-group">
            <li class="nav-item {{ $active === 'sales-order' ? 'active' : '' }}" data-target="sales-order">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span class="nav-label">Sales Order</span>
            </li>
            <li class="nav-item {{ $active === 'customer-relation' ? 'active' : '' }}" data-target="customer-relation">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 19H9a6 6 0 016-6h0a6 6 0 016 6v1"/>
                </svg>
                <span class="nav-label">Customer Relation</span>
            </li>
            <li class="nav-item {{ $active === 'sales-report' ? 'active' : '' }}" data-target="sales-report">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="nav-label">Sales Report</span>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <ul class="nav-group">
            <li class="nav-item {{ $active === 'account' ? 'active' : '' }}" data-target="account">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="nav-label">Account</span>
            </li>
            <li class="nav-item {{ $active === 'settings' ? 'active' : '' }}" data-target="settings">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="nav-label">Settings</span>
            </li>
        </ul>
    </div>

</aside>

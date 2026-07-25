{{--
    Section: Customer Relation
    id="customer-relation" on the parent <section> (set in spa.blade.php) is
    what app.js looks for when the "Customer Relation" sidebar item is
    clicked.

    The real, fully-built Customer Relation page lives at its own route
    (customer-relationship-management/customers, via CustomerController).
    This SPA section is not used for that anymore — no auto-redirect here,
    since scripts in every section execute on page load regardless of
    [hidden] state, which was causing an unwanted redirect on every visit
    to /dashboard. Link instead.
--}}

<div class="cr-placeholder">
    <p>Customer management now lives on its own page.</p>
    <a href="{{ route('customers.index') }}" class="btn btn-primary">Go to Customers →</a>
</div>

<style>
    .cr-placeholder{display:flex;flex-direction:column;align-items:flex-start;gap:12px;padding:24px;}
    .cr-placeholder .btn{padding:9px 16px;border-radius:12px;font-size:0.85rem;font-weight:700;cursor:pointer;background:#120F34;color:#fff;text-decoration:none;display:inline-block;}
</style>
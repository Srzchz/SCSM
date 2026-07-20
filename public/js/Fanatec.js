/* ══════════════════════════ DATA (loaded from API) ══════════════════════════ */

const API = '/sales-order-management/api';

let PRODUCTS = [];
let CUSTOMERS = [];

const QF = ['Draft', 'Sent', 'Accepted']; // quotation happy-path flow (Rejected/Expired are terminal)
const OF = ['Pending', 'Processing', 'Shipped', 'Delivered']; // order_status flow (Cancelled is terminal)

const VALID_TABS = ['quotations', 'orders', 'pricing', 'invoicing'];
const initialTab = new URLSearchParams(window.location.search).get('tab');

let state = {
  tab: VALID_TABS.includes(initialTab) ? initialTab : 'quotations', view: null, activeId: null, openMod: null,
  filterText: '', modal: null, draft: null,
  toast: null, toastTimer: null,
  quotations: [], orders: [], invoices: [], pricingRules: [],
  taxRegions: [],
  loading: true, loadError: null,
};

function money(n) { return '₱' + Math.round(n).toLocaleString('en-PH'); }
function StatCards(items) {
  // items: [{lbl, val, sub, acc, id}]  acc = CSS color for the left accent bar
  // id (optional) makes the card clickable — attachEvents() wires up #id.onclick
  return `<div class="stats">
    ${items.map(s => `<div class="stat ${s.id ? 'stat-clickable' : ''}" ${s.id ? `id="${s.id}"` : ''} style="--acc:${s.acc || 'var(--red)'}">
      <div class="stat-lbl">${s.lbl}</div>
      <div class="stat-val">${s.val}</div>
      <div class="stat-sub">${s.sub}</div>
    </div>`).join('')}
  </div>`;
}
function activeDiscountRules() {
  return (state.pricingRules || []).filter(r => r.status === 'Active');
}
// For a Fixed Amount rule, convert its peso value into an equivalent % for this specific line
// (discount_percent is the only field the schema supports per line item).
function ruleToPercent(rule, product, quantity) {
  if (rule.type === 'Percentage') return rule.value;
  const lineTotal = (product?.unit_price || 0) * (quantity || 1);
  if (!lineTotal) return 0;
  return Math.min(100, Math.round((rule.value / lineTotal) * 10000) / 100);
}
function ruleLabel(rule, product, quantity) {
  if (rule.type === 'Percentage') return `${rule.name} — ${rule.value}%`;
  return `${rule.name} — ${money(rule.value)} off (${ruleToPercent(rule, product, quantity)}%)`;
}

async function api(path, opts) {
  const res = await fetch(API + path, Object.assign({
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
  }, opts));
  if (!res.ok) {
    const body = await res.text();
    throw new Error('API ' + res.status + ': ' + body);
  }
  return res.status === 204 ? null : res.json();
}

async function loadBootstrap() {
  state.loading = true; state.loadError = null; render();
  try {
    const data = await api('/bootstrap');
    PRODUCTS  = data.products;
    CUSTOMERS = data.customers;
    state.quotations   = data.quotations;
    state.orders       = data.orders;
    state.invoices      = data.invoices;
    state.pricingRules = data.pricingRules;
    state.taxRegions   = data.taxRegions;
  } catch (err) {
    state.loadError = err.message;
  } finally {
    state.loading = false;
    render();
  }
}

/* Re-fetch everything after any mutating action — keeps the UI in sync
   with the DB without hand-merging partial responses. */
async function refresh() {
  const data = await api('/bootstrap');
  state.quotations    = data.quotations;
  state.orders        = data.orders;
  state.invoices       = data.invoices;
  state.pricingRules  = data.pricingRules;
  state.taxRegions    = data.taxRegions;
  render();
}

function showToast(ico, msg) {
  if (state.toastTimer) clearTimeout(state.toastTimer);
  state.toast = { ico, msg };
  render();
  state.toastTimer = setTimeout(() => { state.toast = null; render(); }, 2800);
}

/* ══════════════════════════ RENDER ══════════════════════════ */
const root = document.getElementById('root');
function render() { root.innerHTML = Layout(); attachEvents(); }

function qbdg(s) { return `<span class="badge b-${s.toLowerCase()}"><span class="dot"></span>${s}</span>`; }
function obdg(s) { return `<span class="badge b-${s.toLowerCase()}"><span class="dot"></span>${s}</span>`; }
// Orders can be on hold independent of their order_status (Pending/Processing/Shipped),
// so the badge shown to the user should reflect the hold, not the underlying status.
function orderBadge(o) { return o.held ? obdg('Hold') : obdg(o.status); }
function ibdg(s) { return `<span class="badge b-${s}"><span class="dot"></span>${s}</span>`; }
function pbdg(s) { return `<span class="badge b-${s}"><span class="dot"></span>${s}</span>`; }

function openInvoicePdf(invoice) {
  const order = state.orders.find(o => o.id === invoice.orderId);
  const win = window.open('', '_blank');
  if (!win) { showToast('⚠️', 'Popup blocked — allow popups to view the invoice.'); return; }
  win.document.write(`<!doctype html><html><head><title>${invoice.id} — ${invoice.customer}</title>
    <style>
      body{font-family:'Rajdhani',Arial,sans-serif;padding:40px;color:#120F34;max-width:720px;margin:0 auto;}
      h1{font-family:'Oswald',sans-serif;font-size:22px;margin:0 0 4px;}
      .sub{color:#4D4A68;font-size:13px;margin-bottom:24px;}
      table{width:100%;border-collapse:collapse;margin:18px 0;}
      th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#8884A3;padding:6px 8px;border-bottom:1px solid #C3C3C3;}
      td{padding:8px;border-bottom:1px solid #E9EBFC;font-size:14px;}
      .totals{margin-left:auto;max-width:280px;}
      .totals div{display:flex;justify-content:space-between;padding:4px 0;font-size:13px;}
      .grand{font-weight:700;font-size:16px;border-top:1px solid #C3C3C3;margin-top:6px;padding-top:8px;}
    </style></head><body>
    <h1>${invoice.customer}</h1>
    <div class="sub">${invoice.id} · Order ${invoice.orderId} · Issued ${invoice.date} · Due ${invoice.due}</div>
    <table>
      <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Line Total</th></tr></thead>
      <tbody>
        ${order ? order.items.map(i => `<tr><td>${i.product}</td><td>${i.qty}</td><td>${money(i.price)}</td><td>${money(i.lineTotal)}</td></tr>`).join('') : ''}
      </tbody>
    </table>
    <div class="totals">
      <div>Subtotal <b>${money(invoice.subtotal)}</b></div>
      <div>VAT (${order && order.taxRatePct != null ? order.taxRatePct.toFixed(2) : '—'}%) <b>${money(invoice.vat)}</b></div>
      <div class="grand">Total <b>${money(invoice.total)}</b></div>
    </div>
    <p style="margin-top:32px;color:#8884A3;font-size:11px;">Status: ${invoice.status}</p>
    <script>window.onload=()=>window.print();</script>
  </body></html>`);
  win.document.close();
}

/* ── Layout ── */
function Layout() {
  if (state.loading) {
    return `<div class="empty" style="padding:80px 20px;font-size:13px;">Loading Sales Order Management…</div>`;
  }
  if (state.loadError) {
    return `<div class="empty" style="padding:80px 20px;font-size:13px;color:var(--alert);">
      Could not reach the API: ${state.loadError}<br><br>
      <button class="btn primary sm" id="retryLoad">Retry</button>
    </div>`;
  }
  const draftCount = state.quotations.filter(q => q.status === 'Draft').length;
  const titles = {
    quotations: 'Sales Quotations',
    orders: 'Sales Orders',
    pricing: 'Pricing Rules',
    invoicing: 'Invoicing',
  };

  return `
  <div class="app">
    <!-- HEADER -->
    <div class="topbar">
      <div class="topbar-l">
        ${state.view === 'detail'
          ? `<button class="btn ghost sm" id="backBtn">← Back</button>
             <span class="topbar-crumb">/ ${state.activeId}</span>`
          : `<h1>${titles[state.tab]}</h1>`}
      </div>
      <span class="topbar-date">${new Date().toDateString().toUpperCase()}</span>
    </div>

    <div class="content">
      ${state.view === 'detail' ? DetailView() : TabView()}
    </div>

    ${state.modal ? Modal() : ''}
    ${state.toast ? `<div class="toast"><span>${state.toast.ico}</span>${state.toast.msg}</div>` : ''}
  </div>`;
}

/* ── Tabs ── */
function TabView() {
  if (state.tab === 'quotations') return QuotationsTab();
  if (state.tab === 'orders') return OrdersTab();
  if (state.tab === 'pricing') return PricingTab();
  if (state.tab === 'invoicing') return InvoicingTab();
  return '';
}

function QuotationsTab() {
  const rows = state.quotations.filter(q =>
    !state.filterText || q.customer.toLowerCase().includes(state.filterText.toLowerCase()) || q.id.toLowerCase().includes(state.filterText.toLowerCase())
  );
  const openQuotes = state.quotations.filter(q => q.status === 'Draft' || q.status === 'Sent').length;
  const activeOrders = state.orders.filter(o => o.status !== 'Delivered' && o.status !== 'Cancelled').length;
  const pipelineValue = state.orders.filter(o => o.status !== 'Cancelled').reduce((s, o) => s + o.total, 0)
    + state.quotations.filter(q => q.status === 'Draft' || q.status === 'Sent').reduce((s, q) => s + q.total, 0);
  return StatCards([
    { lbl: 'Open Quotes', val: openQuotes, sub: 'awaiting confirmation', acc: 'var(--secondary)' },
    { lbl: 'Active Orders', val: activeOrders, sub: 'in fulfillment pipeline', acc: 'var(--rb)' },
    { lbl: 'Pipeline Value', val: money(pipelineValue), sub: 'incl. VAT', acc: 'var(--vip)' },
    { lbl: 'Customers', val: CUSTOMERS.length, sub: 'active accounts', acc: 'var(--accent)' },
  ]) + `
  <div class="panel">
    <div class="panel-hd">
      <input id="searchBox" class="search" placeholder="Search quotations…" value="${state.filterText}">
      <button class="btn primary" id="newQuoteBtn">+ New Quotation</button>
    </div>
    <table>
      <thead><tr><th>Quotation</th><th>Customer</th><th>Date</th><th>Valid Until</th><th>Total</th><th>Status</th><th></th></tr></thead>
      <tbody>
      ${rows.map(q => `<tr class="row" data-view="q:${q.quotationId}">
        <td class="mono">${q.id}</td>
        <td>${q.customer}</td>
        <td class="mono">${q.date}</td>
        <td class="mono">${q.validUntil}</td>
        <td class="mono">${money(q.total)}</td>
        <td>${qbdg(q.status)}</td>
        <td><button class="btn ghost sm" data-vb="q:${q.quotationId}">View</button></td>
      </tr>`).join('') || `<tr><td colspan="7" class="empty">No quotations yet.</td></tr>`}
      </tbody>
    </table>
  </div>`;
}

function OrdersTab() {
  const rows = state.orders.filter(o =>
    !state.filterText || o.customer.toLowerCase().includes(state.filterText.toLowerCase()) || o.id.toLowerCase().includes(state.filterText.toLowerCase())
  );
  const inPipeline = state.orders.filter(o => ['Pending', 'Processing', 'Shipped'].includes(o.status)).length;
  const onHold = state.orders.filter(o => o.held).length;
  const delivered = state.orders.filter(o => o.status === 'Delivered').length;
  return StatCards([
    { lbl: 'In Pipeline', val: inPipeline, sub: 'active fulfillment', acc: 'var(--accent)' },
    { lbl: 'On Hold', val: onHold, sub: 'requires action', acc: 'var(--accent)' },
    { lbl: 'Delivered', val: delivered, sub: 'fulfilled', acc: 'var(--rb)' },
    { lbl: 'Total Tracked', val: state.orders.length, sub: 'confirmed & beyond', acc: 'var(--accent)' },
  ]) + `
  <div class="panel">
    <div class="panel-hd">
      <input id="searchBox" class="search" placeholder="Search orders…" value="${state.filterText}">
    </div>
    <table>
      <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
      <tbody>
      ${rows.map(o => `<tr class="row" data-view="o:${o.orderId}">
        <td class="mono">${o.id}</td>
        <td>${o.customer}</td>
        <td class="mono">${o.date}</td>
        <td class="mono">${money(o.total)}</td>
        <td>${orderBadge(o)}</td>
        <td><button class="btn ghost sm" data-vb="o:${o.orderId}">View</button></td>
      </tr>`).join('') || `<tr><td colspan="6" class="empty">No orders yet. Accept a quotation to create one.</td></tr>`}
      </tbody>
    </table>
  </div>`;
}

function PricingTab() {
  const pctVals = state.pricingRules.filter(r => r.type === 'Percentage').map(r => r.value);
  const tierRange = pctVals.length ? `${Math.min(...pctVals)}% to ${Math.max(...pctVals)}%` : '—';
  const prices = PRODUCTS.map(p => p.unit_price);
  const priceRange = prices.length ? money(Math.min(...prices)) : '—';
  return StatCards([
    { lbl: 'Products', val: PRODUCTS.length, sub: 'active catalog', acc: 'var(--accent)', id: 'productsCard' },
    { lbl: 'Discount Tiers', val: state.pricingRules.length, sub: tierRange, acc: 'var(--rb)' },
    { lbl: 'Tax Region', val: (() => { const def = state.taxRegions.find(r => r.isDefault) || state.taxRegions[0]; return def ? `${def.vat}%` : '—'; })(), sub: (() => { const def = state.taxRegions.find(r => r.isDefault) || state.taxRegions[0]; return def ? `${def.country} (default)` : 'No regions'; })(), acc: 'var(--vip)', id: 'taxRegionCard' },
    { lbl: 'Price Range', val: priceRange, sub: prices.length ? `to ${money(Math.max(...prices))}` : '', acc: 'var(--secondary)' },
  ]) + `
  <div class="panel">
    <div class="panel-hd">
      <span></span>
      <button class="btn primary" id="newRuleBtn">+ New Pricing Rule</button>
    </div>
    <table>
      <thead><tr><th>Rule</th><th>Type</th><th>Value</th><th>Applies To</th><th>Start</th><th>End</th><th>Status</th><th></th><th></th></tr></thead>
      <tbody>
      ${state.pricingRules.map(r => `<tr>
        <td>${r.name}</td>
        <td>${r.type}</td>
        <td class="mono">${r.type === 'Percentage' ? r.value + '%' : money(r.value)}</td>
        <td>${r.applicableTo}</td>
        <td class="mono">${r.startDate || '—'}</td>
        <td class="mono">${r.endDate || '—'}</td>
        <td>${pbdg(r.status)}</td>
        <td><button class="btn ghost sm" data-toggle-rule="${r.id}">${r.status === 'Active' ? 'Deactivate' : 'Activate'}</button></td>
        <td><button class="btn ghost sm" data-remove-rule="${r.id}">Remove</button></td>
      </tr>`).join('') || `<tr><td colspan="9" class="empty">No pricing rules yet.</td></tr>`}
      </tbody>
    </table>
  </div>`;
}

function InvoicingTab() {
  const paid = state.invoices.filter(i => i.status === 'Paid');
  const outstanding = state.invoices.filter(i => i.status !== 'Paid');
  const paidTotal = paid.reduce((s, i) => s + i.total, 0);
  const outstandingTotal = outstanding.reduce((s, i) => s + i.total, 0);
  const ordersWithInvoice = state.orders.filter(o => o.hasInvoice).length;
  const stockReserved = state.orders
    .filter(o => o.status !== 'Delivered' && o.status !== 'Cancelled')
    .reduce((s, o) => s + o.items.reduce((s2, it) => s2 + it.qty, 0), 0);
  const pendingSyncOrders = state.orders.filter(o => !o.hasInvoice).length;
  const arEntries = state.invoices.length;
  const ledgerPending = outstanding.length;

  return StatCards([
    { lbl: 'Paid', val: paid.length, sub: `${money(paidTotal)} collected`, acc: 'var(--rb)' },
    { lbl: 'Outstanding', val: outstanding.length, sub: `${money(outstandingTotal)} due`, acc: 'var(--secondary)' },
    { lbl: 'Inv. Synced', val: `${ordersWithInvoice}/${state.orders.length}`, sub: 'orders → inventory', acc: 'var(--accent)' },
    { lbl: 'Fin. Synced', val: `${paid.length}/${state.invoices.length}`, sub: 'orders → finance', acc: 'var(--rb)' },
  ]) + `
  <div class="panel">
    <div class="panel-hd"><span>Invoices generated from confirmed Sales Orders</span></div>
    <table>
      <thead><tr><th>Invoice</th><th>Order</th><th>Customer</th><th>Total</th><th>Due</th><th>Status</th><th></th></tr></thead>
      <tbody>
      ${state.invoices.map(i => `<tr>
        <td class="mono">${i.id}</td>
        <td class="mono">${i.orderId}</td>
        <td>${i.customer}</td>
        <td class="mono" style="color:var(--purple);">${money(i.total)}</td>
        <td class="mono">${i.due}</td>
        <td>${ibdg(i.status)}</td>
        <td>${i.status !== 'Paid'
          ? `<button class="btn ghost sm" data-markpaid="${i.invoiceId}">Mark Paid</button>`
          : `<button class="btn ghost sm" data-view-pdf="${i.invoiceId}">View PDF</button>`}</td>
      </tr>`).join('') || `<tr><td colspan="7" class="empty">No invoices yet. Generate one from a Sales Order.</td></tr>`}
      </tbody>
    </table>
  </div>

  <div class="sync-grid">
    <div class="sync-card" style="--acc:var(--btext-blue)">
      <div class="sync-card-top"><h3>Inventory Module</h3><span class="badge-sync">MOCK SYNC</span></div>
      <div class="sync-card-body">
        <div><div class="sync-lbl">Stock Reserved</div><div class="sync-val">${stockReserved} units</div></div>
        <div><div class="sync-lbl">Pending Sync</div><div class="sync-val">${pendingSyncOrders} orders</div></div>
        <div><div class="sync-lbl">Link Status</div><div class="sync-val dot-active">● ACTIVE</div></div>
      </div>
      <div class="sync-card-actions">
        <button class="btn primary sm" data-sync="inventory">Sync Inventory</button>
        <button class="btn ghost sm" data-sync-log="inventory">View Stock Log</button>
      </div>
    </div>
    <div class="sync-card" style="--acc:var(--rb)">
      <div class="sync-card-top"><h3>Finance Module</h3><span class="badge-sync">MOCK SYNC</span></div>
      <div class="sync-card-body">
        <div><div class="sync-lbl">AR Entries</div><div class="sync-val">${arEntries} invoices</div></div>
        <div><div class="sync-lbl">Ledger Pending</div><div class="sync-val">${ledgerPending} entries</div></div>
        <div><div class="sync-lbl">Link Status</div><div class="sync-val dot-active">● ACTIVE</div></div>
      </div>
      <div class="sync-card-actions">
        <button class="btn primary sm" data-sync="finance">Post Pending</button>
        <button class="btn ghost sm" data-sync-log="finance">View Ledger</button>
      </div>
    </div>
  </div>
  <div class="hint" style="margin-top:8px;">Inventory &amp; Finance are separate modules in this ERP — the panels above are a mocked preview of that hand-off, not a live integration.</div>`;
}

/* ── Detail view (quotation or order) ── */
function DetailView() {
  const [type, id] = state.activeId.split(':');
  if (type === 'q') return QuotationDetail(state.quotations.find(q => q.quotationId == id));
  if (type === 'o') return OrderDetail(state.orders.find(o => o.orderId == id));
  return `<div class="empty">Not found.</div>`;
}

function QuotationDetail(q) {
  if (!q) return `<div class="empty">Quotation not found.</div>`;
  return `
  <div class="panel detail">
    <h2>${q.id} — ${q.customer}</h2>
    <div class="detail-meta">
      <span>Date: <b>${q.date}</b></span>
      <span>Valid Until: <b>${q.validUntil}</b></span>
      <span>Tax Region: <b>${q.taxCountry || '—'}</b></span>
      <span>Status: ${qbdg(q.status)}</span>
    </div>
    <table>
      <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Discount %</th><th>Line Total</th></tr></thead>
      <tbody>
      ${q.items.map(i => `<tr>
        <td>${i.product}</td><td class="mono">${i.qty}</td><td class="mono">${money(i.price)}</td>
        <td class="mono">${i.discount || 0}%</td><td class="mono">${money(i.lineTotal)}</td>
      </tr>`).join('')}
      </tbody>
    </table>
    <div class="totals">
      <div>Subtotal <b>${money(q.subtotal)}</b></div>
      <div>Discount <b>-${money(q.discount)}</b></div>
      <div>Tax (VAT ${q.taxRatePct != null ? q.taxRatePct.toFixed(2) : '—'}%) <b>${money(q.tax)}</b></div>
      <div class="grand">Total <b>${money(q.total)}</b></div>
    </div>
    <div class="detail-actions">
      ${q.status === 'Draft' ? `<button class="btn ghost" data-send-quote="${q.quotationId}">Send to Customer</button>` : ''}
      ${q.status === 'Sent' ? `<button class="btn ghost" data-reject-quote="${q.quotationId}">Reject</button>
        <button class="btn primary" data-accept-quote="${q.quotationId}">Accept → Create Order</button>` : ''}
    </div>
  </div>`;
}

function OrderDetail(o) {
  if (!o) return `<div class="empty">Order not found.</div>`;
  const idx = OF.indexOf(o.status);
  const canAdvance = idx !== -1 && idx < OF.length - 1 && !o.held;
  const canHold = o.status !== 'Cancelled' && o.status !== 'Delivered';
  const RAIL = ['Quote', 'Confirmed', 'In Production', 'Shipped', 'Delivered'];
  // an order only exists once its quotation was accepted, so "Quote" is always done;
  // the rest map 1:1 onto order_status (Pending/Processing/Shipped/Delivered)
  const railIdx = o.status === 'Cancelled' ? -1 : (idx === -1 ? 0 : idx + 1);
  const invoice = state.invoices.find(i => i.orderId === o.id);
  const productNames = o.items.map(i => i.product).join(', ');

  return `
  <div class="receipt-head">
    <h1>${o.customer.toUpperCase()}</h1>
    ${o.customerAddress ? `<div class="receipt-addr">${o.customerAddress}</div>` : ''}
    <div class="receipt-sub">${o.id} · Created ${o.date} · Philippines</div>
  </div>

  <div class="panel receipt-status">
    <div class="receipt-status-row">
      <div>
        <b>${o.customer} · ${o.id}</b>
        <div class="hint">${productNames}</div>
      </div>
      <div class="receipt-status-right">
        <div class="mono receipt-amt">${money(o.total)}</div>
        ${orderBadge(o)}
      </div>
    </div>
    ${o.status === 'Cancelled' ? '' : `
    <div class="rail-wrap">
      <div class="rail-line"></div>
      <div class="rail">
        ${RAIL.map((step, i) => `
          <div class="rail-step ${i < railIdx ? 'done' : ''} ${i === railIdx ? 'current' : ''}">
            <div class="circ">${i < railIdx ? '✓' : i + 1}</div>
            <div class="lbl">${step}</div>
          </div>`).join('')}
      </div>
    </div>`}
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Pricing, Discount &amp; Tax Breakdown</h2><span class="hint">${o.taxCountry || '—'} VAT ${o.taxRatePct != null ? o.taxRatePct.toFixed(2) : '—'}%</span></div>
    <div class="panel-body">
      <table>
        <thead><tr><th>Product ID</th><th>Product Name</th><th>Qty</th><th>Unit Price</th></tr></thead>
        <tbody>
        ${o.items.map(i => `<tr>
          <td class="mono">P-${String(i.productId ?? '').padStart(4, '0')}</td>
          <td>${i.product}</td>
          <td class="mono">${i.qty}</td>
          <td class="mono">${money(i.price)}</td>
        </tr>`).join('')}
        </tbody>
      </table>
      <div class="totals">
        <div>Subtotal <b>${money(o.subtotal)}</b></div>
        <div class="disc">Discount <b>-${money(o.discount)}</b></div>
        <div>Taxable Amount <b>${money(o.subtotal - o.discount)}</b></div>
        <div>VAT (${o.taxRatePct != null ? o.taxRatePct.toFixed(2) : '—'}%) <b>${money(o.tax)}</b></div>
        ${o.shipping ? `<div>Shipping <b>${money(o.shipping)}</b></div>` : ''}
        <div class="grand">Grand Total <b>${money(o.total)}</b></div>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Invoice</h2>${invoice ? ibdg(invoice.status) : ''}</div>
    <div class="panel-body">
      ${invoice ? `
      <table>
        <thead><tr><th>Invoice ID</th><th>Issued</th><th>Due</th><th>Amount</th><th></th></tr></thead>
        <tbody>
          <tr>
            <td class="mono">${invoice.id}</td>
            <td class="mono">${invoice.date}</td>
            <td class="mono">${invoice.due}</td>
            <td class="mono">${money(invoice.total)}</td>
            <td><button class="btn ghost sm" data-view-pdf="${invoice.invoiceId}">View PDF</button></td>
          </tr>
        </tbody>
      </table>` : `<div class="hint">No invoice generated yet for this order.</div>`}
    </div>
  </div>

  <div class="detail-actions">
    ${canAdvance ? `<button class="btn primary" data-advance="${o.orderId}">→ ${OF[idx + 1]}</button>` : ''}
    ${o.held
      ? `<button class="btn ghost" data-resume="${o.orderId}">Resume</button>`
      : (canHold ? `<button class="btn ghost" data-hold="${o.orderId}">Hold</button>` : '')}
    ${o.status !== 'Cancelled' && o.status !== 'Delivered' ? `<button class="btn ghost" data-cancel="${o.orderId}">Cancel Order</button>` : ''}
    ${!o.hasInvoice ? `<button class="btn primary" data-gen-invoice="${o.orderId}">Generate Invoice</button>` : ''}
  </div>`;
}

/* ── New Quotation Modal ── */
function Modal() {
  if (state.modal.type === 'new-quote') return NewQuoteModal();
  if (state.modal.type === 'new-rule') return NewRuleModal();
  if (state.modal.type === 'product-catalog') return ProductCatalogModal();
  if (state.modal.type === 'tax-region') return TaxRegionModal();
  return '';
}

function NewQuoteModal() {
  const d = state.draft;
  return `
  <div class="modal-bg" id="modalBg">
    <div class="modal">
      <h3>New Sales Quotation</h3>
      <label>Customer</label>
      <input id="draftCust" value="${d.customer}" placeholder="Customer name">
      <label>Valid Until</label>
      <input id="draftValid" type="date" value="${d.validUntil}">
      <label>Tax Region</label>
      <select id="draftTaxRegion">
        ${state.taxRegions.map(r => `<option value="${r.id}" ${d.tax_region_id == r.id ? 'selected' : ''}>${r.country} — ${r.vat}% VAT${r.isDefault ? ' (default)' : ''}</option>`).join('')}
      </select>
      <div class="line-items">
        <div class="line-item-head">
          <span>Product</span><span>Qty</span><span>Discount</span><span></span>
        </div>
        ${d.items.map((it, ix) => {
          const rules = activeDiscountRules();
          const product = PRODUCTS.find(p => p.product_id == it.product_id);
          const matched = rules.find(r => ruleToPercent(r, product, it.quantity) == it.discount_percent);
          const isCustom = it.discSource === 'custom' || (!it.discSource && !matched);
          const selVal = isCustom ? 'custom' : (it.discSource || (matched ? matched.id : 'custom'));
          return `
          <div class="line-item">
            <select data-product="${ix}">
              ${PRODUCTS.map(p => `<option value="${p.product_id}" ${p.product_id == it.product_id ? 'selected' : ''}>${p.name} — ${money(p.unit_price)}</option>`).join('')}
            </select>
            <input type="number" min="1" value="${it.quantity}" data-qty="${ix}" style="width:70px;" title="Quantity">
            <div class="disc-cell">
              <select data-disc-rule="${ix}" title="Discount">
                <option value="custom" ${isCustom ? 'selected' : ''}>Custom %</option>
                ${rules.map(r => `<option value="${r.id}" ${!isCustom && selVal == r.id ? 'selected' : ''}>${ruleLabel(r, product, it.quantity)}</option>`).join('')}
              </select>
              <input type="number" min="0" max="100" value="${it.discount_percent}" data-disc="${ix}" placeholder="Disc %" title="Discount %" style="${isCustom ? '' : 'display:none;'}">
            </div>
            <button class="btn ghost sm" data-rem="${ix}">✕</button>
          </div>`;
        }).join('')}
      </div>
      <button class="btn ghost sm" id="addLI">+ Add line item</button>
      <div class="modal-actions">
        <button class="btn ghost" id="cancelD">Cancel</button>
        <button class="btn primary" id="saveD">Save Quotation</button>
      </div>
    </div>
  </div>`;
}

function NewRuleModal() {
  const d = state.draft;
  return `
  <div class="modal-bg" id="modalBg">
    <div class="modal">
      <h3>New Pricing Rule</h3>
      <label>Rule Name</label>
      <input id="ruleName" value="${d.rule_name}">
      <label>Type</label>
      <select id="ruleType">
        <option value="Percentage" ${d.rule_type === 'Percentage' ? 'selected' : ''}>Percentage</option>
        <option value="Fixed Amount" ${d.rule_type === 'Fixed Amount' ? 'selected' : ''}>Fixed Amount</option>
      </select>
      <label>Discount Value</label>
      <input id="ruleValue" type="number" min="0" value="${d.discount_value}">
      <label>Applicable To</label>
      <select id="ruleApplicable">
        ${['Product', 'Category', 'Customer Segment', 'Order-wide'].map(v => `<option value="${v}" ${d.applicable_to === v ? 'selected' : ''}>${v}</option>`).join('')}
      </select>
      <label>Start Date</label>
      <input id="ruleStart" type="date" value="${d.start_date}">
      <label>End Date (optional)</label>
      <input id="ruleEnd" type="date" value="${d.end_date || ''}">
      <div class="modal-actions">
        <button class="btn ghost" id="cancelD">Cancel</button>
        <button class="btn primary" id="saveRule">Save Rule</button>
      </div>
    </div>
  </div>`;
}

function ProductCatalogModal() {
  return `
  <div class="modal-bg" id="modalBg">
    <div class="modal modal-wide">
      <h3>Product Catalog</h3>
      <div class="hint" style="margin-bottom:8px;">Read-only preview — products are owned by the Inventory module.</div>
      <table>
        <thead><tr><th>Product ID</th><th>Name</th><th>Category</th><th>Unit Price</th></tr></thead>
        <tbody>
        ${PRODUCTS.map(p => `<tr>
          <td class="mono">P-${String(p.product_id).padStart(4, '0')}</td>
          <td>${p.name}</td>
          <td>${p.category}</td>
          <td class="mono">${money(p.unit_price)}</td>
        </tr>`).join('') || `<tr><td colspan="4" class="empty">No products yet.</td></tr>`}
        </tbody>
      </table>
      <div class="modal-actions">
        <button class="btn ghost" id="cancelD">Close</button>
      </div>
    </div>
  </div>`;
}

function TaxRegionModal() {
  return `
  <div class="modal-bg" id="modalBg">
    <div class="modal modal-wide">
      <h3>Tax Regions</h3>
      <div class="hint" style="margin-bottom:8px;">Read-only preview — tax regions are owned by the Finance module.</div>
      <table>
        <thead><tr><th>Country</th><th>VAT Rate</th><th></th></tr></thead>
        <tbody>
        ${state.taxRegions.map(r => `<tr>
          <td>${r.country}</td>
          <td class="mono">${r.vat}%</td>
          <td>${r.isDefault ? '<span class="hint">Default</span>' : ''}</td>
        </tr>`).join('') || `<tr><td colspan="3" class="empty">No tax regions yet.</td></tr>`}
        </tbody>
      </table>
      <div class="modal-actions">
        <button class="btn ghost" id="cancelD">Close</button>
      </div>
    </div>
  </div>`;
}

/* ══════════ EVENTS ══════════ */
function attachEvents() {
  document.querySelectorAll('.modnav-item[data-mod]').forEach(el => {
    el.onclick = (e) => {
      e.stopPropagation();
      const mod = el.dataset.mod;
      state.openMod = (state.openMod === mod) ? null : mod;
      render();
    };
  });
  if (state.openMod) {
    document.addEventListener('click', function closeMod() {
      state.openMod = null; render();
      document.removeEventListener('click', closeMod);
    }, { once: true });
  }

  document.querySelectorAll('[data-tab]').forEach(el => {
    el.onclick = (e) => { e.stopPropagation(); state.tab = el.dataset.tab; state.view = null; state.activeId = null; state.openMod = null; state.filterText = ''; render(); };
  });
  const back = document.getElementById('backBtn');
  if (back) back.onclick = () => { state.view = null; state.activeId = null; render(); };

  document.querySelectorAll('[data-view]').forEach(el => { el.onclick = () => { state.view = 'detail'; state.activeId = el.dataset.view; render(); }; });
  document.querySelectorAll('[data-vb]').forEach(el => { el.onclick = e => { e.stopPropagation(); state.view = 'detail'; state.activeId = el.dataset.vb; render(); }; });

  const sb = document.getElementById('searchBox');
  if (sb) sb.oninput = e => { state.filterText = e.target.value; render(); setTimeout(() => { const s = document.getElementById('searchBox'); if (s) { s.focus(); s.setSelectionRange(s.value.length, s.value.length); } }, 0); };

  /* Quotation actions */
  document.querySelectorAll('[data-send-quote]').forEach(el => { el.onclick = async () => {
    try { await api(`/quotations/${el.dataset.sendQuote}/send`, { method: 'PATCH' }); await refresh(); showToast('📨', 'Quotation sent to customer.'); }
    catch (err) { showToast('⚠️', 'Failed to send quotation.'); }
  }; });
  document.querySelectorAll('[data-reject-quote]').forEach(el => { el.onclick = async () => {
    try { await api(`/quotations/${el.dataset.rejectQuote}/reject`, { method: 'PATCH' }); await refresh(); showToast('❌', 'Quotation rejected.'); }
    catch (err) { showToast('⚠️', 'Failed to reject quotation.'); }
  }; });
  document.querySelectorAll('[data-accept-quote]').forEach(el => { el.onclick = async () => {
    try {
      const order = await api(`/quotations/${el.dataset.acceptQuote}/accept`, { method: 'PATCH' });
      await refresh();
      state.tab = 'orders'; state.view = 'detail'; state.activeId = 'o:' + order.orderId;
      render();
      showToast('✅', 'Quotation accepted. Sales Order created.');
    } catch (err) { showToast('⚠️', 'Failed to accept quotation.'); }
  }; });

  document.querySelectorAll('[data-view-pdf]').forEach(el => { el.onclick = () => {
    const invoice = state.invoices.find(i => i.invoiceId == el.dataset.viewPdf);
    if (invoice) openInvoicePdf(invoice);
  }; });

  /* Order actions */
  document.querySelectorAll('[data-advance]').forEach(el => { el.onclick = async () => {
    try { await api(`/orders/${el.dataset.advance}/advance`, { method: 'PATCH' }); await refresh(); showToast('🚚', 'Order advanced.'); }
    catch (err) { showToast('⚠️', 'Failed to advance order.'); }
  }; });
  document.querySelectorAll('[data-cancel]').forEach(el => { el.onclick = async () => {
    try { await api(`/orders/${el.dataset.cancel}/cancel`, { method: 'PATCH' }); await refresh(); showToast('🛑', 'Order cancelled.'); }
    catch (err) { showToast('⚠️', 'Failed to cancel order.'); }
  }; });
  document.querySelectorAll('[data-hold]').forEach(el => { el.onclick = async () => {
    try { await api(`/orders/${el.dataset.hold}/hold`, { method: 'PATCH' }); await refresh(); showToast('⏸️', 'Order put on hold.'); }
    catch (err) { showToast('⚠️', 'Failed to put order on hold.'); }
  }; });
  document.querySelectorAll('[data-resume]').forEach(el => { el.onclick = async () => {
    try { await api(`/orders/${el.dataset.resume}/resume`, { method: 'PATCH' }); await refresh(); showToast('▶️', 'Order resumed.'); }
    catch (err) { showToast('⚠️', 'Failed to resume order.'); }
  }; });
  document.querySelectorAll('[data-gen-invoice]').forEach(el => { el.onclick = async () => {
    try {
      await api(`/orders/${el.dataset.genInvoice}/generate-invoice`, { method: 'POST' });
      await refresh();
      showToast('🧾', 'Invoice generated. Requested to sync to Finance module AR ledger.');
    } catch (err) { showToast('⚠️', 'Failed to generate invoice.'); }
  }; });

  /* Invoice actions */
  document.querySelectorAll('[data-markpaid]').forEach(el => { el.onclick = async () => {
    try { await api(`/invoices/${el.dataset.markpaid}/pay`, { method: 'PATCH' }); await refresh(); showToast('💰', 'Invoice marked as paid.'); }
    catch (err) { showToast('⚠️', 'Failed to mark invoice paid.'); }
  }; });

  /* Inventory / Finance mock sync panels — no real backend module exists yet,
     these are just a preview of the intended hand-off, per the modnav notes. */
  document.querySelectorAll('[data-sync]').forEach(el => { el.onclick = () => {
    const mod = el.dataset.sync;
    showToast(mod === 'inventory' ? '📦' : '💰', mod === 'inventory' ? 'Inventory sync requested (mock).' : 'Pending entries posted to ledger (mock).');
  }; });
  document.querySelectorAll('[data-sync-log]').forEach(el => { el.onclick = () => {
    showToast('📄', `${el.dataset.syncLog === 'inventory' ? 'Stock log' : 'Ledger'} view isn't available yet — owned by that module.`);
  }; });

  /* Pricing rule actions */
  document.querySelectorAll('[data-toggle-rule]').forEach(el => { el.onclick = async () => {
    try { await api(`/pricing-rules/${el.dataset.toggleRule}/toggle`, { method: 'PATCH' }); await refresh(); showToast('🏷️', 'Pricing rule status updated.'); }
    catch (err) { showToast('⚠️', 'Failed to update rule.'); }
  }; });
  document.querySelectorAll('[data-remove-rule]').forEach(el => { el.onclick = async () => {
    if (!confirm('Remove this pricing rule? This cannot be undone.')) return;
    try { await api(`/pricing-rules/${el.dataset.removeRule}`, { method: 'DELETE' }); await refresh(); showToast('🗑️', 'Pricing rule removed.'); }
    catch (err) { showToast('⚠️', 'Failed to remove rule.'); }
  }; });

  /* Products / Tax Region stat cards */
  const productsCard = document.getElementById('productsCard');
  if (productsCard) productsCard.onclick = () => { state.modal = { type: 'product-catalog' }; render(); };
  const taxRegionCard = document.getElementById('taxRegionCard');
  if (taxRegionCard) taxRegionCard.onclick = () => { state.modal = { type: 'tax-region' }; render(); };

  const newRuleBtn = document.getElementById('newRuleBtn');
  if (newRuleBtn) newRuleBtn.onclick = () => {
    state.draft = { rule_name: '', rule_type: 'Percentage', discount_value: 0, applicable_to: 'Order-wide', start_date: new Date().toISOString().slice(0, 10), end_date: '' };
    state.modal = { type: 'new-rule' };
    render();
  };
  const saveRule = document.getElementById('saveRule');
  if (saveRule) saveRule.onclick = async () => {
    const d = state.draft;
    if (!d.rule_name) return;
    try {
      await api('/pricing-rules', { method: 'POST', body: JSON.stringify({
        rule_name: d.rule_name, rule_type: d.rule_type, discount_value: parseFloat(d.discount_value) || 0,
        applicable_to: d.applicable_to, start_date: d.start_date, end_date: d.end_date || null, status: 'Active',
      }) });
      state.modal = null;
      await refresh();
      showToast('🏷️', 'Pricing rule created.');
    } catch (err) { showToast('⚠️', 'Failed to save pricing rule.'); }
  };
  const rn = document.getElementById('ruleName'); if (rn) rn.oninput = e => { state.draft.rule_name = e.target.value; };
  const rt = document.getElementById('ruleType'); if (rt) rt.onchange = e => { state.draft.rule_type = e.target.value; };
  const rv = document.getElementById('ruleValue'); if (rv) rv.oninput = e => { state.draft.discount_value = e.target.value; };
  const ra = document.getElementById('ruleApplicable'); if (ra) ra.onchange = e => { state.draft.applicable_to = e.target.value; };
  const rs = document.getElementById('ruleStart'); if (rs) rs.onchange = e => { state.draft.start_date = e.target.value; };
  const re = document.getElementById('ruleEnd'); if (re) re.onchange = e => { state.draft.end_date = e.target.value; };

  /* New quotation modal */
  const newQuoteBtn = document.getElementById('newQuoteBtn');
  if (newQuoteBtn) newQuoteBtn.onclick = () => {
    const defaultRegion = state.taxRegions.find(r => r.isDefault) || state.taxRegions[0];
    state.draft = {
      customer: '', validUntil: new Date(Date.now() + 15 * 86400000).toISOString().slice(0, 10),
      tax_region_id: defaultRegion ? defaultRegion.id : null,
      items: PRODUCTS.length ? [{ product_id: PRODUCTS[0].product_id, quantity: 1, discount_percent: 0, discSource: 'custom' }] : [],
    };
    state.modal = { type: 'new-quote' };
    render();
  };
  const dc = document.getElementById('draftCust'); if (dc) dc.oninput = e => { state.draft.customer = e.target.value; };
  const dv = document.getElementById('draftValid'); if (dv) dv.onchange = e => { state.draft.validUntil = e.target.value; };
  const dtr = document.getElementById('draftTaxRegion'); if (dtr) dtr.onchange = e => { state.draft.tax_region_id = parseInt(e.target.value); };
  document.querySelectorAll('[data-product]').forEach(el => { el.onchange = e => {
    const ix = parseInt(el.dataset.product);
    const it = state.draft.items[ix];
    it.product_id = parseInt(e.target.value);
    if (it.discSource && it.discSource !== 'custom') {
      const rule = activeDiscountRules().find(r => r.id == it.discSource);
      const product = PRODUCTS.find(p => p.product_id == it.product_id);
      if (rule) it.discount_percent = ruleToPercent(rule, product, it.quantity);
    }
    render();
  }; });
  document.querySelectorAll('[data-qty]').forEach(el => { el.onchange = e => {
    const ix = parseInt(el.dataset.qty);
    const it = state.draft.items[ix];
    it.quantity = Math.max(1, parseInt(e.target.value) || 1);
    if (it.discSource && it.discSource !== 'custom') {
      const rule = activeDiscountRules().find(r => r.id == it.discSource);
      const product = PRODUCTS.find(p => p.product_id == it.product_id);
      if (rule) it.discount_percent = ruleToPercent(rule, product, it.quantity);
    }
    render();
  }; });
  document.querySelectorAll('[data-disc]').forEach(el => { el.onchange = e => { state.draft.items[parseInt(el.dataset.disc)].discount_percent = Math.min(100, Math.max(0, parseFloat(e.target.value) || 0)); }; });
  document.querySelectorAll('[data-disc-rule]').forEach(el => { el.onchange = e => {
    const ix = parseInt(el.dataset.discRule);
    const val = e.target.value;
    const it = state.draft.items[ix];
    if (val === 'custom') {
      it.discSource = 'custom';
    } else {
      const rule = activeDiscountRules().find(r => r.id == val);
      const product = PRODUCTS.find(p => p.product_id == it.product_id);
      it.discSource = val;
      it.discount_percent = rule ? ruleToPercent(rule, product, it.quantity) : 0;
    }
    render();
  }; });
  document.querySelectorAll('[data-rem]').forEach(el => { el.onclick = () => { state.draft.items.splice(parseInt(el.dataset.rem), 1); render(); }; });
  const addLI = document.getElementById('addLI');
  if (addLI) addLI.onclick = () => { state.draft.items.push({ product_id: PRODUCTS[0]?.product_id, quantity: 1, discount_percent: 0, discSource: 'custom' }); render(); };
  const saveD = document.getElementById('saveD');
  if (saveD) saveD.onclick = async () => {
    const d = state.draft;
    if (!d.customer || !d.items.length) return;
    try {
      await api('/quotations', { method: 'POST', body: JSON.stringify({
        customer: d.customer, valid_until: d.validUntil, tax_region_id: d.tax_region_id, items: d.items,
      }) });
      state.modal = null;
      await refresh();
      showToast('📋', 'New quotation saved.');
    } catch (err) { showToast('⚠️', 'Failed to save quotation.'); }
  };

  /* retry loading */
  const retryLoad = document.getElementById('retryLoad');
  if (retryLoad) retryLoad.onclick = () => { loadBootstrap(); };

  /* modal close */
  const modalBg = document.getElementById('modalBg');
  if (modalBg) modalBg.onclick = e => { if (e.target.id === 'modalBg') { state.modal = null; render(); } };
  const cancelD = document.getElementById('cancelD');
  if (cancelD) cancelD.onclick = () => { state.modal = null; render(); };
}

loadBootstrap();
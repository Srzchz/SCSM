# Unified Shell Migration — Scope & Handoff Guide

**Goal:** one shared layout + one sidebar listing every real function
(Cases, Warranty, Sales Orders, Quotations, Customers, Communication Logs,
Purchase Behavior, Order History, Performance Dashboard, Targets, Alerts,
Revenue Forecast) — not four separate module shells linked from a bucket
menu (After-Sales | Sales Order | Sales Report | Customer Service).

This file is meant to survive across sessions/people. Update the
**Status** table at the bottom as work lands, so whoever picks this up
next — human or Claude — knows exactly where things stand without
re-deriving it.

---

## The actual problem (found while scoping this out)

The four modules aren't just visually different — they use three
different navigation patterns, and only one of them is a normal
Laravel multi-page app you can just "slot into" a shared shell:

| Module | Pattern |
|---|---|
| CRM | Normal multi-page, server-rendered. **This is the shell to standardize on** — most complete, and already Tailwind (via CDN, no build step) + Alpine.js. |
| ASCM | One URL, client-side JS shows/hides sections. Own CSS file (`css/app.css`), own classes (`app-shell`, `app-content`) — **shares zero design tokens with CRM**, despite both being "Curema-style." Needs actual visual rework, not just relocation. |
| SPR | Normal multi-page, own layout, own design tokens — haven't compared these against CRM's yet (unscoped risk, see below). |
| SOM | **The hard one.** Its one page is a fully self-contained SPA — own `<!DOCTYPE>`, own `<head>`, own JS fetching from its own internal API. Never built to render as content inside someone else's layout. |

CRM's sidebar already anticipated this problem and got worked around
with three bucket links instead of real ones — this is the exact thing
being replaced:

```
['label' => 'Sales Order', 'route' => 'sales-order']              // redirects to SOM's own page
['label' => 'After-Sales Support', 'route' => 'after-sales-support'] // redirects to ASCM's own page
['label' => 'Sales Report', 'route' => 'sales-report']             // redirects to SPR's own page
```

## Two ways to attack this — pick based on remaining budget

**Option A — Navigation first, visuals later (recommended, cheapest win).**
Replace those 3 bucket links with the real sub-items (Cases, Warranty,
Sales Orders, Quotations, Dashboard, Targets, Alerts, Forecast) *right
now*, pointing at each module's existing routes. Clicking them still
drops you into that module's own separate-looking page for now — but
the **sidebar itself** stops being a bucket menu immediately. This
directly fixes the complaint ("functions accessible in the sidebar")
without touching any module's actual page code. Small, low-risk,
independent of which modules get visually migrated later or by whom.

**Option B — Full visual unification.** Every module's pages actually
render inside one shared layout, so the whole app looks and feels
consistent, not just navigates consistently. This is the bigger work
broken out by module below.

These aren't mutually exclusive — Option A can ship today, Option B
happens module-by-module afterward, in any order, by any number of
people, without conflicting with each other (see ownership note below).

---

## Phase breakdown (Option B, per submodule)

### Phase 0 — Shared shell + master sidebar
**Size: Small.** Base it on `resources/views/crm/layouts/app.blade.php`
— generalize it (rename out of the `crm/` folder, e.g.
`resources/views/layouts/shell.blade.php`), extract the `$nav` array out
of the Blade file into something each page can extend/override rather
than it being hardcoded, keep the CDN-based Tailwind config (no Vite
build step to fight with) and Alpine.js.

### Phase 1 — CRM
**Size: Small.** Already built for this exact layout. Mostly deleting
now-redundant per-page nav bits once Phase 0's shell replaces
`crm/layouts/app.blade.php` directly. Good first real migration to prove
the shell works before touching harder modules.

### Phase 2 — ASCM
**Size: Medium.** Its 2 real pages (Cases, Warranty) need their markup
rewritten from ASCM's custom CSS classes to Tailwind + `curema-*` tokens
— this is a visual rebuild of those two pages, not a copy-paste. The
underlying PHP/data (`AscmShellController`, `CaseController`,
`WarrantyController`) doesn't need to change, only the Blade markup.

### Phase 3 — SPR
**Size: Medium, unscoped risk.** 5 real pages (Dashboard, Targets,
Alerts, Forecast, Generate Report). Same category of work as ASCM —
haven't yet compared SPR's own layout/tokens against CRM's closely
enough to know how much is reusable vs. needs rebuilding. **First task
of this phase should be that comparison**, before estimating further.

### Phase 4 — SOM
**Size: Large, highest risk. Do this last**, once the pattern's proven
on the other three. Its SPA needs to be pulled apart: the actual UI
content extracted from its own `<!DOCTYPE>`/`<head>` wrapper into
something that can `@extends` the shared shell, while keeping its
internal JS/API calls working. This is real re-architecture, not a
markup pass like ASCM/SPR.

---

## Ownership / working in parallel without collisions

If more than one person is doing this at once:

- **Only whoever's on Phase 0 touches the shared shell file and the
  master `$nav` array.** Everyone else's job is pointing their module's
  pages *at* that shell, not editing it.
- Each phase after Phase 0 only touches that module's own view files
  (`resources/views/{module}/...`) — no cross-module file overlap, so
  two people on two different phases can't conflict with each other.
- If Phase 0 isn't done yet, later phases can still prep the Blade
  markup rewrite work (e.g. Phase 2's Tailwind conversion of ASCM's
  pages) against the *existing* CRM layout as a stand-in — swapping
  `@extends()` to point at the real shared shell once Phase 0 lands is
  a one-line change per file.

---

## Status

| Phase | Status | Branch | Notes |
|---|---|---|---|
| Option A — Nav-only fix | **Done** | `scsm-after_sales` | Replaced the single "After-Sales Support" bucket link in `crm/layouts/app.blade.php` with direct "Cases" and "Warranty" links. Superseded by Phase 2 below (routes changed from `ascm.dashboard?section=` to dedicated `ascm.cases` / `ascm.warranty`). |
| 2 — ASCM | **Done** | `scsm-after_sales` | Cases and Warranty now `@extends('crm.layouts.app')` as real pages at `/ascm/cases` and `/ascm/warranty`, dropping ASCM's own sidebar/topbar/SPA shell entirely. Also fixed two pre-existing bugs found along the way: `CaseController`/`WarrantyController` redirect helpers pointed at the wrong route (`route('dashboard')`, which is HubController's bucket page, not ASCM), and the off-canvas panel's JS built form-submit URLs missing the `/ascm` prefix. |
| 0 — Shared shell | Not started | — | — |
| 1 — CRM | Not started | — | — |
| 3 — SPR | Not started | — | Needs the token/layout comparison first |
| 4 — SOM | Not started | — | Do last |

*(Whoever picks up a phase: update this table with your branch name and
a one-line note when you stop, even mid-phase, so the next person
doesn't have to re-read the diff to figure out where you left off.)*

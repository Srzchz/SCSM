# Sales Performance Reporting — Automation Patch

## Update: round 2 fixes/features
- **Alert Settings now saves for real.** New `POST /sales-performance-reporting/alert-settings` route + `AlertsController::updateSettings()`. The threshold dropdown and both toggles now submit a real form; inventory-trigger growth%/months and forecast-deviation% are now editable too (they existed in the DB before but had no UI).
- **Targets: Region Attainment was missing Actual/Quota columns** (only had Region + a bar). Added, matching the Rep/Product tables.
- **Generate Report: removed the redundant "Region" column** that always showed "—" when viewing Sales by Region/Product (only Sales by Rep rows actually have a per-row region).
- **Revenue Forecast: added a plain-language explanation panel** below the chart, side by side — one card per method, auto-generated from the actual computed slope/values (not static text).
- **Fixed rep/region actual always showing ₱0.** Root cause: the seeder only created quota rows for the 6 brand-new demo reps, but your 476 historical orders (seeded earlier by `HistoricalSalesOrderSeeder`) are attributed to whichever reps *that* seeder used. `TargetSyncService` was working correctly — there was just nothing for it to sync against for those specific 6 rep IDs. Fixed by having the seeder target **every** sales rep that actually has a linked user account, not just its own 6.
  - **Caveat:** if your existing reps' `region_id` points at regions with different names than "Luzon/Visayas/Mindanao" (e.g. from `StaffUserSeeder`), region-level attainment may still under-count even though rep-level is now correct — check `sales_reps.region_id` vs `regions.name` if Region Attainment still looks off after re-seeding.
  - **Re-run the seeder** after pulling this update — it's idempotent (uses `updateOrInsert`), safe to run again.


## What this does
- **Automated forecasting**: `RevenueForecastService` computes Linear Regression *and* Weighted Moving Average forecasts live from `sales_orders` every page load. No sliders, no manual assumptions. Both are stored (labeled by method) in the new `sales_performance_reporting_forecasts` table.
- **Automated targets sync**: `TargetSyncService` recalculates `actual_amount` on every Rep/Region/Product target row from real order data, every page load.
- **Automated alerts**: `AlertGenerationService` replaces "+ New Alert"/Edit/Delete entirely. It scans quota attainment, product growth trends, and forecast-vs-actual deviation, and writes/clears alert rows accordingly. Only "Mark as read" remains as a user action.
- **Dynamic period**: the hardcoded `PERIOD = '2026-Q2'` constant (previously duplicated in 3 controllers) is replaced by `PeriodHelper::current()`, which derives the "current" quarter from the latest order in the database — it never needs manual bumping.
- **Seeder**: `SalesPerformanceReportingDemoSeeder` generates ~15 months of realistic PC-parts sales history (regions, reps, orders, order items) plus quota rows for the last two quarters, using your existing 57 products and 100 customers.

## Where each file goes
Copy this folder's contents into your Laravel project root, matching the paths:

```
app/Modules/SalesPerformanceReporting/Controllers/   (overwrites 3 of 4 — Dashboard untouched)
app/Modules/SalesPerformanceReporting/Services/       (new)
app/Modules/SalesPerformanceReporting/Models/         (new — see note below)
database/migrations/                                  (new)
database/seeders/                                     (new)
resources/views/sales-performance-reporting/pages/    (overwrites 2 — generate-report.blade.php & targets.blade.php untouched)
routes/sales-performance-reporting.php                (overwrites)
```

## ⚠️ Models note
I didn't have your original `app/Modules/SalesPerformanceReporting/Models/*.php` files, so the ones included here are reconstructions based on how `RepTarget`, `RegionTarget`, `ProductTarget`, `Alert`, `AlertSetting`, `Region`, and `SalesRep` are actually *used* in your controllers/blades (`attainmentStatus()`, `progressWidth()`, `timeAgo()`, `AlertSetting::current()`, etc.). If your real models already exist with extra fields/relations I don't know about, **diff before overwriting** rather than blindly replacing. One assumption to double-check: `ProductTarget::product()` points at `App\Models\Product` — change the class reference if your product model lives elsewhere.

Two files from the old version are now unused and should be deleted:
- `app/Modules/SalesPerformanceReporting/Models/ForecastAssumption.php`
- `app/Modules/SalesPerformanceReporting/Requests/UpdateForecastAssumptionRequest.php`
- `app/Modules/SalesPerformanceReporting/Requests/AlertRequest.php` (only used by the removed store/update — confirm nothing else references it first)

## Install steps
```bash
# 1. Copy files in (see paths above)

# 2. Run the new migrations
php artisan migrate

# 3. Seed demo data (registers a fresh dataset; skips order generation if
#    sales_orders already has more than a handful of rows)
php artisan db:seed --class="Database\Seeders\SalesPerformanceReportingDemoSeeder"

# 4. Visit /sales-performance-reporting/revenue-forecast, /targets, and
#    /alerts — everything populates itself, nothing to click "generate" on.
```

## Design notes
- **Linear Regression**: least-squares trend line over the last 12 closed months, projected 3 months forward.
- **Weighted Moving Average**: 3-month window, most recent month weighted heaviest (3:2:1), each forecasted step feeds into the next.
- **`sales_performance_reporting_monthly_revenue`** is left untouched/unused by this submodule now (actuals are computed live from `sales_orders` instead) — I didn't drop it in case your Dashboard page reads from it.
- **Alert dedupe**: each generated alert has a `dedupe_key` (e.g. `rep_target:14:2026-Q3`) so re-running the generator on every page load updates existing alerts in place (keeping `is_read`/`created_at`) instead of duplicating, and removes alerts whose condition is no longer true.
- Quotas (`target_amount`) are still seeded, not derived — that's a legitimate business decision, not something to reverse-engineer from history. The seeder sets each quota to ~112% of the prior quarter's actual as a "stretch goal" baseline.

## Still worth asking about
- Do you want alert-settings toggles (target-breach threshold, inventory trigger, forecast deviation) to actually **save** to the DB? Right now they render from `AlertSetting::current()` but the toggle buttons are cosmetic only (same as before) — I left a note in the blade file with the one-route fix if you want it.

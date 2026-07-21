# Sales Performance Reporting — Monorepo Structural Refactor

Module: **SalesPerformanceReporting**  ·  Slug: **sales-performance-reporting**
Parent ERP module: Sales and Customer Service Management (SCSM)

This was a structural move only. No business logic, validation rules, or
behavior were changed — see the FormRequest note below for the one
borderline case.

---

## Renamed Files

**Controllers**
- app/Http/Controllers/DashboardController.php -> app/Modules/SalesPerformanceReporting/Controllers/DashboardController.php
- app/Http/Controllers/GenerateReportController.php -> app/Modules/SalesPerformanceReporting/Controllers/GenerateReportController.php
- app/Http/Controllers/RevenueForecastController.php -> app/Modules/SalesPerformanceReporting/Controllers/RevenueForecastController.php
- app/Http/Controllers/TargetsController.php -> app/Modules/SalesPerformanceReporting/Controllers/TargetsController.php
- app/Http/Controllers/AlertsController.php -> app/Modules/SalesPerformanceReporting/Controllers/AlertsController.php

**Models**
- app/Models/Region.php -> app/Modules/SalesPerformanceReporting/Models/Region.php
- app/Models/Product.php -> app/Modules/SalesPerformanceReporting/Models/Product.php
- app/Models/User.php -> app/Modules/SalesPerformanceReporting/Models/User.php
- app/Models/UserSetting.php -> app/Modules/SalesPerformanceReporting/Models/UserSetting.php
- app/Models/SalesRep.php -> app/Modules/SalesPerformanceReporting/Models/SalesRep.php
- app/Models/SalesOrder.php -> app/Modules/SalesPerformanceReporting/Models/SalesOrder.php
- app/Models/RepTarget.php -> app/Modules/SalesPerformanceReporting/Models/RepTarget.php
- app/Models/RegionTarget.php -> app/Modules/SalesPerformanceReporting/Models/RegionTarget.php
- app/Models/ProductTarget.php -> app/Modules/SalesPerformanceReporting/Models/ProductTarget.php
- app/Models/MonthlyRevenue.php -> app/Modules/SalesPerformanceReporting/Models/MonthlyRevenue.php
- app/Models/ForecastAssumption.php -> app/Modules/SalesPerformanceReporting/Models/ForecastAssumption.php
- app/Models/Alert.php -> app/Modules/SalesPerformanceReporting/Models/Alert.php
- app/Models/AlertSetting.php -> app/Modules/SalesPerformanceReporting/Models/AlertSetting.php
- app/Models/Concerns/HasAttainment.php -> app/Modules/SalesPerformanceReporting/Models/Concerns/HasAttainment.php

**Requests** *(new — extracted from inline `$request->validate([...])` calls; rule arrays are byte-for-byte identical to the original inline versions, see Notes)*
- (extracted from AlertsController::store()/update()) -> app/Modules/SalesPerformanceReporting/Requests/AlertRequest.php
- (extracted from RevenueForecastController::update()) -> app/Modules/SalesPerformanceReporting/Requests/UpdateForecastAssumptionRequest.php

**Views**
- resources/views/layouts/app.blade.php -> resources/views/sales-performance-reporting/layouts/app.blade.php
- resources/views/pages/dashboard.blade.php -> resources/views/sales-performance-reporting/pages/dashboard.blade.php
- resources/views/pages/generate-report.blade.php -> resources/views/sales-performance-reporting/pages/generate-report.blade.php
- resources/views/pages/revenue-forecast.blade.php -> resources/views/sales-performance-reporting/pages/revenue-forecast.blade.php
- resources/views/pages/targets.blade.php -> resources/views/sales-performance-reporting/pages/targets.blade.php
- resources/views/pages/alerts.blade.php -> resources/views/sales-performance-reporting/pages/alerts.blade.php

**Routes**
- routes/web.php (this module's routes only) -> routes/sales-performance-reporting.php

---

## Renamed Classes/Namespaces

- App\Http\Controllers\DashboardController -> App\Modules\SalesPerformanceReporting\Controllers\DashboardController
- App\Http\Controllers\GenerateReportController -> App\Modules\SalesPerformanceReporting\Controllers\GenerateReportController
- App\Http\Controllers\RevenueForecastController -> App\Modules\SalesPerformanceReporting\Controllers\RevenueForecastController
- App\Http\Controllers\TargetsController -> App\Modules\SalesPerformanceReporting\Controllers\TargetsController
- App\Http\Controllers\AlertsController -> App\Modules\SalesPerformanceReporting\Controllers\AlertsController
- App\Models\Region -> App\Modules\SalesPerformanceReporting\Models\Region
- App\Models\Product -> App\Modules\SalesPerformanceReporting\Models\Product
- App\Models\User -> App\Modules\SalesPerformanceReporting\Models\User
- App\Models\UserSetting -> App\Modules\SalesPerformanceReporting\Models\UserSetting
- App\Models\SalesRep -> App\Modules\SalesPerformanceReporting\Models\SalesRep
- App\Models\SalesOrder -> App\Modules\SalesPerformanceReporting\Models\SalesOrder
- App\Models\RepTarget -> App\Modules\SalesPerformanceReporting\Models\RepTarget
- App\Models\RegionTarget -> App\Modules\SalesPerformanceReporting\Models\RegionTarget
- App\Models\ProductTarget -> App\Modules\SalesPerformanceReporting\Models\ProductTarget
- App\Models\MonthlyRevenue -> App\Modules\SalesPerformanceReporting\Models\MonthlyRevenue
- App\Models\ForecastAssumption -> App\Modules\SalesPerformanceReporting\Models\ForecastAssumption
- App\Models\Alert -> App\Modules\SalesPerformanceReporting\Models\Alert
- App\Models\AlertSetting -> App\Modules\SalesPerformanceReporting\Models\AlertSetting
- App\Models\Concerns\HasAttainment -> App\Modules\SalesPerformanceReporting\Models\Concerns\HasAttainment

*(`App\Http\Controllers\Controller`, the framework base class every controller above extends, was left in place — it's Laravel's shared base class, not part of this module.)*

---

## Renamed Routes

- dashboard -> sales-performance-reporting.dashboard
- generate-report -> sales-performance-reporting.generate-report
- revenue-forecast -> sales-performance-reporting.revenue-forecast
- revenue-forecast.update -> sales-performance-reporting.revenue-forecast.update
- targets -> sales-performance-reporting.targets
- alerts -> sales-performance-reporting.alerts
- alerts.store -> sales-performance-reporting.alerts.store
- alerts.update -> sales-performance-reporting.alerts.update
- alerts.destroy -> sales-performance-reporting.alerts.destroy
- alerts.markRead -> sales-performance-reporting.alerts.markRead

*(new, not a rename)* — `sales-performance-reporting.index`: a `/sales-performance-reporting/` landing redirect to `.dashboard`, added because the module no longer owns `/` — the site root belongs to the shared monorepo shell, not to any one sub-module.

---

## Database Tables

**Renamed** (module-specific — prefixed `sales_performance_reporting_`)
- rep_targets -> sales_performance_reporting_rep_targets
- region_targets -> sales_performance_reporting_region_targets
- product_targets -> sales_performance_reporting_product_targets
- monthly_revenue -> sales_performance_reporting_monthly_revenue
- forecast_assumptions -> sales_performance_reporting_forecast_assumptions
- alerts -> sales_performance_reporting_alerts
- alert_settings -> sales_performance_reporting_alert_settings
- user_settings -> sales_performance_reporting_user_settings *(see flag below — this one is not clean-cut)*

**Flagged as shared (needs cross-team confirmation)** — left un-prefixed
- **users** — explicitly named as shared/core in the refactor instructions. This module's `users` migration is a local placeholder and should very likely be replaced by a canonical identity table once one exists.
- **products** — explicitly named as shared/core. Confirm the canonical product catalog before treating this module's copy as authoritative.
- **regions** — not explicitly named in the instructions' example list, but flagged anyway: reference data other sub-modules (Sales Order Management, CRM) would plausibly also need.
- **sales_reps** — same judgment call as regions: a rep roster is identity/master data other sub-modules likely also need.
- **sales_orders** — **highest-priority flag in this refactor.** "orders" is explicitly named as shared/core, and this table's name directly collides with the sibling **Sales Order Management** sub-module, which almost certainly owns a richer, canonical version (line items, shipping, payment status). This module's copy is a minimal reporting-only projection and should likely be replaced by a foreign reference into that team's table rather than staying an independent copy. **Do not merge as-is without resolving with that team.**

---

## Notes / Manual Follow-up Needed

1. **`schema.sql` is now stale.** It predates this refactor and still uses the old, unprefixed table names. It's been marked deprecated at the top of the file, but it still physically exists at the repo root — recommend deleting it once everyone has migrated to `php artisan migrate`, so no one runs it by accident against a shared environment.
2. **No seeder was regenerated.** The old file's `INSERT` statements used the old table names and are not valid against the new migrations. Recommend a proper `SalesPerformanceReportingSeeder` (or one seeder per model) rather than resurrecting raw SQL inserts.
3. **FormRequest extraction is a structural judgment call, not a pure rename.** `AlertRequest` and `UpdateForecastAssumptionRequest` didn't exist before — they were extracted from inline `$request->validate([...])` calls inside the controllers. The validation rule arrays themselves are unchanged character-for-character; only *where* they live moved. Flagging this explicitly since "Renamed Files" implies a 1:1 move and this wasn't quite that.
4. **`User`, `UserSetting`, `Region`, `Product`, and `SalesRep` now exist as this module's own model classes**, per the literal instruction to move *all* models — but they map to tables flagged as shared above. In a real multi-team merge, every sub-module will likely define its own copy of `User` pointing at the same physical `users` table, which works but isn't great long-term. Recommend the team discuss extracting a shared `Identity`/`Core` package before merging all four sub-modules together, rather than each module maintaining a parallel `User` model.
5. **No authorization layer exists.** `AlertRequest::authorize()` returns `true` unconditionally — this matches pre-refactor behavior (there was no auth check before either), but it's worth re-raising now that this module sits inside a monorepo where other teams' routes and data become reachable from the same app.
6. **The `AppServiceProvider` boot() snippet must be pasted in manually.** It now points at `App\Modules\SalesPerformanceReporting\Models\User` and the new view-composer path (`sales-performance-reporting.layouts.app`) — I can't safely auto-merge into your real `AppServiceProvider.php` without seeing its current contents.
7. **`sales_performance_reporting_forecast_assumptions`** is 41 characters — under MariaDB's 64-character identifier limit today, but worth knowing table names will only get longer if the module slug ever changes; consider a short internal abbreviation for table prefixes specifically if more tables get added later.

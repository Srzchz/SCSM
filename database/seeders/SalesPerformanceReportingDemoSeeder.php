<?php

namespace Database\Seeders;

use App\Modules\SalesPerformanceReporting\Services\PeriodHelper;
use App\Modules\SalesPerformanceReporting\Services\TargetSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds everything the Sales Performance Reporting sub-module needs to
 * demonstrate automated forecasting/targets/alerts against real-looking
 * data, WITHOUT touching products or customers (already seeded — 57 PC
 * parts, 100 customers):
 *
 *   1. Regions (Luzon, Visayas, Mindanao)
 *   2. Sales reps (users w/ role=rep + matching sales_reps rows)
 *   3. ~15 months of sales_orders + sales_order_items with a mild upward
 *      trend and a Nov/Dec seasonal bump, so Linear Regression and
 *      Weighted Moving Average actually have something to disagree about
 *   4. Quota rows (rep/region/product targets) for the current and
 *      previous quarter, with actual_amount immediately synced from the
 *      orders just created
 *
 * Run with: php artisan db:seed --class=Database\\Seeders\\SalesPerformanceReportingDemoSeeder
 * (or add a call to it inside DatabaseSeeder::run())
 */
class SalesPerformanceReportingDemoSeeder extends Seeder
{
    private const MONTHS_OF_HISTORY = 15;
    private const REGION_NAMES = ['Luzon', 'Visayas', 'Mindanao'];

    private const REP_NAMES = [
        ['name' => 'Miguel Santos', 'region' => 'Luzon'],
        ['name' => 'Bea Fernandez', 'region' => 'Luzon'],
        ['name' => 'Carlo Dela Cruz', 'region' => 'Visayas'],
        ['name' => 'Angeline Reyes', 'region' => 'Visayas'],
        ['name' => 'Paolo Mendoza', 'region' => 'Mindanao'],
        ['name' => 'Krystel Aquino', 'region' => 'Mindanao'],
    ];

    public function run(): void
    {
        $regionIds = $this->seedRegions();
        $reps = $this->seedReps($regionIds);
        $productIds = DB::table('products')->pluck('id')->all();
        $customerIds = DB::table('customers')->pluck('customer_id')->all();

        if (empty($productIds) || empty($customerIds)) {
            $this->command?->warn('No products/customers found — seed those first, then re-run this seeder.');
            return;
        }

        $this->seedOrders($reps, $productIds, $customerIds);

        // IMPORTANT: don't just target the 6 reps this seeder created.
        // If sales_orders already had historical data before this seeder
        // ran (as logged: "sales_orders already has data — skipping order
        // generation"), those orders are attributed to whichever reps
        // whatever seeder created them — almost certainly not these demo
        // ones. Targets need to cover every sales rep that actually has
        // order volume, or actual_amount will sync to 0 for all of them.
        $allReps = $this->allRepsWithUserAccounts();

        $this->seedTargets($allReps, $regionIds, $productIds);

        $this->command?->info('Targeted ' . count($allReps) . ' sales rep(s) across ' . count($regionIds) . ' region(s).');
        $this->command?->info('rep_targets rows: ' . DB::table('sales_performance_reporting_rep_targets')->count());
        $this->command?->info('region_targets rows: ' . DB::table('sales_performance_reporting_region_targets')->count());
        $this->command?->info('product_targets rows: ' . DB::table('sales_performance_reporting_product_targets')->count());
        $this->command?->info('Sales Performance Reporting demo data seeded.');
    }

    private function seedRegions(): array
    {
        foreach (self::REGION_NAMES as $name) {
            DB::table('regions')->updateOrInsert(['name' => $name], ['name' => $name]);
        }

        return DB::table('regions')->pluck('id', 'name')->all();
    }

    /** @return array<int, array{sales_rep_id:int,user_id:int,region_id:int,name:string}> */
    private function seedReps(array $regionIds): array
    {
        $reps = [];

        foreach (self::REP_NAMES as $i => $rep) {
            $email = 'rep' . ($i + 1) . '@ultd-demo.test';

            $userId = DB::table('users')->where('email', $email)->value('id');
            if (! $userId) {
                $userId = DB::table('users')->insertGetId([
                    'name'          => $rep['name'],
                    'email'         => $email,
                    'password'      => Hash::make('password'),
                    'role'          => 'employee',
                    'region_id'     => $regionIds[$rep['region']],
                    'avatar_initials' => collect(explode(' ', $rep['name']))->map(fn ($p) => $p[0])->join(''),
                    'employee_code' => 'REP-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                    'department'    => 'Sales',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            $salesRepId = DB::table('sales_reps')->where('user_id', $userId)->value('id');
            if (! $salesRepId) {
                $salesRepId = DB::table('sales_reps')->insertGetId([
                    'user_id'    => $userId,
                    'name'       => $rep['name'],
                    'region_id'  => $regionIds[$rep['region']],
                    'hire_date'  => Carbon::now()->subYears(rand(1, 4))->toDateString(),
                    'status'     => 'active',
                    'created_at' => now(),
                ]);
            }

            $reps[] = [
                'sales_rep_id' => $salesRepId,
                'user_id'      => $userId,
                'region_id'    => $regionIds[$rep['region']],
                'name'         => $rep['name'],
            ];
        }

        return $reps;
    }

    /**
     * @return array<int, array{sales_rep_id:int,user_id:int,region_id:int,name:string}>
     */
    private function allRepsWithUserAccounts(): array
    {
        return DB::table('sales_reps')
            ->whereNotNull('user_id')
            ->whereNotNull('region_id')
            ->get(['id as sales_rep_id', 'user_id', 'region_id', 'name'])
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function seedOrders(array $reps, array $productIds, array $customerIds): void
    {
        // Don't double-seed if history already exists.
        if (DB::table('sales_orders')->count() > 5) {
            $this->command?->info('sales_orders already has data — skipping order generation.');
            return;
        }

        $taxRegionId = DB::table('tax_regions')->where('is_default', true)->value('id')
            ?? DB::table('tax_regions')->value('id');

        $start = Carbon::now()->startOfMonth()->subMonths(self::MONTHS_OF_HISTORY - 1);
        $products = DB::table('products')->select('id', 'unit_price', 'price')->get()->keyBy('id');

        for ($m = 0; $m < self::MONTHS_OF_HISTORY; $m++) {
            $monthStart = (clone $start)->addMonths($m);
            $monthNumber = (int) $monthStart->format('n');

            // Mild upward trend over time + a Nov/Dec seasonal bump, plus
            // some randomness so the line isn't a suspiciously perfect ramp.
            $trend = 35 + ($m * 1.6);
            $seasonal = in_array($monthNumber, [11, 12]) ? 1.35 : 1.0;
            $ordersThisMonth = (int) round($trend * $seasonal * (mt_rand(85, 115) / 100));

            $isCurrentMonth = $monthStart->isSameMonth(Carbon::now());
            // Current month is still "in progress" — fewer orders so far.
            if ($isCurrentMonth) {
                $ordersThisMonth = (int) round($ordersThisMonth * (Carbon::now()->day / $monthStart->daysInMonth));
            }

            $orderRows = [];
            $itemRows = [];

            for ($i = 0; $i < $ordersThisMonth; $i++) {
                $orderDate = (clone $monthStart)->addDays(rand(0, $monthStart->daysInMonth - 1));
                if ($orderDate->isFuture()) {
                    continue;
                }

                $rep = $reps[array_rand($reps)];
                $itemCount = rand(1, 4);
                $chosenProducts = (array) array_rand($productIds, min($itemCount, count($productIds)));
                if (! is_array($chosenProducts)) {
                    $chosenProducts = [$chosenProducts];
                }

                $subtotal = 0;
                $pendingItems = [];

                foreach ($chosenProducts as $idx) {
                    $productId = $productIds[$idx];
                    $unitPrice = (float) ($products[$productId]->unit_price ?? $products[$productId]->price);
                    $qty = rand(1, 3);
                    $discountPct = collect([0, 0, 0, 5, 10])->random();
                    $lineTotal = round($unitPrice * $qty * (1 - $discountPct / 100), 2);
                    $subtotal += $lineTotal;

                    $pendingItems[] = [
                        'product_id'       => $productId,
                        'quantity'         => $qty,
                        'unit_price'       => $unitPrice,
                        'discount_percent' => $discountPct,
                        'line_total'       => $lineTotal,
                    ];
                }

                $discountAmount = 0;
                $taxAmount = round($subtotal * 0.12, 2);
                $shippingFee = $subtotal > 20000 ? 0 : 350;
                $totalAmount = round($subtotal - $discountAmount + $taxAmount + $shippingFee, 2);

                $status = $isCurrentMonth && $i >= $ordersThisMonth - 3 ? 'Processing' : 'Completed';

                $orderId = DB::table('sales_orders')->insertGetId([
                    'customer_id'     => $customerIds[array_rand($customerIds)],
                    'sales_rep_id'    => $rep['user_id'],
                    'order_date'      => $orderDate->toDateString(),
                    'order_status'    => $status,
                    'subtotal'        => $subtotal,
                    'discount_amount' => $discountAmount,
                    'tax_amount'      => $taxAmount,
                    'shipping_fee'    => $shippingFee,
                    'total_amount'    => $totalAmount,
                    'tax_region_id'   => $taxRegionId,
                    'on_hold'         => 0,
                    'created_at'      => $orderDate,
                    'updated_at'      => $orderDate,
                ]);

                foreach ($pendingItems as $item) {
                    $itemRows[] = $item + [
                        'sales_order_id' => $orderId,
                        'created_at'     => $orderDate,
                        'updated_at'     => $orderDate,
                    ];
                }
            }

            if (! empty($itemRows)) {
                foreach (array_chunk($itemRows, 200) as $chunk) {
                    DB::table('sales_order_items')->insert($chunk);
                }
            }
        }
    }

    private function seedTargets(array $reps, array $regionIds, array $productIds): void
    {
        $currentPeriod = PeriodHelper::current();
        [$start, $end] = PeriodHelper::bounds($currentPeriod);
        $prevStart = (clone $start)->subMonths(3);
        $prevPeriod = $prevStart->year . '-Q' . $prevStart->quarter;

        // Use the last 3 closed months of actuals as a baseline, then set
        // quota = baseline * a stretch factor, so attainment isn't
        // suspiciously always 100%.
        $stretch = 1.12;

        foreach ([$prevPeriod, $currentPeriod] as $period) {
            [$pStart, $pEnd] = PeriodHelper::bounds($period);

            // Rep targets
            foreach ($reps as $rep) {
                $baseline = DB::table('sales_orders')
                    ->where('sales_rep_id', $rep['user_id'])
                    ->whereBetween('order_date', [(clone $pStart)->subMonths(3)->toDateString(), $pStart->copy()->subDay()->toDateString()])
                    ->sum('total_amount');

                $target = max(50000, round($baseline * $stretch, -3));

                DB::table('sales_performance_reporting_rep_targets')->updateOrInsert(
                    ['rep_id' => $rep['sales_rep_id'], 'period' => $period],
                    ['target_amount' => $target, 'actual_amount' => 0]
                );
            }

            // Region targets
            foreach ($regionIds as $name => $regionId) {
                $repIds = collect($reps)->where('region_id', $regionId)->pluck('user_id');
                $baseline = DB::table('sales_orders')
                    ->whereIn('sales_rep_id', $repIds)
                    ->whereBetween('order_date', [(clone $pStart)->subMonths(3)->toDateString(), $pStart->copy()->subDay()->toDateString()])
                    ->sum('total_amount');

                $target = max(80000, round($baseline * $stretch, -3));

                DB::table('sales_performance_reporting_region_targets')->updateOrInsert(
                    ['region_id' => $regionId, 'period' => $period],
                    ['target_amount' => $target, 'actual_amount' => 0]
                );
            }

            // Product targets — only the top ~15 sellers get a formal quota,
            // matching how a real ERP wouldn't track every SKU individually.
            $topProducts = DB::table('sales_order_items')
                ->select('product_id', DB::raw('SUM(line_total) as total'))
                ->groupBy('product_id')
                ->orderByDesc('total')
                ->limit(15)
                ->pluck('total', 'product_id');

            foreach ($topProducts as $productId => $baseline) {
                $target = max(10000, round($baseline * ($stretch / 5), -2)); // /5: baseline covers ~15mo, target is 1 quarter

                DB::table('sales_performance_reporting_product_targets')->updateOrInsert(
                    ['product_id' => $productId, 'period' => $period],
                    ['target_amount' => $target, 'actual_amount' => 0]
                );
            }
        }

        // Fill in real actual_amount for both periods using the same
        // service the controllers use, so seeded data and live data behave
        // identically.
        $sync = new TargetSyncService();
        $sync->sync($prevPeriod);
        $sync->sync($currentPeriod);
    }
}

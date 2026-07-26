<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Modules\SalesOrderManagement\Services\AutoQuotationService;
use Illuminate\Console\Command;

/**
 * One-time backfill: generates/refreshes a Sales Quotation for every
 * existing CRM order that doesn't have one yet (or whose items have
 * changed since it was last synced).
 *
 * Going forward, new orders sync automatically via OrderObserver /
 * OrderItemObserver — this command only exists to catch up orders that
 * were seeded/placed before that automation was added, so the Sales
 * Quotations screen has real, matching demo data to show.
 *
 * Usage: php artisan quotations:sync-from-orders
 */
class SyncQuotationsFromOrders extends Command
{
    protected $signature = 'quotations:sync-from-orders {--fresh : Re-sync every order, even ones that already have a quotation}';

    protected $description = 'Backfill Sales Quotations from existing CRM orders (for demo/catch-up purposes)';

    public function handle(AutoQuotationService $service): int
    {
        $query = Order::with('items')->orderBy('order_id');

        if (! $this->option('fresh')) {
            // Fast path: only touch orders that don't have a synced
            // quotation yet, so re-running this doesn't redo work every time.
            $query->whereDoesntHave('quotation');
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->warn('No CRM orders found — nothing to sync.');

            return self::SUCCESS;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        $this->withProgressBar($orders, function (Order $order) use ($service, &$created, &$updated, &$skipped) {
            $quotation = $service->syncFromOrder($order);

            if (! $quotation) {
                $skipped++;
            } elseif ($quotation->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        });

        $this->newLine(2);
        $this->info("Done. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}.");

        return self::SUCCESS;
    }
}

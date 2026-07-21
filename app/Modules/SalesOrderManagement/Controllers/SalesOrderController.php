<?php

namespace App\Modules\SalesOrderManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Modules\SalesOrderManagement\Models\Invoice;
use App\Modules\SalesOrderManagement\Models\PricingRule;
use App\Models\Product;
use App\Modules\SalesOrderManagement\Models\SalesOrder;
use App\Modules\SalesOrderManagement\Models\SalesOrderItem;
use App\Modules\SalesOrderManagement\Models\SalesQuotation;
use App\Modules\SalesOrderManagement\Models\SalesQuotationItem;
use App\Models\TaxRegion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    /** order_status flow used by the "advance" action */
    protected array $orderStatusFlow = ['Pending', 'Processing', 'Shipped', 'Delivered'];

    /**
     * Resolve which TaxRegion applies. Falls back to the region flagged
     * is_default (seeded as Philippines 12%) if none was requested, and to
     * the first region in the table if somehow nothing is flagged default.
     */
    protected function resolveTaxRegion(?int $taxRegionId): TaxRegion
    {
        return TaxRegion::find($taxRegionId)
            ?? TaxRegion::where('is_default', true)->first()
            ?? TaxRegion::firstOrFail();
    }

    /**
     * Mocked "current user" — until the real Users/Auth module is wired up.
     * Auth::id() will be null outside a logged-in session, so we fall back to
     * a real row in the users table (creating one if the app was never seeded)
     * instead of a hardcoded id that may not exist and would violate the
     * created_by / sales_rep_id foreign key.
     */
    protected function defaultUserId(): int
    {
        return Auth::id() ?? User::query()->value('id') ?? User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@fanatec.local',
        ])->id;
    }

    /**
     * Everything the frontend needs on first load.
     */
    public function bootstrap()
    {
        return response()->json([
            'products'     => Product::all(['id', 'name', 'category', 'unit_price'])
                                ->map(fn ($p) => [
                                    'product_id' => $p->id,
                                    'name'       => $p->name,
                                    'category'   => $p->category,
                                    'unit_price' => (float) $p->unit_price,
                                ]),
            'customers'    => Customer::all(['customer_id', 'first_name', 'last_name'])
                                ->map(fn ($c) => [
                                    'customer_id' => $c->customer_id,
                                    'name'        => $c->full_name,
                                ]),
            'pricingRules' => PricingRule::orderByDesc('created_at')->get()
                                ->map(fn ($r) => $this->transformPricingRule($r)),
            'taxRegions'   => TaxRegion::orderByDesc('is_default')->orderBy('country')->get()
                                ->map(fn ($t) => $this->transformTaxRegion($t)),
            'quotations'   => SalesQuotation::with(['items.product', 'customer', 'taxRegion'])
                                ->orderBy('quotation_id')
                                ->get()
                                ->map(fn ($q) => $this->transformQuotation($q)),
            'orders'       => SalesOrder::with(['items.product', 'customer', 'invoices', 'taxRegion'])
                                ->orderBy('sales_order_id')
                                ->get()
                                ->map(fn ($o) => $this->transformOrder($o)),
            'invoices'     => Invoice::with(['salesOrder', 'customer'])
                                ->orderBy('invoice_id')
                                ->get()
                                ->map(fn ($i) => $this->transformInvoice($i)),
        ]);
    }

    /* ───────────────────────── Sales Quotations ───────────────────────── */

    public function storeQuotation(Request $request)
    {
        $data = $request->validate([
            'customer'            => 'required|string',
            'valid_until'         => 'nullable|date',
            'tax_region_id'       => 'nullable|integer|exists:tax_regions,id',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $customer = Customer::firstOrCreate(['name' => $data['customer']]);
        $taxRegion = $this->resolveTaxRegion($data['tax_region_id'] ?? null);

        $quotation = DB::transaction(function () use ($data, $customer, $taxRegion) {
            [$subtotal, $discountTotal, $lines] = $this->priceLines($data['items']);
            $taxable = $subtotal - $discountTotal;
            $tax     = round($taxable * ((float) $taxRegion->vat_rate / 100), 2);

            $quotation = SalesQuotation::create([
                'customer_id'      => $customer->customer_id,
                'tax_region_id'    => $taxRegion->id,
                'quotation_date'   => now()->toDateString(),
                'valid_until'      => $data['valid_until'] ?? now()->addDays(15)->toDateString(),
                'status'           => 'Draft',
                'subtotal'         => $subtotal,
                'discount_amount'  => $discountTotal,
                'tax_amount'       => $tax,
                'total_amount'     => round($taxable + $tax, 2),
                'created_by'       => $this->defaultUserId(),
            ]);

            foreach ($lines as $line) {
                SalesQuotationItem::create(array_merge($line, ['quotation_id' => $quotation->quotation_id]));
            }

            return $quotation;
        });

        return response()->json($this->transformQuotation($quotation->fresh(['items.product', 'customer', 'taxRegion'])));
    }

    public function sendQuotation(SalesQuotation $quotation)
    {
        $quotation->update(['status' => 'Sent']);

        return response()->json($this->transformQuotation($quotation->fresh(['items.product', 'customer', 'taxRegion'])));
    }

    public function rejectQuotation(SalesQuotation $quotation)
    {
        $quotation->update(['status' => 'Rejected']);

        return response()->json($this->transformQuotation($quotation->fresh(['items.product', 'customer', 'taxRegion'])));
    }

    /**
     * Accepting a quotation creates the confirmed Sales Order + its items.
     */
    public function acceptQuotation(SalesQuotation $quotation)
    {
        $quotation->load('items');

        $order = DB::transaction(function () use ($quotation) {
            $quotation->update(['status' => 'Accepted']);

            $order = SalesOrder::create([
                'quotation_id'     => $quotation->quotation_id,
                'customer_id'      => $quotation->customer_id,
                'tax_region_id'    => $quotation->tax_region_id,
                'sales_rep_id'     => $this->defaultUserId(),
                'order_date'       => now()->toDateString(),
                'order_status'     => 'Pending',
                'subtotal'         => $quotation->subtotal,
                'discount_amount'  => $quotation->discount_amount,
                'tax_amount'       => $quotation->tax_amount,
                'shipping_fee'     => 0,
                'total_amount'     => $quotation->total_amount,
            ]);

            foreach ($quotation->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id'    => $order->sales_order_id,
                    'product_id'        => $item->product_id,
                    'quantity'          => $item->quantity,
                    'unit_price'        => $item->unit_price,
                    'discount_percent'  => $item->discount_percent,
                    'line_total'        => $item->line_total,
                ]);
            }

            return $order;
        });

        return response()->json($this->transformOrder($order->fresh(['items.product', 'customer', 'invoices', 'taxRegion'])));
    }

    /* ───────────────────────── Sales Orders ───────────────────────── */

    public function advanceOrder(SalesOrder $order)
    {
        if ($order->on_hold) {
            return response()->json(['message' => 'Order is on hold. Resume it before advancing.'], 422);
        }

        $idx = array_search($order->order_status, $this->orderStatusFlow);

        if ($idx !== false && $idx < count($this->orderStatusFlow) - 1) {
            $order->order_status = $this->orderStatusFlow[$idx + 1];
            $order->save();
        }

        return response()->json($this->transformOrder($order->fresh(['items.product', 'customer', 'invoices', 'taxRegion'])));
    }

    public function cancelOrder(SalesOrder $order)
    {
        $order->update(['order_status' => 'Cancelled', 'on_hold' => false]);

        return response()->json($this->transformOrder($order->fresh(['items.product', 'customer', 'invoices', 'taxRegion'])));
    }

    /**
     * Pause fulfillment on a Pending/Processing/Shipped order without losing
     * its place in the pipeline (e.g. payment issue, stock hold).
     */
    public function holdOrder(SalesOrder $order)
    {
        if (in_array($order->order_status, ['Delivered', 'Cancelled'], true)) {
            return response()->json(['message' => 'Delivered or cancelled orders cannot be put on hold.'], 422);
        }

        $order->update(['on_hold' => true]);

        return response()->json($this->transformOrder($order->fresh(['items.product', 'customer', 'invoices', 'taxRegion'])));
    }

    /**
     * Take a held order off hold and resume it at its current order_status.
     */
    public function resumeOrder(SalesOrder $order)
    {
        $order->update(['on_hold' => false]);

        return response()->json($this->transformOrder($order->fresh(['items.product', 'customer', 'invoices', 'taxRegion'])));
    }

    /* ───────────────────────── Invoices ───────────────────────── */

    public function generateInvoice(SalesOrder $order)
    {
        if ($order->invoices()->exists()) {
            return response()->json($this->transformInvoice($order->invoices()->first()->fresh(['salesOrder', 'customer'])));
        }

        $invoice = Invoice::create([
            'sales_order_id' => $order->sales_order_id,
            'customer_id'    => $order->customer_id,
            'invoice_date'   => now()->toDateString(),
            'due_date'       => now()->addDays(20)->toDateString(),
            'subtotal'       => $order->subtotal - ($order->discount_amount ?? 0),
            'vat_amount'     => $order->tax_amount,
            'total_amount'   => $order->total_amount + ($order->shipping_fee ?? 0),
            'invoice_status' => 'Pending',
        ]);

        return response()->json($this->transformInvoice($invoice->fresh(['salesOrder', 'customer'])));
    }

    public function payInvoice(Invoice $invoice)
    {
        $invoice->update(['invoice_status' => 'Paid']);

        return response()->json($this->transformInvoice($invoice->fresh(['salesOrder', 'customer'])));
    }

    /* ───────────────────────── Pricing Rules ───────────────────────── */

    public function storePricingRule(Request $request)
    {
        $data = $request->validate([
            'rule_name'      => 'required|string|max:150',
            'rule_type'      => 'required|in:Percentage,Fixed Amount',
            'discount_value' => 'required|numeric|min:0',
            'applicable_to'  => 'required|in:Product,Category,Customer Segment,Order-wide',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'status'         => 'required|in:Active,Inactive',
        ]);

        $rule = PricingRule::create($data);

        return response()->json($this->transformPricingRule($rule));
    }

    public function togglePricingRule(PricingRule $pricingRule)
    {
        $pricingRule->update([
            'status' => $pricingRule->status === 'Active' ? 'Inactive' : 'Active',
        ]);

        return response()->json($this->transformPricingRule($pricingRule));
    }

    public function deletePricingRule(PricingRule $pricingRule)
    {
        $pricingRule->delete();

        return response()->json(['deleted' => true]);
    }

    /* Tax Regions are owned by the Finance module. SOM only reads them
       (see resolveTaxRegion() and the tax_region_id FK on quotations/orders)
       and no longer exposes create/delete endpoints for them here. */

    /* ───────────────────────── Helpers ───────────────────────── */

    /**
     * Price a set of {product_id, quantity, discount_percent} lines against the
     * (mocked) Inventory product catalog. Returns [subtotal, discountTotal, lines[]].
     */
    protected function priceLines(array $items): array
    {
        $subtotal = 0;
        $discountTotal = 0;
        $lines = [];

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $qty     = $item['quantity'];
            $pct     = $item['discount_percent'] ?? 0;

            $lineGross = $product->unit_price * $qty;
            $lineDisc  = round($lineGross * ($pct / 100), 2);
            $lineTotal = round($lineGross - $lineDisc, 2);

            $subtotal      += $lineGross;
            $discountTotal += $lineDisc;

            $lines[] = [
                'product_id'       => $product->id,
                'quantity'         => $qty,
                'unit_price'       => $product->unit_price,
                'discount_percent' => $pct,
                'line_total'       => $lineTotal,
            ];
        }

        return [round($subtotal, 2), round($discountTotal, 2), $lines];
    }

    protected function transformQuotation(SalesQuotation $q): array
    {
        return [
            'id'         => 'QT-' . str_pad($q->quotation_id, 4, '0', STR_PAD_LEFT),
            'quotationId'=> $q->quotation_id,
            'customer'   => $q->customer->full_name,
            'customerAddress' => $q->customer->address ?? null, // TODO: canonical Customer has no address field — see change log
            'date'       => $q->quotation_date->toDateString(),
            'validUntil' => $q->valid_until->toDateString(),
            'status'     => $q->status,
            'taxRegionId'   => $q->tax_region_id,
            'taxCountry'    => optional($q->taxRegion)->country,
            'taxRatePct'    => $q->taxRegion ? (float) $q->taxRegion->vat_rate : null,
            'items'      => $q->items->map(fn ($i) => [
                'product'  => $i->product->name,
                'qty'      => $i->quantity,
                'price'    => (float) $i->unit_price,
                'discount' => (float) $i->discount_percent,
                'lineTotal'=> (float) $i->line_total,
            ])->values(),
            'subtotal' => (float) $q->subtotal,
            'discount' => (float) $q->discount_amount,
            'tax'      => (float) $q->tax_amount,
            'total'    => (float) $q->total_amount,
        ];
    }

    protected function transformOrder(SalesOrder $o): array
    {
        return [
            'id'          => 'SO-' . str_pad($o->sales_order_id, 4, '0', STR_PAD_LEFT),
            'orderId'     => $o->sales_order_id,
            'quotationId' => $o->quotation_id,
            'customer'    => $o->customer->full_name,
            'customerAddress' => $o->customer->address ?? null, // TODO: canonical Customer has no address field — see change log
            'date'        => $o->order_date->toDateString(),
            'status'      => $o->order_status,
            'held'        => (bool) $o->on_hold,
            'taxRegionId'   => $o->tax_region_id,
            'taxCountry'    => optional($o->taxRegion)->country,
            'taxRatePct'    => $o->taxRegion ? (float) $o->taxRegion->vat_rate : null,
            'items'       => $o->items->map(fn ($i) => [
                'productId'=> $i->product_id,
                'product'  => $i->product->name,
                'qty'      => $i->quantity,
                'price'    => (float) $i->unit_price,
                'discount' => (float) $i->discount_percent,
                'lineTotal'=> (float) $i->line_total,
            ])->values(),
            'subtotal'    => (float) $o->subtotal,
            'discount'    => (float) $o->discount_amount,
            'tax'         => (float) $o->tax_amount,
            'shipping'    => (float) $o->shipping_fee,
            'total'       => (float) $o->total_amount,
            'hasInvoice'  => $o->invoices->isNotEmpty(),
        ];
    }

    protected function transformInvoice(Invoice $i): array
    {
        return [
            'id'         => 'INV-' . str_pad($i->invoice_id, 4, '0', STR_PAD_LEFT),
            'invoiceId'  => $i->invoice_id,
            'orderId'    => 'SO-' . str_pad($i->sales_order_id, 4, '0', STR_PAD_LEFT),
            'customer'   => $i->customer->full_name,
            'date'       => $i->invoice_date->toDateString(),
            'due'        => $i->due_date->toDateString(),
            'subtotal'   => (float) $i->subtotal,
            'vat'        => (float) $i->vat_amount,
            'total'      => (float) $i->total_amount,
            'status'     => $i->invoice_status,
        ];
    }

    protected function transformPricingRule(PricingRule $r): array
    {
        return [
            'id'            => $r->pricing_rule_id,
            'name'          => $r->rule_name,
            'type'          => $r->rule_type,
            'value'         => (float) $r->discount_value,
            'applicableTo'  => $r->applicable_to,
            'startDate'     => optional($r->start_date)->toDateString(),
            'endDate'       => optional($r->end_date)->toDateString(),
            'status'        => $r->status,
        ];
    }

    protected function transformTaxRegion(TaxRegion $t): array
    {
        return [
            'id'        => $t->id,
            'country'   => $t->country,
            'vat'       => (float) $t->vat_rate,
            'isDefault' => (bool) $t->is_default,
        ];
    }
}

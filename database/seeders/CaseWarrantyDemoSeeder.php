<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Modules\ASCM\Models\CaseNote;
use App\Modules\ASCM\Models\CaseStatusHistory;
use App\Modules\ASCM\Models\SupportCase;
use App\Modules\ASCM\Models\WarrantyClaim;
use App\Modules\ASCM\Models\WarrantyClaimNote;
use App\Modules\ASCM\Models\WarrantyRegistration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CaseWarrantyDemoSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::with('orders')->inRandomOrder()->take(14)->get();
        $staff = User::inRandomOrder()->take(5)->get();

        if ($customers->isEmpty() || $staff->isEmpty()) {
            $this->command?->warn('Need at least a few customers and users in the DB before running this seeder.');
            return;
        }

        $categories = ['Technical', 'Billing', 'Shipping', 'Returns', 'Product Defect', 'Warranty'];
        $priorities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['pending', 'open', 'resolved', 'closed'];
        $modes = ['Chat', 'Email', 'Phone', 'SMS'];
        $warrantyTypes = ['standard', 'extended', 'commercial'];
        $requestedActions = ['repair', 'replace', 'refund', 'credit'];

        foreach ($customers as $index => $customer) {
            $order = $customer->orders->isNotEmpty() ? $customer->orders->random() : null;
            $orderItem = $order
                ? OrderItem::where('order_id', $order->order_id)->inRandomOrder()->first()
                : null;
            $product = $orderItem
                ? Product::find($orderItem->product_id)
                : Product::inRandomOrder()->first();

            $priority = $priorities[array_rand($priorities)];
            $status = $statuses[array_rand($statuses)];
            $category = $categories[array_rand($categories)];
            $agent = $staff->random();
            $productName = $product->name ?? 'their product';

            // --- Case ---
            $case = SupportCase::create([
                'customer_id' => $customer->customer_id,
                'order_id' => $order?->order_id,
                'order_item_id' => $orderItem?->id,
                'product_id' => $product?->id,
                'category' => $category,
                'priority' => $priority,
                'status' => $status,
                'assigned_to' => $agent->id,
                'sla_due_at' => now()->addDays(rand(1, 7)),
                'resolved_at' => in_array($status, ['resolved', 'closed']) ? now()->subDays(rand(0, 3)) : null,
                'closed_at' => $status === 'closed' ? now()->subDay() : null,
            ]);

            CaseNote::create([
                'case_id' => $case->id,
                'author_id' => $agent->id,
                'entry_type' => 'internal_note',
                'visibility' => 'internal',
                'title' => 'Initial triage',
                'body' => "Reviewed the customer's {$category} issue regarding {$productName}. Priority set to {$priority}.",
            ]);

            CaseNote::create([
                'case_id' => $case->id,
                'author_id' => $agent->id,
                'entry_type' => 'customer_note',
                'visibility' => 'customer_visible',
                'title' => 'Update sent to customer',
                'body' => match ($category) {
                    'Technical' => "We've reviewed your {$productName} issue and are working on a fix — we'll follow up shortly with next steps.",
                    'Billing' => "We've received your billing concern and are reviewing your account. We'll update you as soon as we have a resolution.",
                    'Shipping' => "We're looking into the shipping issue with your order and will confirm next steps shortly.",
                    'Returns' => "Your return request has been received — we'll send confirmation and instructions shortly.",
                    'Product Defect' => "Thanks for reporting the defect with your {$productName}. We're arranging next steps and will be in touch.",
                    default => "We've logged your warranty concern regarding {$productName} and are reviewing it now.",
                },
            ]);

            CaseStatusHistory::create([
                'case_id' => $case->id,
                'from_status' => null,
                'to_status' => $status,
                'changed_by' => $agent->id,
                'note' => 'Case created.',
            ]);

            // --- Linked communication log (ticket_id ties back to the case) ---
            $logId = DB::table('crm_communication_logs')->insertGetId([
                'customer_id' => $customer->customer_id,
                'issue' => "{$category}: {$productName}",
                'details' => "Customer reported a {$category} issue related to case {$case->case_number}.",
                'log_date' => now()->subDays(rand(0, 5))->toDateString(),
                'mode' => $modes[array_rand($modes)],
                'status' => $status === 'closed' ? 'Closed' : 'Open',
                'ticket_id' => $case->case_number,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // --- Mock chat thread under that log ---
            $thread = [
                ['sender' => 'customer', 'message' => "Hi, I'm having an issue with my {$productName}. Can someone help?"],
                ['sender' => 'agent', 'message' => "Hi {$customer->first_name}, sorry to hear that. I've opened case {$case->case_number} for you — could you describe what's happening?"],
                ['sender' => 'customer', 'message' => match ($category) {
                    'Technical' => "It won't power on properly, and I've already tried resetting it.",
                    'Billing' => "I was charged twice for the same order and need this corrected.",
                    'Shipping' => "My package shows delivered but I never received it.",
                    'Returns' => "I'd like to return this since it doesn't fit my setup.",
                    'Product Defect' => "There's a defect out of the box — it doesn't work as expected.",
                    default => "I think this might be a warranty issue, it stopped working recently.",
                }],
                ['sender' => 'agent', 'message' => "Thanks for the details. I've logged this and set the priority to {$priority}. We'll follow up shortly."],
            ];

            if (in_array($status, ['resolved', 'closed'])) {
                $thread[] = ['sender' => 'agent', 'message' => "This has been marked as {$status}. Let us know if anything else comes up!"];
            }

            foreach ($thread as $i => $msg) {
                DB::table('crm_chat_messages')->insert([
                    'customer_id' => $customer->customer_id,
                    'sender' => $msg['sender'],
                    'message' => $msg['message'],
                    'sent_at' => now()->subDays(rand(0, 5))->addMinutes($i * 7),
                    'communication_log_id' => $logId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // --- Warranty registration + claim for roughly half the cases ---
            if ($index % 2 === 0 && $order && $product) {
                $registration = WarrantyRegistration::create([
                    'customer_id' => $customer->customer_id,
                    'order_id' => $order->order_id,
                    'order_item_id' => $orderItem?->id,
                    'product_id' => $product->id,
                    'serial_number' => strtoupper(Str::random(10)),
                    'warranty_type' => $warrantyTypes[array_rand($warrantyTypes)],
                    'coverage_start' => $order->created_at ?? now()->subMonths(6),
                    'coverage_end' => now()->addMonths(rand(1, 12)),
                    'coverage_status' => 'eligible',
                ]);

                $claim = WarrantyClaim::create([
                    'warranty_registration_id' => $registration->id,
                    'customer_id' => $customer->customer_id,
                    'case_id' => $case->id,
                    'issue_description' => "Reported defect with {$productName}, related to case {$case->case_number}.",
                    'requested_action' => $requestedActions[array_rand($requestedActions)],
                    'estimated_amount' => $product->price ? round($product->price * 0.3, 2) : 50,
                    'status' => 'submitted',
                ]);

                WarrantyClaimNote::create([
                    'warranty_claim_id' => $claim->id,
                    'author_id' => $agent->id,
                    'note_type' => 'general',
                    'body' => 'Claim opened pending review.',
                ]);
            }
        }

        $this->command?->info('Seeded ' . $customers->count() . ' cases with linked communication logs and chat threads.');
    }
}
<?php

namespace Database\Seeders;

use App\Modules\ASCM\Models\CaseAttachment;
use App\Modules\ASCM\Models\CaseNote;
use App\Modules\ASCM\Models\CaseStatusHistory;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Product;
use App\Modules\ASCM\Models\SupportCase;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaseManagementSeeder extends Seeder
{
    /**
     * Counts here are illustrative, not pulled from the stat cards in the
     * UI (those are still hardcoded in the Blade view). Adjust freely, or
     * wire the stat cards to real COUNT() queries once you're ready —
     * that's the actual fix for "the numbers on screen match the DB."
     *
     * Also responsible for the CRM communication-log + chat-thread that
     * used to be seeded separately (see seedCommunicationLog below) —
     * folded in here so every case gets one in the same pass instead of
     * needing a second seeder to run afterward. Absorbs what
     * CaseWarrantyDemoSeeder and CaseCommunicationLogSeeder used to do;
     * both were retired to avoid generating overlapping/duplicate case
     * rows (see WarrantySeeder for the equivalent on the claims side).
     */
    public function run(): void
    {
        $customers = Customer::with('orders')->get();
        $staff = User::all();

        $counts = [
            'open' => 18,
            'pending' => 7,
            'resolved' => 41,
            'closed' => 5,
        ];

        foreach ($counts as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $customer = $customers->random();

                // Tie the case to a real order/order item/product of this
                // customer when one exists, so cases read like they came
                // from an actual purchase rather than floating on just a
                // customer_id (this is what CaseWarrantyDemoSeeder used to
                // add on top of the plain factory-only version).
                $order = $customer->orders->isNotEmpty() ? $customer->orders->random() : null;
                $orderItem = $order
                    ? OrderItem::where('order_id', $order->order_id)->inRandomOrder()->first()
                    : null;
                $product = $orderItem
                    ? Product::find($orderItem->product_id)
                    : Product::inRandomOrder()->first();

                $case = SupportCase::factory()
                    ->status($status)
                    ->create([
                        'customer_id' => $customer->customer_id,
                        'order_id' => $order?->order_id,
                        'order_item_id' => $orderItem?->id,
                        'product_id' => $product?->id,
                        'assigned_to' => $staff->isNotEmpty() && fake()->boolean(70)
                            ? $staff->random()->id
                            : null,
                    ]);

                $this->seedNotesAndHistory($case, $staff);
                $this->seedCommunicationLog($case, $customer, $product);
            }
        }
    }

    /**
     * One crm_communication_logs row + a short crm_chat_messages thread
     * per case, linked back via ticket_id = case_number. Mirrors what
     * CaseCommunicationLogSeeder used to do as a separate pass — folded in
     * here so it can't be run out of order or double up against it.
     */
    private function seedCommunicationLog(SupportCase $case, Customer $customer, ?Product $product): void
    {
        $modes = ['Chat', 'Email', 'Phone', 'SMS'];
        $productName = $product->name ?? 'their product';

        $logId = DB::table('crm_communication_logs')->insertGetId([
            'customer_id' => $customer->customer_id,
            'issue' => "{$case->category} issue",
            'details' => "Customer reported a {$case->category} issue related to case {$case->case_number}.",
            'log_date' => now()->subDays(rand(0, 5))->toDateString(),
            'mode' => $modes[array_rand($modes)],
            'status' => in_array($case->status, ['resolved', 'closed']) ? 'Closed' : 'Open',
            'ticket_id' => $case->case_number,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customerName = $customer->first_name ?? 'there';

        $thread = [
            ['sender' => 'customer', 'message' => "Hi, I'm having an issue with {$productName} and wanted to follow up on case {$case->case_number}."],
            ['sender' => 'agent', 'message' => "Hi {$customerName}, thanks for reaching out. Let me take a look at this for you."],
            ['sender' => 'customer', 'message' => "Appreciate it — just let me know if you need anything else from my end."],
            ['sender' => 'agent', 'message' => "Will do! I've noted this under case {$case->case_number} and will follow up shortly."],
        ];

        if (in_array($case->status, ['resolved', 'closed'])) {
            $thread[] = ['sender' => 'agent', 'message' => "This case has been marked as {$case->status}. Let us know if anything else comes up!"];
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
    }

    private function seedNotesAndHistory(SupportCase $case, $staff): void
    {
        // Opening note.
        CaseNote::create([
            'case_id' => $case->id,
            'author_id' => null,
            'entry_type' => 'customer_note',
            'visibility' => 'customer_visible',
            'title' => 'Customer note',
            'body' => fake()->sentence(12),
        ]);

        // Internal follow-up, roughly half the time.
        if (fake()->boolean(50) && $staff->isNotEmpty()) {
            CaseNote::create([
                'case_id' => $case->id,
                'author_id' => $staff->random()->id,
                'entry_type' => 'internal_note',
                'visibility' => 'internal',
                'title' => 'Internal update',
                'body' => fake()->sentence(10),
            ]);
        }

        // One attachment, most of the time.
        if (fake()->boolean(60)) {
            CaseAttachment::create([
                'case_id' => $case->id,
                'uploaded_by' => $staff->isNotEmpty() ? $staff->random()->id : null,
                'file_name' => fake()->randomElement(['photo.jpg', 'invoice.pdf', 'screenshot.png']),
                'file_path' => 'attachments/' . fake()->uuid() . '.dat',
                'file_size' => fake()->numberBetween(20_000, 4_000_000),
                'mime_type' => fake()->randomElement(['image/jpeg', 'application/pdf', 'image/png']),
            ]);
        }

        // Status history: always a "created" entry, plus a transition if
        // the case has moved past pending.
        CaseStatusHistory::create([
            'case_id' => $case->id,
            'from_status' => null,
            'to_status' => 'pending',
            'changed_by' => null,
            'note' => 'Case created.',
        ]);

        if ($case->status !== 'pending') {
            CaseStatusHistory::create([
                'case_id' => $case->id,
                'from_status' => 'pending',
                'to_status' => $case->status,
                'changed_by' => $staff->isNotEmpty() ? $staff->random()->id : null,
                'note' => null,
            ]);
        }
    }
}

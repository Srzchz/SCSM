<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Modules\CRM\Models\Activity;
use App\Modules\CRM\Models\FollowUp;
use Illuminate\Database\Seeder;

class CrmDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::inRandomOrder()->take(5)->get();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found — seed customers first.');
            return;
        }

        $followUpNotes = [
            'Awaiting response about product feedback.',
            'Follow up on abandoned cart.',
            'Sent an incoming sale with 80% discount.',
            'Reminder to confirm delivery address.',
            'Check in after recent support ticket.',
        ];

        foreach ($customers as $i => $customer) {
            FollowUp::create([
                'customer_id' => $customer->customer_id,
                'note' => $followUpNotes[$i] ?? 'Scheduled follow-up.',
                'due_date' => now()->addDays(rand(2, 20)),
                'status' => 'Open',
            ]);
        }

        $activityTemplates = [
            ['type' => 'customer', 'title' => 'New customer registered', 'note' => null],
            ['type' => 'order', 'title' => 'Order placed', 'note' => null],
            ['type' => 'review', 'title' => 'Review submitted', 'note' => null],
            ['type' => 'support', 'title' => 'Support ticket opened', 'note' => null],
            ['type' => 'order', 'title' => 'Order delivered', 'note' => null],
        ];

        foreach ($customers as $i => $customer) {
            $template = $activityTemplates[$i] ?? $activityTemplates[0];

            Activity::create([
                'customer_id' => $customer->customer_id,
                'type' => $template['type'],
                'title' => $template['title'],
                'note' => $customer->full_name,
            ]);
        }

        $this->command->info('Seeded ' . $customers->count() . ' follow-ups and ' . $customers->count() . ' activities.');
    }
}
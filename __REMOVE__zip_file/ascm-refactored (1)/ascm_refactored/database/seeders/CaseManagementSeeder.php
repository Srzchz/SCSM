<?php

namespace Database\Seeders;

use App\Modules\ASCM\Models\CaseAttachment;
use App\Modules\ASCM\Models\CaseNote;
use App\Modules\ASCM\Models\CaseStatusHistory;
use App\Models\Customer;
use App\Modules\ASCM\Models\SupportCase;
use App\Models\User;
use Illuminate\Database\Seeder;

class CaseManagementSeeder extends Seeder
{
    /**
     * Counts here are illustrative, not pulled from the stat cards in the
     * UI (those are still hardcoded in the Blade view). Adjust freely, or
     * wire the stat cards to real COUNT() queries once you're ready —
     * that's the actual fix for "the numbers on screen match the DB."
     */
    public function run(): void
    {
        $customers = Customer::all();
        $staff = User::all();

        $counts = [
            'open' => 18,
            'pending' => 7,
            'resolved' => 41,
            'closed' => 5,
        ];

        foreach ($counts as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $case = SupportCase::factory()
                    ->status($status)
                    ->create([
                        'customer_id' => $customers->random()->customer_id,
                        'assigned_to' => $staff->isNotEmpty() && fake()->boolean(70)
                            ? $staff->random()->id
                            : null,
                    ]);

                $this->seedNotesAndHistory($case, $staff);
            }
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

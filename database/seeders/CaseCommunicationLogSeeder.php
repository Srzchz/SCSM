<?php

namespace Database\Seeders;

use App\Modules\ASCM\Models\SupportCase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaseCommunicationLogSeeder extends Seeder
{
    public function run(): void
    {
        $modes = ['Chat', 'Email', 'Phone', 'SMS'];

        SupportCase::with('customer')->chunk(50, function ($cases) use ($modes) {
            foreach ($cases as $case) {
                // Skip cases that already have a log tied to them.
                $exists = DB::table('crm_communication_logs')
                    ->where('ticket_id', $case->case_number)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $logId = DB::table('crm_communication_logs')->insertGetId([
                    'customer_id' => $case->customer_id,
                    'issue' => "{$case->category} issue",
                    'details' => "Customer reported a {$case->category} issue related to case {$case->case_number}.",
                    'log_date' => now()->subDays(rand(0, 5))->toDateString(),
                    'mode' => $modes[array_rand($modes)],
                    'status' => in_array($case->status, ['resolved', 'closed']) ? 'Closed' : 'Open',
                    'ticket_id' => $case->case_number,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $customerName = $case->customer->first_name ?? 'there';

                $thread = [
                    ['sender' => 'customer', 'message' => "Hi, I'm having an issue and wanted to follow up on case {$case->case_number}."],
                    ['sender' => 'agent', 'message' => "Hi {$customerName}, thanks for reaching out. Let me take a look at this for you."],
                    ['sender' => 'customer', 'message' => "Appreciate it — just let me know if you need anything else from my end."],
                    ['sender' => 'agent', 'message' => "Will do! I've noted this under case {$case->case_number} and will follow up shortly."],
                ];

                if (in_array($case->status, ['resolved', 'closed'])) {
                    $thread[] = ['sender' => 'agent', 'message' => "This case has been marked as {$case->status}. Let us know if anything else comes up!"];
                }

                foreach ($thread as $i => $msg) {
                    DB::table('crm_chat_messages')->insert([
                        'customer_id' => $case->customer_id,
                        'sender' => $msg['sender'],
                        'message' => $msg['message'],
                        'sent_at' => now()->subDays(rand(0, 5))->addMinutes($i * 7),
                        'communication_log_id' => $logId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        $this->command?->info('Linked communication logs and chat threads to all existing cases.');
    }
}
<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Modules\CRM\Models\Activity;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CrmCustomerSeeder extends Seeder
{
    /**
     * Canonical order `status` only has: pending, processing, completed,
     * cancelled. The old demo data used 'Delivered'/'Pending' — mapped here
     * rather than rewritten by hand across every order line below.
     */
    private const STATUS_MAP = [
        'Delivered' => 'completed',
        'Pending' => 'pending',
    ];

    public function run(): void
    {
        $today = Carbon::now();

        $customers = [
            [
                'name' => 'Charles Nodalo',
                'email' => 'charlesnodalo@gmail.com',
                'phone' => '09123456789',
                'address' => 'Panabatan 1, Trece, Cavite',
                'dob' => '2000-08-23',
                'customer_since' => $today->copy()->subMonths(6)->format('Y-m-d'),
                'customer_type' => 'VIP',
                'preferred_channel' => 'Email, SMS',
                'clv' => 1269.00,
                'orders' => [
                    ['days_ago' => 1, 'order_number' => 'PS0023', 'quantity' => 2, 'price' => 123.00, 'total' => 246.00, 'status' => 'Delivered'],
                    ['days_ago' => 9, 'order_number' => 'PS0022', 'quantity' => 1, 'price' => 137.00, 'total' => 137.00, 'status' => 'Delivered'],
                    ['days_ago' => 20, 'order_number' => 'PS0021', 'quantity' => 3, 'price' => 99.00, 'total' => 297.00, 'status' => 'Delivered'],
                    ['days_ago' => 30, 'order_number' => 'PS0020', 'quantity' => 1, 'price' => 145.00, 'total' => 145.00, 'status' => 'Delivered'],
                    ['days_ago' => 42, 'order_number' => 'PS0019', 'quantity' => 2, 'price' => 112.00, 'total' => 224.00, 'status' => 'Delivered'],
                    ['days_ago' => 50, 'order_number' => 'PS0018', 'quantity' => 1, 'price' => 103.00, 'total' => 103.00, 'status' => 'Delivered'],
                    ['days_ago' => 58, 'order_number' => 'PS0017', 'quantity' => 1, 'price' => 117.00, 'total' => 117.00, 'status' => 'Delivered'],
                ],
                'logs' => [
                    [
                        'issue' => 'Damaged', 'details' => 'Item arrived with a cracked case', 'days_ago' => 2, 'mode' => 'Chat', 'status' => 'New',
                        'chats' => [
                            ['sender' => 'customer', 'message' => 'My item is damaged when it arrived', 'hours_ago' => 48],
                            ['sender' => 'agent', 'message' => 'Sorry to hear that! Can you send a photo so we can process a replacement?', 'hours_ago' => 47],
                        ],
                    ],
                    [
                        'issue' => 'Missing Item', 'details' => 'Order marked delivered but box was empty', 'days_ago' => 8, 'mode' => 'Chat', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => 'My order says delivered but the box was empty', 'hours_ago' => 190],
                            ['sender' => 'agent', 'message' => "That's not right, we'll send a replacement right away.", 'hours_ago' => 189],
                            ['sender' => 'customer', 'message' => 'Thank you, appreciate the quick help!', 'hours_ago' => 188],
                        ],
                    ],
                    [
                        'issue' => 'Order Status', 'details' => 'Asking for tracking update on a recent order', 'days_ago' => 12, 'mode' => 'Chat', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => "Hi, can you check the status of order #PS0021? It hasn't moved in tracking for 3 days.", 'hours_ago' => 260],
                            ['sender' => 'agent', 'message' => "Checking now — it's with the courier and should update within 24 hours.", 'hours_ago' => 259],
                            ['sender' => 'customer', 'message' => 'Got it, thanks for checking!', 'hours_ago' => 258],
                        ],
                    ],
                    [
                        'issue' => 'Promo Code', 'details' => 'Discount code not applying at checkout', 'days_ago' => 25, 'mode' => 'Email', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => 'The code WELCOME10 says invalid at checkout, can you help?', 'hours_ago' => 600],
                            ['sender' => 'agent', 'message' => "That code expired last week, but here's a fresh one: SAVE10 valid for 7 days.", 'hours_ago' => 598],
                        ],
                    ],
                ],
                'follow_up' => ['note' => 'an incoming sale with 80% discount', 'due_in_days' => 20],
            ],
            [
                'name' => 'John Yevs Bryan Suico',
                'email' => 'johnyevsbryansuico@gmail.com',
                'phone' => '09187654321',
                'address' => 'Salawag, Dasmariñas, Cavite',
                'dob' => '1999-03-02',
                'customer_since' => $today->copy()->subMonths(2)->format('Y-m-d'),
                'customer_type' => 'New Customer',
                'preferred_channel' => 'Email',
                'clv' => 482.65,
                'orders' => [
                    ['days_ago' => 2, 'order_number' => 'PS0031', 'quantity' => 1, 'price' => 120.00, 'total' => 120.00, 'status' => 'Delivered'],
                    ['days_ago' => 16, 'order_number' => 'PS0030', 'quantity' => 1, 'price' => 118.00, 'total' => 118.00, 'status' => 'Delivered'],
                ],
                'logs' => [
                    [
                        'issue' => 'Delayed', 'details' => 'Delivery running later than the estimated date', 'days_ago' => 15, 'mode' => 'Chat', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => 'When will my order arrive?', 'hours_ago' => 30],
                            ['sender' => 'agent', 'message' => "It's out for delivery, should reach you today.", 'hours_ago' => 29],
                        ],
                    ],
                    [
                        'issue' => 'Sizing Question', 'details' => 'Asking about fit before ordering', 'days_ago' => 40, 'mode' => 'Chat', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => 'Does this run true to size or should I size up?', 'hours_ago' => 900],
                            ['sender' => 'agent', 'message' => 'It runs slightly small — we recommend sizing up by one.', 'hours_ago' => 899],
                            ['sender' => 'customer', 'message' => 'Perfect, thank you!', 'hours_ago' => 898],
                        ],
                    ],
                    [
                        'issue' => 'Payment Failed', 'details' => 'Card declined during checkout', 'days_ago' => 55, 'mode' => 'Call', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => 'My payment keeps failing, can you check if something is wrong on your end?', 'hours_ago' => 1300],
                            ['sender' => 'agent', 'message' => 'Everything looks fine here — it may be your bank blocking the charge. Try again or use GCash.', 'hours_ago' => 1298],
                        ],
                    ],
                ],
                'follow_up' => ['note' => 'an incoming sale with 80% discount', 'due_in_days' => 20],
            ],
            [
                'name' => 'Arren Jade Lord Toong',
                'email' => 'arrenjadelordtoong@gmail.com',
                'phone' => '09201234567',
                'address' => 'Anabu, Imus, Cavite',
                'dob' => '2001-07-19',
                'customer_since' => $today->copy()->subMonth()->format('Y-m-d'),
                'customer_type' => 'New Customer',
                'preferred_channel' => 'SMS',
                'clv' => 336.20,
                'orders' => [
                    ['days_ago' => 3, 'order_number' => 'PS0040', 'quantity' => 1, 'price' => 112.00, 'total' => 112.00, 'status' => 'Delivered'],
                ],
                'logs' => [
                    [
                        'issue' => 'Awaiting feedback', 'details' => 'Requesting a review after delivery', 'days_ago' => 2, 'mode' => 'Chat', 'status' => 'New',
                        'chats' => [
                            ['sender' => 'agent', 'message' => "How was your recent order? We'd love your feedback!", 'hours_ago' => 40],
                        ],
                    ],
                    [
                        'issue' => 'Change Address', 'details' => 'Wants to update delivery address before shipping', 'days_ago' => 3, 'mode' => 'Chat', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => 'Can I change my delivery address? I put the wrong unit number.', 'hours_ago' => 68],
                            ['sender' => 'agent', 'message' => "Sure, what's the corrected address?", 'hours_ago' => 67],
                            ['sender' => 'customer', 'message' => 'Unit 12B instead of 12A, thank you!', 'hours_ago' => 66],
                        ],
                    ],
                    [
                        'issue' => 'Product Availability', 'details' => 'Asking if an out-of-stock item will restock', 'days_ago' => 18, 'mode' => 'Email', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => 'Will the black colorway be restocked soon?', 'hours_ago' => 430],
                            ['sender' => 'agent', 'message' => "Yes, we're expecting new stock within 2 weeks. I'll notify you when it's back.", 'hours_ago' => 428],
                        ],
                    ],
                ],
                'follow_up' => ['note' => 'awaiting for products feedback', 'due_in_days' => 5],
            ],
            [
                'name' => 'Harvey Mark Baysac',
                'email' => 'harveymarkbaysac@gmail.com',
                'phone' => '09171112233',
                'address' => 'Bancal, General Trias, Cavite',
                'dob' => '2002-11-30',
                'customer_since' => $today->copy()->subWeeks(3)->format('Y-m-d'),
                'customer_type' => 'New Customer',
                'preferred_channel' => 'Email',
                'clv' => 272.00,
                'orders' => [
                    ['days_ago' => 4, 'order_number' => 'PS0050', 'quantity' => 1, 'price' => 109.00, 'total' => 109.00, 'status' => 'Pending'],
                ],
                'logs' => [
                    [
                        'issue' => 'Abandoned cart follow-up', 'details' => 'Reminder about items left in cart', 'days_ago' => 3, 'mode' => 'Chat', 'status' => 'New',
                        'chats' => [
                            ['sender' => 'agent', 'message' => 'You left some items in your cart — need any help?', 'hours_ago' => 20],
                        ],
                    ],
                    [
                        'issue' => 'Cancel Order', 'details' => 'Wants to cancel a pending order', 'days_ago' => 4, 'mode' => 'Chat', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => "Can I still cancel order #PS0050? I ordered by mistake.", 'hours_ago' => 90],
                            ['sender' => 'agent', 'message' => "Since it hasn't shipped yet, I've cancelled it and your refund is processing.", 'hours_ago' => 89],
                        ],
                    ],
                    [
                        'issue' => 'Complaint', 'details' => 'Unhappy about a long hold time on a previous call', 'days_ago' => 10, 'mode' => 'Call', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => "I waited 20 minutes on hold yesterday, that's not okay.", 'hours_ago' => 240],
                            ['sender' => 'agent', 'message' => "You're right, I'm sorry about that wait time. I've flagged this for our team to fix.", 'hours_ago' => 239],
                        ],
                    ],
                ],
                'follow_up' => ['note' => 'follow-up on abandoned cart', 'due_in_days' => 2],
            ],
            [
                'name' => 'Charlize Lheyn Casama',
                'email' => 'charlizelheyncasama@gmail.com',
                'phone' => '09995556677',
                'address' => 'Malagasang, Imus, Cavite',
                'dob' => '2000-02-14',
                'customer_since' => $today->copy()->subMonths(8)->format('Y-m-d'),
                'customer_type' => 'Inactive',
                'preferred_channel' => 'SMS',
                'clv' => 216.40,
                'orders' => [
                    ['days_ago' => 100, 'order_number' => 'PS0060', 'quantity' => 1, 'price' => 108.00, 'total' => 108.00, 'status' => 'Delivered'],
                ],
                'logs' => [
                    [
                        'issue' => 'Discount offer sent', 'details' => 'Win-back promo sent to re-engage', 'days_ago' => 5, 'mode' => 'Chat', 'status' => 'New',
                        'chats' => [
                            ['sender' => 'agent', 'message' => "We miss you! Here's 80% off your next order.", 'hours_ago' => 100],
                        ],
                    ],
                    [
                        'issue' => 'Warranty Claim', 'details' => 'Product stopped working after 2 months', 'days_ago' => 95, 'mode' => 'Email', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => "The item stopped working after 2 months, isn't it under warranty?", 'hours_ago' => 2280],
                            ['sender' => 'agent', 'message' => "Yes, it's covered. Please ship it back and we'll send a replacement free of charge.", 'hours_ago' => 2278],
                            ['sender' => 'customer', 'message' => 'Thank you, sending it out tomorrow.', 'hours_ago' => 2277],
                        ],
                    ],
                ],
                'follow_up' => ['note' => 'sent an incoming sale with 80% discount', 'due_in_days' => 3],
            ],
            [
                'name' => 'Trisha Anne Villareal',
                'email' => 'trishaannevillareal@gmail.com',
                'phone' => '09051239876',
                'address' => 'Aguinaldo Highway, Dasmariñas, Cavite',
                'dob' => '1997-05-10',
                'customer_since' => $today->copy()->subYear()->format('Y-m-d'),
                'customer_type' => 'VIP',
                'preferred_channel' => 'Email, SMS',
                'clv' => 12650.00,
                'orders' => $this->generateVipOrders(),
                'logs' => [
                    [
                        'issue' => 'Priority support request', 'details' => 'Asking about early access to new drop', 'days_ago' => 4, 'mode' => 'Call', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => 'Do you have early access to the new drop?', 'hours_ago' => 6],
                            ['sender' => 'agent', 'message' => 'Yes! As a VIP you get early access starting tomorrow.', 'hours_ago' => 5],
                        ],
                    ],
                    [
                        'issue' => 'Compliment', 'details' => 'Positive feedback about VIP support experience', 'days_ago' => 30, 'mode' => 'Email', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => 'Just wanted to say your VIP support team is amazing, always so fast!', 'hours_ago' => 720],
                            ['sender' => 'agent', 'message' => 'That means a lot, thank you for being one of our top customers!', 'hours_ago' => 719],
                        ],
                    ],
                    [
                        'issue' => 'Loyalty Points', 'details' => 'Question about redeeming loyalty points', 'days_ago' => 60, 'mode' => 'Chat', 'status' => 'Resolved',
                        'chats' => [
                            ['sender' => 'customer', 'message' => 'How do I redeem my loyalty points on my next order?', 'hours_ago' => 1440],
                            ['sender' => 'agent', 'message' => "They're applied automatically at checkout — you'll see the discount before you pay.", 'hours_ago' => 1438],
                        ],
                    ],
                ],
                'follow_up' => ['note' => 'VIP early-access invite for next collection', 'due_in_days' => 7],
            ],
        ];

        $savedCustomers = [];
        $pendingLogs = []; // flat list across all customers, so ticket numbers can be assigned in true date order

        foreach ($customers as $data) {
            $orders = $data['orders'];
            $logs = $data['logs'];
            $followUp = $data['follow_up'];

            [$firstName, $lastName] = array_pad(explode(' ', $data['name'], 2), 2, '');

            $customer = Customer::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'phone_number' => $data['phone'],
                'status' => $data['customer_type'] === 'Inactive' ? 'Inactive' : 'Active',
                'role' => 'customer',
            ]);

            $customer->insight()->create([
                'address' => $data['address'],
                'dob' => $data['dob'],
                'customer_since' => $data['customer_since'],
                'customer_type' => $data['customer_type'],
                'preferred_channel' => $data['preferred_channel'],
                'clv' => $data['clv'],
            ]);

            $savedCustomers[$data['name']] = $customer;

            foreach ($orders as $order) {
                $subtotal = $order['price'] * $order['quantity'];

                $customer->orders()->create([
                    'order_number' => $order['order_number'],
                    'status' => self::STATUS_MAP[$order['status']] ?? 'pending',
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'shipping_fee' => 0,
                    'tax' => 0,
                    'grand_total' => $order['total'],
                    'shipping_name' => $data['name'],
                    'shipping_email' => $data['email'],
                    'shipping_phone' => $data['phone'],
                    'shipping_address' => $data['address'],
                    'customer_received' => $order['status'] === 'Delivered',
                    'payment_status' => $order['status'] === 'Delivered' ? 'paid' : 'pending',
                    'created_at' => $today->copy()->subDays($order['days_ago']),
                    'updated_at' => $today->copy()->subDays($order['days_ago']),
                ]);
            }

            foreach ($logs as $log) {
                $pendingLogs[] = [
                    'customer' => $customer,
                    'log_date' => $today->copy()->subDays($log['days_ago']),
                    'issue' => $log['issue'],
                    'details' => $log['details'],
                    'mode' => $log['mode'],
                    'status' => $log['status'],
                    'chats' => $log['chats'],
                ];
            }

            $customer->followUps()->create([
                'note' => $followUp['note'],
                'due_date' => $today->copy()->addDays($followUp['due_in_days'])->format('Y-m-d'),
            ]);
        }

        // Sort every log across every customer by its actual date, oldest first,
        // so TCK-1001 is genuinely the very first ticket ever submitted.
        usort($pendingLogs, fn ($a, $b) => $a['log_date']->timestamp <=> $b['log_date']->timestamp);

        $ticketNumber = 1001;

        foreach ($pendingLogs as $log) {
            $commLog = $log['customer']->communicationLogs()->create([
                'ticket_id' => 'TCK-' . $ticketNumber++,
                'issue' => $log['issue'],
                'details' => $log['details'],
                'log_date' => $log['log_date']->format('Y-m-d'),
                'mode' => $log['mode'],
                'status' => $log['status'],
            ]);

            foreach ($log['chats'] as $chat) {
                $hoursAgo = $chat['hours_ago'];
                unset($chat['hours_ago']);
                $chat['sent_at'] = $today->copy()->subHours($hoursAgo);
                $chat['communication_log_id'] = $commLog->id;
                $log['customer']->chatMessages()->create($chat);
            }
        }

        $activities = [
            ['customer' => 'Charlize Lheyn Casama', 'type' => 'registration', 'title' => 'New customer registered', 'note' => 'Charlize Lheyn Casama'],
            ['customer' => 'Harvey Mark Baysac', 'type' => 'order', 'title' => 'Order Placed', 'note' => 'Order #10253 by Harvey Mark'],
            ['customer' => null, 'type' => 'review', 'title' => 'Review Submitted', 'note' => '5* for Samsung Galaxy A73'],
            ['customer' => 'Trisha Anne Villareal', 'type' => 'segment_update', 'title' => 'Customer Segment Updated', 'note' => 'Trisha Anne Villareal moved to VIP'],
        ];

        foreach ($activities as $a) {
            Activity::create([
                'customer_id' => $a['customer'] ? ($savedCustomers[$a['customer']]->customer_id ?? null) : null,
                'type' => $a['type'],
                'title' => $a['title'],
                'note' => $a['note'],
            ]);
        }
    }

    protected function generateVipOrders(): array
    {
        $orders = [];
        $daysAgo = 2;

        for ($i = 1; $i <= 22; $i++) {
            $orders[] = [
                'days_ago' => $daysAgo,
                'order_number' => 'VIP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'quantity' => rand(1, 3),
                'price' => 575.00,
                'total' => 575.00,
                'status' => 'Delivered',
            ];
            $daysAgo += rand(2, 4);
        }

        return $orders;
    }
}

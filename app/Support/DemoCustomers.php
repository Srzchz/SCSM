<?php

namespace App\Support;

/**
 * Temporary in-memory data source standing in for a Customer / Order / CommunicationLog
 * Eloquent model layer. Every array here is what a real query would eventually return —
 * swap DemoCustomers::all() for Customer::all(), DemoCustomers::find($id) for
 * Customer::findOrFail($id), etc. once your migrations/models exist.
 */
class DemoCustomers
{
    public static function all(): array
    {
        return [
            1 => [
                'id' => 1,
                'name' => 'Charles Nodalo',
                'full_name' => 'Charles Nodalo Jr.',
                'email' => 'charlesnodalo@gmail.com',
                'phone' => '09123456789',
                'address' => 'Panabatan 1, Trece, Cavite',
                'dob' => 'August 23, 2000',
                'customer_since' => 'January 15, 2025',
                'customer_type' => 'VIP',
                'preferred_channel' => 'Email, SMS',
                'segment' => 'VIP',
                'total_orders' => 12,
                'total_spent' => '₱1,452.00',
                'avg_order_value' => '₱121',
                'last_ordered' => 'May 17, 2025',
                'clv' => '₱1,452.00',
                'recent_orders' => [
                    ['id' => 'PS0023', 'date' => 'May 17, 2025', 'amount' => '₱123.00', 'status' => 'Delivered'],
                    ['id' => 'PS0022', 'date' => 'May 9, 2025', 'amount' => '₱137.00', 'status' => 'Delivered'],
                    ['id' => 'PS0021', 'date' => 'Apr 30, 2025', 'amount' => '₱99.00', 'status' => 'Delivered'],
                    ['id' => 'PS0020', 'date' => 'Apr 20, 2025', 'amount' => '₱145.00', 'status' => 'Delivered'],
                    ['id' => 'PS0019', 'date' => 'Apr 8, 2025', 'amount' => '₱112.00', 'status' => 'Delivered'],
                ],
                'order_history' => [
                    ['id' => 'PS0023', 'date' => 'May 17, 2025', 'qty' => 2, 'price' => '₱123.00', 'total' => '₱246.00', 'status' => 'Delivered'],
                    ['id' => 'PS0022', 'date' => 'May 9, 2025', 'qty' => 1, 'price' => '₱137.00', 'total' => '₱137.00', 'status' => 'Delivered'],
                    ['id' => 'PS0021', 'date' => 'Apr 30, 2025', 'qty' => 3, 'price' => '₱99.00', 'total' => '₱297.00', 'status' => 'Delivered'],
                    ['id' => 'PS0020', 'date' => 'Apr 20, 2025', 'qty' => 1, 'price' => '₱145.00', 'total' => '₱145.00', 'status' => 'Delivered'],
                    ['id' => 'PS0019', 'date' => 'Apr 8, 2025', 'qty' => 2, 'price' => '₱112.00', 'total' => '₱224.00', 'status' => 'Delivered'],
                    ['id' => 'PS0018', 'date' => 'Mar 30, 2025', 'qty' => 1, 'price' => '₱103.00', 'total' => '₱103.00', 'status' => 'Delivered'],
                    ['id' => 'PS0017', 'date' => 'Mar 14, 2025', 'qty' => 1, 'price' => '₱117.00', 'total' => '₱117.00', 'status' => 'Delivered'],
                ],
                'communication_history' => [
                    ['issue' => 'Damaged', 'details' => 'My item is damaged when i arrived it.', 'date' => 'May 18, 2025', 'mode' => 'Chat', 'status' => 'Resolved'],
                    ['issue' => 'Missing Item', 'details' => 'I ordered 3 units but received only 2', 'date' => 'May 12, 2025', 'mode' => 'Chat', 'status' => 'Resolved'],
                    ['issue' => 'Search', 'details' => 'I need to buy this but I can\'t find it', 'date' => 'May 8, 2025', 'mode' => 'Chat', 'status' => 'Resolved'],
                    ['issue' => 'Return', 'details' => 'I need to return damaged items', 'date' => 'Apr 25, 2025', 'mode' => 'Chat', 'status' => 'Resolved'],
                    ['issue' => 'Refund', 'details' => 'I need a refund', 'date' => 'May 16, 2025', 'mode' => 'Call', 'status' => 'Resolved'],
                    ['issue' => 'Cancel', 'details' => 'How do i cancel my order?', 'date' => 'May 11, 2025', 'mode' => 'Chat', 'status' => 'Resolved'],
                ],
                'chats' => [
                    ['from' => 'customer', 'text' => "My item is damaged when it arrived", 'time' => '2:14 PM'],
                    ['from' => 'agent', 'text' => "Sorry to hear that! Can you send a photo so we can process a replacement?", 'time' => '2:16 PM'],
                    ['from' => 'customer', 'text' => "Sure, sending it now.", 'time' => '2:17 PM'],
                ],
            ],
            2 => [
                'id' => 2,
                'name' => 'John Yevs Bryan Suico',
                'full_name' => 'John Yevs Bryan Suico',
                'email' => 'johnyevsbryansuico@gmail.com',
                'phone' => '09187654321',
                'address' => 'Salawag, Dasmariñas, Cavite',
                'dob' => 'March 2, 1999',
                'customer_since' => 'February 2, 2025',
                'customer_type' => 'Repeat Buyer',
                'preferred_channel' => 'Email',
                'segment' => 'Repeat Buyer',
                'total_orders' => 8,
                'total_spent' => '₱965.30',
                'avg_order_value' => '₱121',
                'last_ordered' => 'May 16, 2025',
                'clv' => '₱482.65',
                'recent_orders' => [
                    ['id' => 'PS0031', 'date' => 'May 16, 2025', 'amount' => '₱120.00', 'status' => 'Delivered'],
                    ['id' => 'PS0030', 'date' => 'May 2, 2025', 'amount' => '₱118.00', 'status' => 'Delivered'],
                ],
                'order_history' => [
                    ['id' => 'PS0031', 'date' => 'May 16, 2025', 'qty' => 1, 'price' => '₱120.00', 'total' => '₱120.00', 'status' => 'Delivered'],
                    ['id' => 'PS0030', 'date' => 'May 2, 2025', 'qty' => 1, 'price' => '₱118.00', 'total' => '₱118.00', 'status' => 'Delivered'],
                ],
                'communication_history' => [
                    ['issue' => 'Delayed', 'details' => 'Order delivery was delayed', 'date' => 'May 3, 2025', 'mode' => 'Chat', 'status' => 'Resolved'],
                ],
                'chats' => [
                    ['from' => 'customer', 'text' => "When will my order arrive?", 'time' => '10:02 AM'],
                    ['from' => 'agent', 'text' => "It's out for delivery, should reach you today.", 'time' => '10:05 AM'],
                ],
            ],
            3 => [
                'id' => 3,
                'name' => 'Arren Jade Lord Toong',
                'full_name' => 'Arren Jade Lord Toong',
                'email' => 'arrenjadelordtoong@gmail.com',
                'phone' => '09201234567',
                'address' => 'Anabu, Imus, Cavite',
                'dob' => 'July 19, 2001',
                'customer_since' => 'March 5, 2025',
                'customer_type' => 'Repeat Buyer',
                'preferred_channel' => 'SMS',
                'segment' => 'Repeat Buyer',
                'total_orders' => 6,
                'total_spent' => '₱672.40',
                'avg_order_value' => '₱112',
                'last_ordered' => 'May 15, 2025',
                'clv' => '₱336.20',
                'recent_orders' => [
                    ['id' => 'PS0040', 'date' => 'May 15, 2025', 'amount' => '₱112.00', 'status' => 'Delivered'],
                ],
                'order_history' => [
                    ['id' => 'PS0040', 'date' => 'May 15, 2025', 'qty' => 1, 'price' => '₱112.00', 'total' => '₱112.00', 'status' => 'Delivered'],
                ],
                'communication_history' => [
                    ['issue' => 'Awaiting feedback', 'details' => 'Waiting for customer feedback on order', 'date' => 'May 16, 2025', 'mode' => 'Chat', 'status' => 'Open'],
                ],
                'chats' => [
                    ['from' => 'agent', 'text' => "How was your recent order? We'd love your feedback!", 'time' => '9:00 AM'],
                ],
            ],
            4 => [
                'id' => 4,
                'name' => 'Harvey Mark Baysac',
                'full_name' => 'Harvey Mark Baysac',
                'email' => 'harveymarkbaysac@gmail.com',
                'phone' => '09171112233',
                'address' => 'Bancal, General Trias, Cavite',
                'dob' => 'November 30, 2002',
                'customer_since' => 'April 1, 2025',
                'customer_type' => 'New Customer',
                'preferred_channel' => 'Email',
                'segment' => 'New Customer',
                'total_orders' => 5,
                'total_spent' => '₱544.00',
                'avg_order_value' => '₱109',
                'last_ordered' => 'May 14, 2025',
                'clv' => '₱272.00',
                'recent_orders' => [
                    ['id' => 'PS0050', 'date' => 'May 14, 2025', 'amount' => '₱109.00', 'status' => 'Pending'],
                ],
                'order_history' => [
                    ['id' => 'PS0050', 'date' => 'May 14, 2025', 'qty' => 1, 'price' => '₱109.00', 'total' => '₱109.00', 'status' => 'Pending'],
                ],
                'communication_history' => [
                    ['issue' => 'Abandoned cart follow-up', 'details' => 'Customer left items in cart', 'date' => 'May 15, 2025', 'mode' => 'Chat', 'status' => 'Open'],
                ],
                'chats' => [
                    ['from' => 'agent', 'text' => "You left some items in your cart — need any help?", 'time' => '3:20 PM'],
                ],
            ],
            5 => [
                'id' => 5,
                'name' => 'Charlize Lheyn Casama',
                'full_name' => 'Charlize Lheyn Casama',
                'email' => 'charlizelheyncasama@gmail.com',
                'phone' => '09995556677',
                'address' => 'Malagasang, Imus, Cavite',
                'dob' => 'February 14, 2000',
                'customer_since' => 'January 28, 2025',
                'customer_type' => 'At Risk',
                'preferred_channel' => 'SMS',
                'segment' => 'At Risk',
                'total_orders' => 4,
                'total_spent' => '₱432.80',
                'avg_order_value' => '₱108',
                'last_ordered' => 'May 14, 2025',
                'clv' => '₱216.40',
                'recent_orders' => [
                    ['id' => 'PS0060', 'date' => 'May 14, 2025', 'amount' => '₱108.00', 'status' => 'Delivered'],
                ],
                'order_history' => [
                    ['id' => 'PS0060', 'date' => 'May 14, 2025', 'qty' => 1, 'price' => '₱108.00', 'total' => '₱108.00', 'status' => 'Delivered'],
                ],
                'communication_history' => [
                    ['issue' => 'Discount offer sent', 'details' => 'Special discount offer sent to encourage repurchase', 'date' => 'Sep 10, 2025', 'mode' => 'Chat', 'status' => 'Open'],
                ],
                'chats' => [
                    ['from' => 'agent', 'text' => "We miss you! Here's 80% off your next order.", 'time' => '11:45 AM'],
                ],
            ],
        ];
    }

    public static function find(int $id): ?array
    {
        return self::all()[$id] ?? null;
    }

    public static function tableRows(): array
    {
        return array_map(fn (array $c) => [
            'id' => $c['id'],
            'name' => $c['name'],
            'email' => $c['email'],
            'orders' => $c['total_orders'],
            'spent' => $c['total_spent'],
            'clv' => $c['clv'],
            'last' => $c['last_ordered'],
            'segment' => $c['segment'],
        ], self::all());
    }

    public static function insights(): array
    {
        return [
            ['label' => 'High spending customer', 'value' => '864'],
            ['label' => 'Customer at risk', 'value' => '533'],
            ['label' => 'Inactive customer', 'value' => '985'],
            ['label' => 'New customer this month', 'value' => '1,172'],
        ];
    }

    public static function followUps(): array
    {
        return [
            ['name' => 'Arren Jade Lord Toong', 'note' => 'awaiting for products feedback', 'date' => 'Aug 10, 2025', 'id' => 3],
            ['name' => 'Harvey Mark Baysac', 'note' => 'follow-up on abandoned cart', 'date' => 'Aug 12, 2025', 'id' => 4],
            ['name' => 'Charlize Lheyn Casama', 'note' => 'sent an incoming sale with 80% discount', 'date' => 'Sep 10, 2025', 'id' => 5],
            ['name' => 'Charles Nodalo', 'note' => 'an incoming sale with 80% discount', 'date' => 'Sep 30, 2025', 'id' => 1],
            ['name' => 'John Yevs Bryan Suico', 'note' => 'an incoming sale with 80% discount', 'date' => 'Sep 30, 2025', 'id' => 2],
        ];
    }

    public static function activities(): array
    {
        return [
            ['icon' => '⊕', 'title' => 'New customer registered', 'note' => 'Charlize Lheyn Casama', 'time' => '2 min ago'],
            ['icon' => '🛒', 'title' => 'Order Placed', 'note' => 'Order #10253 by Harvey Mark', 'time' => '15 min ago'],
            ['icon' => '★', 'title' => 'Review Submitted', 'note' => '5* for Samsung Galaxy A73', 'time' => '1 hr ago'],
            ['icon' => '⇄', 'title' => 'Customer Segment Updated', 'note' => 'Charles Nodalo moved to VIP', 'time' => '2 hr ago'],
        ];
    }

    public static function segmentBadgeClasses(): array
    {
        return [
            'VIP' => 'bg-curema-vip text-curema-ink',
            'Repeat Buyer' => 'bg-curema-greensoft text-curema-green',
            'New Customer' => 'bg-curema-bluesoft text-curema-blue',
            'Inactive' => 'bg-curema-coral text-curema-ink',
        ];
    }
}
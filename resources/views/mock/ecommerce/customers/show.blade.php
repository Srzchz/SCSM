<!DOCTYPE html>
<html>
<head>
    <title>Mock Ecommerce - {{ $customer->first_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, Segoe UI, sans-serif; max-width: 640px; margin: 48px auto; padding: 0 20px; color: #1f2328; background: #f6f8fa; }
        a.back { color: #57606a; text-decoration: none; font-size: 13px; }
        h1 { font-size: 22px; margin: 12px 0 24px; }
        .choices { display: grid; gap: 14px; }
        .choice { display: block; background: #fff; border: 1px solid #d0d7de; border-radius: 8px; padding: 20px; text-decoration: none; color: inherit; }
        .choice:hover { border-color: #8c959f; }
        .choice .title { font-weight: 600; font-size: 16px; margin-bottom: 4px; }
        .choice .desc { color: #57606a; font-size: 13px; }
    </style>
</head>
<body>
    <a class="back" href="{{ route('mock.ecommerce.customers') }}">&larr; Back to customers</a>
    <h1>{{ $customer->first_name }} {{ $customer->last_name }}</h1>

    <div class="choices">
        <a class="choice" href="{{ route('mock.ecommerce.customers.orders', $customer) }}">
            <div class="title">Place an Order</div>
            <div class="desc">Simulate a purchase through Sales Order Management's real API.</div>
        </a>
        <a class="choice" href="{{ route('mock.ecommerce.customers.help-desk', $customer) }}">
            <div class="title">Customer Support / Help Desk</div>
            <div class="desc">Report an issue against an order, or rate a resolved case.</div>
        </a>
    </div>
</body>
</html>

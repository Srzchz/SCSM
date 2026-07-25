<!DOCTYPE html>
<html>
<head>
    <title>Mock Ecommerce - Simulate as customer</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, Segoe UI, sans-serif; max-width: 640px; margin: 48px auto; padding: 0 20px; color: #1f2328; background: #f6f8fa; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        p.sub { color: #57606a; margin-top: 0; margin-bottom: 24px; }
        .list { background: #fff; border: 1px solid #d0d7de; border-radius: 8px; overflow: hidden; }
        .row { display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; border-bottom: 1px solid #eaeef2; text-decoration: none; color: inherit; }
        .row:last-child { border-bottom: none; }
        .row:hover { background: #f6f8fa; }
        .name { font-weight: 600; }
        .email { color: #57606a; font-size: 13px; }
        .arrow { color: #8c959f; }
    </style>
</head>
<body>
    <h1>Who are you simulating?</h1>
    <p class="sub">Pick a customer to act as, then see their orders and place new ones.</p>
    <div class="list">
        @foreach ($customers as $customer)
            <a class="row" href="{{ route('mock.ecommerce.customers.orders', $customer) }}">
                <span>
                    <div class="name">{{ $customer->first_name }} {{ $customer->last_name }}</div>
                    <div class="email">{{ $customer->email }}</div>
                </span>
                <span class="arrow">&rarr;</span>
            </a>
        @endforeach
    </div>
</body>
</html>

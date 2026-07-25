<!DOCTYPE html>
<html>
<head>
    <title>Mock Ecommerce - Simulate as customer</title>
</head>
<body style="font-family: sans-serif; max-width: 700px; margin: 40px auto;">
    <h1>Who are you simulating?</h1>
    <p>Pick a customer to act as, then you'll see their past orders.</p>
    <ul>
        @foreach ($customers as $customer)
            <li>
                <a href="{{ route('mock.ecommerce.customers.orders', $customer) }}">
                    {{ $customer->first_name }} {{ $customer->last_name }} ({{ $customer->email }})
                </a>
            </li>
        @endforeach
    </ul>
</body>
</html>

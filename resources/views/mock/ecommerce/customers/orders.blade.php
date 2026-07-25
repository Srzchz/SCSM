<!DOCTYPE html>
<html>
<head>
    <title>Mock Ecommerce - {{ $customer->first_name }}'s orders</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body style="font-family: sans-serif; max-width: 700px; margin: 40px auto;">
    <p><a href="{{ route('mock.ecommerce.customers') }}">&larr; Back to customers</a></p>
    <h1>{{ $customer->first_name }} {{ $customer->last_name }}'s orders</h1>

    @if ($orders->isEmpty())
        <p>No orders yet for this customer.</p>
    @else
        <ul>
            @foreach ($orders as $order)
                <li>
                    <a href="{{ route('mock.ecommerce.orders.show', $order) }}">
                        {{ $order->order_number }} — {{ $order->status }} — ₱{{ number_format($order->grand_total, 2) }}
                    </a>
                    (report an issue against this order)
                </li>
            @endforeach
        </ul>
    @endif

    <hr>

    <h2>Place a new order</h2>
    <p>This calls the real Sales Order Management API (quotation → accept) — it'll show up in their dashboard too.</p>

    <form id="place-order-form">
        <label>Product</label><br>
        <select name="product_id" id="product_id">
            @foreach ($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }} — ₱{{ number_format($product->price, 2) }}</option>
            @endforeach
        </select>
        <br><br>
        <label>Quantity</label><br>
        <input type="number" name="quantity" id="quantity" value="1" min="1">
        <br><br>
        <button type="submit">Place order via SOM</button>
    </form>

    <script>
        document.getElementById('place-order-form').addEventListener('submit', async function (e) {
            e.preventDefault();

            const token = document.querySelector('meta[name="csrf-token"]').content;
            const productId = document.getElementById('product_id').value;
            const quantity = document.getElementById('quantity').value;

            const res = await fetch('{{ route('mock.ecommerce.customers.place-order', $customer) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ product_id: productId, quantity: quantity }),
            });

            const data = await res.json();

            if (res.ok) {
                alert('Order placed!\n\nStatus: ' + res.status + '\n\n' + JSON.stringify(data.order, null, 2));
                location.reload();
            } else {
                alert('Order placement failed (' + res.status + ')\n\n' + JSON.stringify(data, null, 2));
            }
        });
    </script>
</body>
</html>

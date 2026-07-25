<!DOCTYPE html>
<html>
<head>
    <title>Mock Ecommerce - {{ $customer->first_name }}'s orders</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, Segoe UI, sans-serif; max-width: 640px; margin: 48px auto; padding: 0 20px; color: #1f2328; background: #f6f8fa; }
        a.back { color: #57606a; text-decoration: none; font-size: 13px; }
        h1 { font-size: 22px; margin: 12px 0 20px; }
        h2 { font-size: 16px; margin: 32px 0 4px; }
        p.sub { color: #57606a; margin-top: 0; margin-bottom: 16px; font-size: 14px; }
        .card { background: #fff; border: 1px solid #d0d7de; border-radius: 8px; padding: 4px 0; }
        .row { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #eaeef2; font-size: 14px; }
        .row:last-child { border-bottom: none; }
        .status { font-size: 12px; padding: 2px 8px; border-radius: 999px; background: #ddf4ff; color: #0969da; }
        .empty { padding: 16px; color: #57606a; font-size: 14px; }
        form.card { padding: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 12px 0 4px; }
        select, input[type=number] { width: 100%; padding: 8px; border: 1px solid #d0d7de; border-radius: 6px; font-size: 14px; }
        button { margin-top: 18px; background: #1f883d; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-size: 14px; cursor: pointer; }
        button:hover { background: #1a7f37; }
        button:disabled { opacity: 0.6; cursor: default; }
    </style>
</head>
<body>
    <a class="back" href="{{ route('mock.ecommerce.customers.show', $customer) }}">&larr; Back</a>
    <h1>{{ $customer->first_name }} {{ $customer->last_name }}'s orders</h1>

    <div class="card">
        @forelse ($orders as $order)
            <div class="row">
                <span>{{ $order->order_number }} — ₱{{ number_format($order->grand_total, 2) }}</span>
                <span class="status">{{ $order->status }}</span>
            </div>
        @empty
            <div class="empty">No orders yet for this customer.</div>
        @endforelse
    </div>

    <h2>Place a new order</h2>
    <p class="sub">Goes through Sales Order Management's real API — will show up in their dashboard.</p>

    <form id="place-order-form" class="card">
        <label>Product</label>
        <select name="product_id" id="product_id">
            @foreach ($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }} — ₱{{ number_format($product->price, 2) }}</option>
            @endforeach
        </select>
        <label>Quantity</label>
        <input type="number" name="quantity" id="quantity" value="1" min="1">
        <button type="submit">Place order via SOM</button>
    </form>

    <script>
        document.getElementById('place-order-form').addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            btn.disabled = true;
            btn.textContent = 'Placing order...';

            const token = document.querySelector('meta[name="csrf-token"]').content;
            const productId = document.getElementById('product_id').value;
            const quantity = document.getElementById('quantity').value;

            try {
                const res = await fetch('{{ route('mock.ecommerce.customers.place-order', $customer) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify({ product_id: productId, quantity: quantity }),
                });

                if (res.ok) {
                    alert('Order placed successfully.');
                    location.reload();
                } else {
                    alert('Order placement failed.');
                }
            } catch (err) {
                alert('Order placement failed.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Place order via SOM';
            }
        });
    </script>
</body>
</html>

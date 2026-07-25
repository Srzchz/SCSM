<!DOCTYPE html>
<html>
<head>
    <title>Mock Ecommerce - Order {{ $order->order_number }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body style="font-family: sans-serif; max-width: 700px; margin: 40px auto;">
    <p><a href="{{ route('mock.ecommerce.customers.orders', $order->customer_id) }}">&larr; Back to customer's orders</a></p>
    <h1>Order {{ $order->order_number }}</h1>
    <p><strong>Customer:</strong> {{ $order->customer->first_name }} {{ $order->customer->last_name }} ({{ $order->customer->email }})</p>
    <p><strong>Status:</strong> {{ $order->status }}</p>
    <p><strong>Total:</strong> ₱{{ number_format($order->grand_total, 2) }}</p>

    <hr>

    <h2>Contact support</h2>
    <form id="support-form">
        <label>Category</label><br>
        <select name="category" id="category">
            <option>Technical</option>
            <option>Returns</option>
            <option>Warranty</option>
            <option>Support</option>
        </select>
        <br><br>
        <label>Priority</label><br>
        <select name="priority" id="priority">
            <option value="">Default (medium)</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="critical">Critical</option>
        </select>
        <br><br>
        <textarea name="issue_description" id="issue_description" rows="4" style="width:100%;" placeholder="Describe the issue..."></textarea>
        <br><br>
        <button type="submit">Send to SCSM</button>
    </form>

    <script>
        document.getElementById('support-form').addEventListener('submit', async function (e) {
            e.preventDefault();

            const token = document.querySelector('meta[name="csrf-token"]').content;

            const res = await fetch('{{ route('mock.ecommerce.orders.request-support', $order) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    category: document.getElementById('category').value,
                    priority: document.getElementById('priority').value || null,
                    issue_description: document.getElementById('issue_description').value,
                }),
            });

            const data = await res.json();

            if (res.ok) {
                alert('Case created!\n\nStatus: ' + res.status + '\n\n' + JSON.stringify(data.response, null, 2));
                document.getElementById('support-form').reset();
            } else {
                alert('Failed (' + res.status + ')\n\n' + JSON.stringify(data, null, 2));
            }
        });
    </script>
</body>
</html>

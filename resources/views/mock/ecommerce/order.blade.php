<!DOCTYPE html>
<html>
<head>
    <title>Mock Ecommerce - Order {{ $order->order_number }}</title>
</head>
<body style="font-family: sans-serif; max-width: 700px; margin: 40px auto;">
    <h1>Order {{ $order->order_number }}</h1>
    <p><strong>Customer:</strong> {{ $order->customer->first_name }} {{ $order->customer->last_name }} ({{ $order->customer->email }})</p>
    <p><strong>Status:</strong> {{ $order->status }}</p>
    <p><strong>Total:</strong> ₱{{ number_format($order->grand_total, 2) }}</p>

    <hr>

    <h2>Contact support</h2>
    <form method="POST" action="{{ route('mock.ecommerce.orders.request-support', $order) }}">
        @csrf
        <label>Category</label><br>
        <select name="category">
            <option>Technical</option>
            <option>Returns</option>
            <option>Warranty</option>
            <option>Support</option>
        </select>
        <br><br>
        <label>Priority</label><br>
        <select name="priority">
            <option value="">Default (medium)</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="critical">Critical</option>
        </select>
        <br><br>
        <textarea name="issue_description" rows="4" style="width:100%;" placeholder="Describe the issue..."></textarea>
        <br><br>
        <button type="submit">Send to SCSM</button>
    </form>

    @if (session('mock_payload'))
        <h3>Payload sent</h3>
        <pre>{{ json_encode(session('mock_payload'), JSON_PRETTY_PRINT) }}</pre>

        <h3>Response from SCSM ({{ session('mock_status') }})</h3>
        <pre>{{ json_encode(session('mock_response'), JSON_PRETTY_PRINT) }}</pre>
    @endif
</body>
</html>

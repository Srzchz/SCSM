<!DOCTYPE html>
<html>
<head>
    <title>Mock Ecommerce - Order {{ $order->order_number }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, Segoe UI, sans-serif; max-width: 640px; margin: 48px auto; padding: 0 20px; color: #1f2328; background: #f6f8fa; }
        a.back { color: #57606a; text-decoration: none; font-size: 13px; }
        h1 { font-size: 22px; margin: 12px 0 20px; }
        h2 { font-size: 16px; margin: 32px 0 4px; }
        .card { background: #fff; border: 1px solid #d0d7de; border-radius: 8px; padding: 18px; }
        .meta { color: #57606a; font-size: 14px; margin: 4px 0; }
        .meta strong { color: #1f2328; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 12px 0 4px; }
        select, textarea { width: 100%; padding: 8px; border: 1px solid #d0d7de; border-radius: 6px; font-size: 14px; font-family: inherit; }
        button { margin-top: 18px; background: #cf222e; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-size: 14px; cursor: pointer; }
        button:hover { background: #a40e26; }
        button:disabled { opacity: 0.6; cursor: default; }
    </style>
</head>
<body>
    <a class="back" href="{{ route('mock.ecommerce.customers.orders', $order->customer_id) }}">&larr; Back to customer's orders</a>
    <h1>Order {{ $order->order_number }}</h1>

    <div class="card">
        <p class="meta"><strong>Customer:</strong> {{ $order->customer->first_name }} {{ $order->customer->last_name }} ({{ $order->customer->email }})</p>
        <p class="meta"><strong>Status:</strong> {{ $order->status }}</p>
        <p class="meta"><strong>Total:</strong> ₱{{ number_format($order->grand_total, 2) }}</p>
    </div>

    <h2>Contact support</h2>
    <form id="support-form" class="card">
        <label>Category</label>
        <select name="category" id="category">
            <option>Technical</option>
            <option>Returns</option>
            <option>Warranty</option>
            <option>Support</option>
        </select>
        <label>Priority</label>
        <select name="priority" id="priority">
            <option value="">Default (medium)</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="critical">Critical</option>
        </select>
        <label>Issue</label>
        <textarea name="issue_description" id="issue_description" rows="4" placeholder="Describe the issue..."></textarea>
        <button type="submit">Send to SCSM</button>
    </form>

    <script>
        document.getElementById('support-form').addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            btn.disabled = true;
            btn.textContent = 'Sending...';

            const token = document.querySelector('meta[name="csrf-token"]').content;

            try {
                const res = await fetch('{{ route('mock.ecommerce.orders.request-support', $order) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        category: document.getElementById('category').value,
                        priority: document.getElementById('priority').value || null,
                        issue_description: document.getElementById('issue_description').value,
                    }),
                });

                if (res.ok) {
                    alert('Case submitted successfully.');
                    document.getElementById('support-form').reset();
                } else {
                    alert('Failed to submit case.');
                }
            } catch (err) {
                alert('Failed to submit case.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Send to SCSM';
            }
        });
    </script>
</body>
</html>

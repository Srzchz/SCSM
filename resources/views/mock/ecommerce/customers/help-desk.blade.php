<!DOCTYPE html>
<html>
<head>
    <title>Mock Ecommerce - {{ $customer->first_name }}'s Help Desk</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, Segoe UI, sans-serif; max-width: 640px; margin: 48px auto; padding: 0 20px; color: #1f2328; background: #f6f8fa; }
        a.back { color: #57606a; text-decoration: none; font-size: 13px; }
        h1 { font-size: 22px; margin: 12px 0 4px; }
        p.sub { color: #57606a; margin-top: 0; margin-bottom: 20px; font-size: 14px; }
        h2 { font-size: 16px; margin: 32px 0 4px; }
        .card { background: #fff; border: 1px solid #d0d7de; border-radius: 8px; padding: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 12px 0 4px; }
        select, textarea { width: 100%; padding: 8px; border: 1px solid #d0d7de; border-radius: 6px; font-size: 14px; font-family: inherit; }
        button { margin-top: 18px; background: #cf222e; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-size: 14px; cursor: pointer; }
        button:hover { background: #a40e26; }
        button.rate { background: #9a6700; }
        button.rate:hover { background: #7d5400; }
        button:disabled { opacity: 0.6; cursor: default; }
        .case-list { background: #fff; border: 1px solid #d0d7de; border-radius: 8px; overflow: hidden; margin-bottom: 8px; }
        .case-row { padding: 10px 16px; border-bottom: 1px solid #eaeef2; font-size: 14px; display: flex; justify-content: space-between; }
        .case-row:last-child { border-bottom: none; }
        .rated { color: #57606a; font-size: 12px; }
        .empty { padding: 16px; color: #57606a; font-size: 14px; background: #fff; border: 1px solid #d0d7de; border-radius: 8px; }
    </style>
</head>
<body>
    <a class="back" href="{{ route('mock.ecommerce.customers.show', $customer) }}">&larr; Back</a>
    <h1>Help Desk</h1>
    <p class="sub">{{ $customer->first_name }} {{ $customer->last_name }}</p>

    <h2>Report an issue</h2>
    <form id="case-form" class="card">
        <label>Order</label>
        <select name="order_id" id="order_id">
            @forelse ($orders as $order)
                <option value="{{ $order->order_id }}">{{ $order->order_number }} — ₱{{ number_format($order->grand_total, 2) }}</option>
            @empty
                <option value="">No orders available</option>
            @endforelse
        </select>
        <label>Category</label>
        <select name="category" id="category" onchange="document.getElementById('amount-field').style.display = this.value === 'Warranty' ? 'block' : 'none'">
            <option>Technical</option>
            <option>Returns</option>
            <option>Warranty</option>
            <option>Support</option>
        </select>
        <div id="amount-field" style="display:none">
            <label>Estimated repair/replacement cost (₱)</label>
            <input type="number" name="estimated_amount" id="estimated_amount" min="0" step="0.01" placeholder="0.00">
        </div>
        <label>Priority</label>
        <select name="priority" id="priority">
            <option value="">Default (medium)</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="critical">Critical</option>
        </select>
        <label>Issue</label>
        <textarea name="issue_description" id="issue_description" rows="3" placeholder="Describe the issue..."></textarea>
        <button type="submit">Send to SCSM</button>
    </form>

    <h2>Rate a resolved case</h2>
    <div class="case-list">
        @forelse ($cases as $case)
            <div class="case-row">
                <span>{{ $case['case_number'] }} — {{ $case['category'] }} ({{ $case['status'] }})</span>
                @if ($case['satisfaction_rating'])
                    <span class="rated">Rated {{ $case['satisfaction_rating'] }}★</span>
                @endif
            </div>
        @empty
            <div class="case-row">No cases yet.</div>
        @endforelse
    </div>

    <form id="satisfaction-form" class="card">
        <label>Case</label>
        <select name="case_id" id="case_id">
            @forelse ($cases as $case)
                @if (in_array($case['status'], ['resolved', 'closed']))
                    <option value="{{ $case['id'] }}">{{ $case['case_number'] }} ({{ $case['status'] }})</option>
                @endif
            @empty
            @endforelse
        </select>
        <label>Rating (1–5)</label>
        <select name="satisfaction_rating" id="satisfaction_rating">
            <option value="5">5 — Very satisfied</option>
            <option value="4">4 — Satisfied</option>
            <option value="3">3 — Neutral</option>
            <option value="2">2 — Unsatisfied</option>
            <option value="1">1 — Very unsatisfied</option>
        </select>
        <label>Feedback (optional)</label>
        <textarea name="satisfaction_feedback" id="satisfaction_feedback" rows="2" placeholder="Anything else to add..."></textarea>
        <button type="submit" class="rate">Submit rating</button>
    </form>

    <script>
        const token = document.querySelector('meta[name="csrf-token"]').content;

        document.getElementById('case-form').addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            btn.disabled = true;
            btn.textContent = 'Sending...';

            try {
                const res = await fetch('{{ route('mock.ecommerce.customers.help-desk.cases', $customer) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        order_id: document.getElementById('order_id').value,
                        category: document.getElementById('category').value,
                        priority: document.getElementById('priority').value || null,
                        issue_description: document.getElementById('issue_description').value,
                        estimated_amount: document.getElementById('estimated_amount').value || null,
                    }),
                });

                // res.ok alone isn't reliable: if the server redirects
                // (CSRF/session issue on a `web` route), fetch() silently
                // follows it and lands on a real 200 page unrelated to case
                // creation. Only a real JSON body with ok:true counts.
                const contentType = res.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('Non-JSON response (likely a redirect was followed) — status ' + res.status);
                }
                const data = await res.json();
                const success = res.ok && data.ok === true;

                if (success) {
                    alert('Case submitted successfully.');
                    document.getElementById('case-form').reset();
                    location.reload();
                } else {
                    const detail = data.errors
                        ? Object.values(data.errors).flat().join('\n')
                        : (data.message || 'Unknown error.');
                    alert('Failed to submit case:\n\n' + detail);
                }
            } catch (err) {
                console.error(err);
                alert('Failed to submit case.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Send to SCSM';
            }
        });

        document.getElementById('satisfaction-form').addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const caseSelect = document.getElementById('case_id');

            if (!caseSelect.value) {
                alert('No resolved or closed case available to rate yet.');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Submitting...';

            try {
                const res = await fetch('{{ route('mock.ecommerce.customers.help-desk.satisfaction', $customer) }}', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        case_id: caseSelect.value,
                        satisfaction_rating: document.getElementById('satisfaction_rating').value,
                        satisfaction_feedback: document.getElementById('satisfaction_feedback').value || null,
                    }),
                });

                const contentType = res.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('Non-JSON response (likely a redirect was followed) — status ' + res.status);
                }
                const data = await res.json();
                const success = res.ok && data.ok === true;

                if (success) {
                    alert('Rating submitted successfully.');
                    location.reload();
                } else {
                    const detail = data.errors
                        ? Object.values(data.errors).flat().join('\n')
                        : (data.message || 'Unknown error.');
                    alert('Failed to submit rating:\n\n' + detail);
                }
            } catch (err) {
                console.error(err);
                alert('Failed to submit rating.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Submit rating';
            }
        });
    </script>
</body>
</html>

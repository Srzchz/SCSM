<!DOCTYPE html>
<html>
<head>
    <title>Mock Ecommerce - Catalog</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, Segoe UI, sans-serif; max-width: 640px; margin: 48px auto; padding: 0 20px; color: #1f2328; background: #f6f8fa; }
        h1 { font-size: 22px; margin-bottom: 20px; }
        .card { background: #fff; border: 1px solid #d0d7de; border-radius: 8px; overflow: hidden; }
        .row { display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid #eaeef2; font-size: 14px; }
        .row:last-child { border-bottom: none; }
        .row.head { font-weight: 600; background: #f6f8fa; }
        .sku { color: #57606a; }
    </style>
</head>
<body>
    <h1>Mock catalog</h1>
    <div class="card">
        <div class="row head"><span>Product</span><span>Price</span></div>
        @foreach ($products as $product)
        <div class="row">
            <span>{{ $product->name }} <span class="sku">({{ $product->sku }})</span></span>
            <span>₱{{ number_format($product->price, 2) }}</span>
        </div>
        @endforeach
    </div>
</body>
</html>

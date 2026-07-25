<!DOCTYPE html>
<html>
<head>
    <title>Mock Ecommerce - Catalog</title>
</head>
<body style="font-family: sans-serif; max-width: 700px; margin: 40px auto;">
    <h1>Mock catalog</h1>
    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse: collapse;">
        <tr>
            <th>Product</th><th>SKU</th><th>Price</th>
        </tr>
        @foreach ($products as $product)
        <tr>
            <td>{{ $product->name }}</td>
            <td>{{ $product->sku }}</td>
            <td>₱{{ number_format($product->price, 2) }}</td>
        </tr>
        @endforeach
    </table>
</body>
</html>

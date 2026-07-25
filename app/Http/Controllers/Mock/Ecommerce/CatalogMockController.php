<?php

namespace App\Http\Controllers\Mock\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Product;

class CatalogMockController extends Controller
{
    public function index()
    {
        // Not using withCount('orderItems') here since the shared Product
        // stub model doesn't define that relation yet — avoids requiring
        // an edit to a file another team owns just for this demo view.
        $products = Product::all();

        return view('mock.ecommerce.catalog', compact('products'));
    }
}

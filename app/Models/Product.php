<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * MOCK MODEL — owned by the Inventory module.
 */
class Product extends Model
{
    use HasFactory;

    // NOTE: the products table uses the default `id` auto-increment column
    // (see the create_products_table migration's `$table->id()`), not
    // `product_id`. Other modules' migrations that reference this table by
    // `product_id` have been corrected to point at `id` instead.
    protected $fillable = ['sku', 'name', 'category', 'price', 'unit_price', 'is_active'];

    protected $casts = [
        'price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}

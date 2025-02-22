<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVarient;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['cart_id', 'product_id', 'product_variant_id', 'quantity', 'is_free', 'dealid'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVarient::class, 'product_variant_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVarient::class, 'product_variant_id');
    }

}

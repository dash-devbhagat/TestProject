<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function freeProduct()
    {
        return $this->belongsTo(Product::class, 'free_product_id');
    }

    public function productVarient()
    {
        return $this->belongsTo(ProductVarient::class, 'product_variant_id');
    }

    public function freeProductVarient()
    {
        return $this->belongsTo(Product::class, 'free_product_variant_id');
    }

    public function dealComboProducts()
    {
        return $this->hasMany(DealComboProduct::class, 'deal_id', 'id');
    }

    // Relationship for buy product
    public function buyProduct()
    {
        return $this->belongsTo(Product::class, 'buy_product_id');
    }

    // Relationship for buy variant
    public function buyVariant()
    {
        return $this->belongsTo(ProductVarient::class, 'buy_variant_id');
    }

    // Relationship for get product
    public function getProduct()
    {
        return $this->belongsTo(Product::class, 'get_product_id');
    }

    // Relationship for get variant
    public function getVariant()
    {
        return $this->belongsTo(ProductVarient::class, 'get_variant_id');
    }

}

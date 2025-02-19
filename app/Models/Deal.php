<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function buyProduct()
    {
        return $this->belongsTo(Product::class, 'buy_product_id');
    }

    public function getProduct()
    {
        return $this->belongsTo(Product::class, 'get_product_id');
    }

    public function buyProductVariant()
    {
        return $this->belongsTo(ProductVarient::class, 'buy_variant_id', 'id');
    }

    public function getProductVariant()
    {
        return $this->belongsTo(ProductVarient::class, 'get_variant_id', 'id');
    }

    public function dealComboProducts()
    {
        return $this->hasMany(DealComboProduct::class, 'deal_id', 'id');
    }
}


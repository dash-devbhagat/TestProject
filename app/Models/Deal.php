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
        return $this->belongsTo(Product::class,'product_id');
    }

    public function freeProduct()
    {
        return $this->belongsTo(Product::class, 'free_product_id');
    }

    public function productVarient()
    {
        return $this->belongsTo(ProductVarient::class,'product_variant_id');
    }

    public function freeProductVarient()
    {
        return $this->belongsTo(Product::class, 'free_product_variant_id');
    }
}

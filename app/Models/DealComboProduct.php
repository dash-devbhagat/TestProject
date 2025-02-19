<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealComboProduct extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'deal_combo_products';

    // Fillable attributes to allow mass assignment
    protected $fillable = [
        'deal_id',
        'product_id',
        'variant_id',
        'quantity',
    ];

    /**
     * Get the deal that owns the combo product.
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * Get the product associated with the combo.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the product variant associated with the combo.
     */
    public function variant()
    {
        return $this->belongsTo(ProductVarient::class, 'variant_id');
    }


}

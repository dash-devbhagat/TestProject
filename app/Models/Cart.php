<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'cart_total', 'combo_discount'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function recalculateTotal()
    {
        $sum = $this->items()->where('is_free', false)
            ->with('productVariant')
            ->get()
            ->sum(function ($item) {
                return $item->productVariant->price * $item->quantity;
            });
        // Subtract any combo discount that has been applied
        $this->cart_total = $sum - $this->combo_discount;
        $this->save();
    }
}

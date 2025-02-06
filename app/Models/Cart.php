<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'cart_total'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function recalculateTotal()
    {
        $this->cart_total = $this->items()->where('is_free', false)
            ->with('productVariant')
            ->get()
            ->sum(function ($item) {
                return $item->productVariant->price * $item->quantity;
            });
        $this->save();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'bonus_id', 'amount', 'payment_status', 'parent_id', 'child_id'];

    public function user()
    {
        return $this->belongsTo(MobileUser::class);
    }
    public function bonus()
    {
        return $this->belongsTo(Bonus::class);
    }
}

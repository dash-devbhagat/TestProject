<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redeem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function mobileUser()
    {
        return $this->belongsTo(MobileUser::class, 'user_id');
    }
}

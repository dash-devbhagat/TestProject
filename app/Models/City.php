<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name', 'state_id'];

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'city_id');
    }
}

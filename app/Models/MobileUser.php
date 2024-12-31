<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'auth_token',
        'fcm_token',
        'device_type',
    ];

    protected $hidden = [
        'password',
        'auth_token',
        'fcm_token',
    ];
}

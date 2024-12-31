<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class MobileUser extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'fcm_token',
        'device_type',
        'auth_token',
        'email_verified_at',
        'email_verification_token',
        'referral_code',
        'referred_by',
        'phone',
        'gender',
        'profilepic',
        'birthdate',
        'is_profile_complete'
    ];


    protected $hidden = [
        'password',
    ];
}

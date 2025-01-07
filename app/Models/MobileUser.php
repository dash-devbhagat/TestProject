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
        'auth_token_expires_at',
        'email_verified_at',
        'email_verification_token',
        'referral_code',
        'referred_by',
        'phone',
        'gender',
        'profilepic',
        'birthdate',
        'is_profile_complete',
        'address_id'
    ];


    protected $hidden = [
        'password',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id', 'id');
    }

    public function bonus()
    {
        return $this->hasMany(Bonus::class, 'id', 'bonus_id');
    }

    public function updateAddress($addressData)
    {
        $address = $this->address ?? new Address(); // Create new address if not present
        $address->fill($addressData);

        // Ensure that user_id is assigned before saving
        $address->user_id = $this->id; // Assign the current user's id to user_id

        $address->save();

        // Optionally, update the address_id of the user to link to the new address
        $this->address_id = $address->id;
        $this->save();
    }
}

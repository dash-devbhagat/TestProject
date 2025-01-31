<?php

namespace App\Http\Middleware\API;

use Closure;
use Illuminate\Http\Request;

class MobUserCheckProfile
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Required fields to consider the profile complete
        $requiredFields = ['phone', 'gender', 'birthdate', 'address_id'];
        $isProfileComplete = true;

        foreach ($requiredFields as $field) {
            if (empty($user->{$field})) {
                $isProfileComplete = false;
                break;
            }
        }

        $user->load('address');

        // Update profile completion status if needed
        if ($user->is_profile_complete !== $isProfileComplete) {
            $user->is_profile_complete = $isProfileComplete;
            $user->save();
        }

        // Restrict access to certain routes for incomplete profiles
        if (!$isProfileComplete) {
            $allowedRoutes = ['mobile/signout', 'mobile/completeprofile'];

            if (!in_array($request->route()->uri(), $allowedRoutes)) {
                return response()->json([

            'data' => [
                'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'profilePicture' => $user->profilepic,
            'birthDate' => $user->birthdate,
            'is_profile_complete' => $user->is_profile_complete,
            'referralCode' => $user->referral_code,
'address' => $user->address ? [
    'addressLine' => $user->address->address_line ?? 'null',
    'city' => $user->address->city ? $user->address->city->name : 'null',
    'city_id' => $user->address->city ? $user->address->city->id : 'null',
    'state' => $user->address->state ? $user->address->state->name : 'null',
    'state_id' => $user->address->state ? $user->address->state->id : 'null',
    'zipCode' => $user->address->zip_code ?? 'null',
    'latitude' => $user->address->latitude ?? 'null',
    'longitude' => $user->address->longitude ?? 'null',
] : [
    'addressLine' => 'null',
    'city' => 'null',
    'city_id' => 'null',
    'state' => 'null',
    'state_id' => 'null',
    'zipCode' => 'null',
    'latitude' => 'null',
    'longitude' => 'null',
],
        ],
            ],
            'meta' => [
                'success' => false,
                'message' => 'Your profile is incomplete. Please complete your profile to access this resource.',
            ],
        ], 200);
            }
        }

        return $next($request);
    }
}

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

                    'data' => json_decode('{}'),
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

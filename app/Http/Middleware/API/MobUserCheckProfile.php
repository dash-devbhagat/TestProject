<?php

namespace App\Http\Middleware\API;

use Closure;
use Illuminate\Http\Request;

class MobUserCheckProfile
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();  // Get the authenticated user

        // Check if any of the required fields are missing
        $requiredFields = ['phone', 'gender', 'birthdate'];
        $isProfileComplete = true;

        foreach ($requiredFields as $field) {
            if (empty($user->{$field})) {
                $isProfileComplete = false;
                break;
            }
        }

        // Update the profile completion status if it has changed
        if ($user->is_profile_complete !== $isProfileComplete) {
            $user->is_profile_complete = $isProfileComplete;
            $user->save();
        }

        // If the profile is incomplete, restrict access to certain routes
        if (!$isProfileComplete) {
            $allowedRoutes = ['mobile/signout', 'mobile/completeprofile'];

            if (!in_array($request->route()->uri(), $allowedRoutes)) {
                return response()->json([
                    'meta' => [
                        'success' => false,
                        'message' => 'Your profile is incomplete. Please complete your profile to access this resource.',
                    ],
                    'data' => json_decode('{}'),
                ], 200); // 200 Forbidden status
            }
        }

        // Proceed to the next middleware or request handler
        return $next($request);
    }
}

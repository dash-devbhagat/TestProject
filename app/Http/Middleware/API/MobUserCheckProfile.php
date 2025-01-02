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

        // Check if the profile is incomplete
        if (!$user->phone || !$user->gender || !$user->birthdate) {
            return response()->json([
                'message' => 'Please complete your profile to proceed.',
                'profile_complete' => false,
            ], 400);
        }

        // Mark the profile as complete if all fields are filled
        if ($user->phone && $user->gender && $user->birthdate && !$user->is_profile_complete) {
            $user->is_profile_complete = true;
            $user->save();
        }

        return $next($request);
    }
}

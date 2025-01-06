<?php

namespace App\Http\Middleware\API;

use Closure;
use Illuminate\Http\Request;
use App\Models\MobileUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CustomAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'message' => 'Unauthorized. Token is missing.',
                ],
                'data' => json_decode('{}'),
            ], 401);
        }

        $user = MobileUser::where('auth_token', $token)->first();

        if (!$user) {
            return response()->json([

                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Unauthorized. Invalid token.',
                ],

            ], 401);
        }

        if ($user->auth_token_expires_at && now()->greaterThan($user->auth_token_expires_at)) {
            return response()->json([

                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Token has expired.',
                ],

            ], 403);
        }

        // Set the authenticated user
        Auth::setUser($user);

        return $next($request);
    }
}

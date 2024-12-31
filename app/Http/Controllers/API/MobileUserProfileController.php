<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MobileUser;
use Illuminate\Support\Facades\Response;

class MobileUserProfileController extends Controller
{
    /**
     * Display the authenticated user's profile details.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        // Retrieve the authenticated user
        $user = $request->user();

        // If user is not authenticated, return an error response
        if (!$user) {
            return Response::json([
                'message' => 'User not found or unauthenticated.'
            ], 401);
        }

        // Prepare the response data
        $profileData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'birthdate' => $user->birthdate,
            'referral_code' => $user->referral_code,
        ];

        // Return a successful response with user profile data
        return Response::json([
            'message' => 'User profile retrieved successfully.',
            'data'    => $profileData,
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MobileUser;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

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

    public function completeprofile(Request $request)
    {
        // Validate the profile fields
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:15',  // You can adjust the max length for phone
            'gender' => 'required|in:male,female,other',  // Restrict gender to specific values
            'birthdate' => 'required|date',  // Ensure the birthdate is a valid date
        ]);

        // If validation fails, return a 422 error with the validation messages
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Retrieve the authenticated user
        $user = $request->user();

        // Update the user's profile data
        $user->phone = $request->phone;
        $user->gender = $request->gender;
        $user->birthdate = $request->birthdate;

        // Mark the profile as complete
        $user->is_profile_complete = true;
        $user->save();

        // Return a successful response
        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => [
                'phone' => $user->phone,
                'gender' => $user->gender,
                'birthdate' => $user->birthdate,
                'is_profile_complete' => $user->is_profile_complete
            ]
        ], 200);
    }
}

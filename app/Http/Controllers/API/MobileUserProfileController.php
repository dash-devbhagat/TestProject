<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MobileUser;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'User not found or unauthenticated.',
                ],
            ], 200); // 200 Unauthorized status
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
        return response()->json([
            'data' => $profileData,
            'meta' => [
                'success' => true,
                'message' => 'User profile retrieved successfully.',
            ],
        ], 200); // 200 OK status
    }


    public function completeprofile(Request $request)
    {
        // Validate the profile fields
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:15',  // You can adjust the max length for phone
            'gender' => 'required|in:male,female,other',  // Restrict gender to specific values
            'birthdate' => 'required|date',  // Ensure the birthdate is a valid date
        ]);

        // If validation fails, return the first validation error
        if ($validator->fails()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => $validator->errors()->first(), // Show only the first error message
                ],
            ], 200); // 200 OK status
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
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'birthdate' => $user->birthdate,
                'referral_code' => $user->referral_code,
                'is_profile_complete' => $user->is_profile_complete
            ],
            'meta' => [
                'success' => true,
                'message' => 'Profile updated successfully.',
            ],
        ], 200); // 200 OK status
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:mobile_users,email,' . $request->user()->id,
            'phone' => 'required|string|max:15',
            'gender' => 'required|in:male,female,other',
            'birthdate' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ],
            ], 200);
        }

        $user = $request->user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->gender = $request->gender;
        $user->birthdate = $request->birthdate;
        $user->save();

        return response()->json([
            'meta' => [
                'success' => true,
                'message' => 'Profile updated successfully.',
            ],
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'birthdate' => $user->birthdate,
            ],
        ], 200);
    }

    public function updateProfilePic(Request $request)
    {
        // Validate the profile picture input
        $validator = Validator::make($request->all(), [
            'profilepic' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ],
            ], 200); // 200 Unprocessable Entity status
        }

        // Retrieve the authenticated user
        $user = $request->user();

        if ($request->hasFile('profilepic')) {
            $image = $request->file('profilepic');
            $imageSize = getimagesize($image);

            // Validate the image dimensions
            if ($imageSize[0] > 500 || $imageSize[1] > 500) {
                return response()->json([
                    'meta' => [
                        'success' => false,
                        'message' => 'The profile picture must be 500x500 pixels or smaller.',
                    ],
                    'data' => json_decode('{}'),
                ], 200);
            }

            // Delete the old profile picture if it exists
            if ($user->profilepic && Storage::disk('public')->exists($user->profilepic)) {
                Storage::disk('public')->delete($user->profilepic);
            }

            // Store the new profile picture
            $profilePicPath = $image->store('profile_pics', 'public');
            $user->profilepic = $profilePicPath;
            $user->save();
        }

        return response()->json([
            'meta' => [
                'success' => true,
                'message' => 'Profile picture updated successfully.',
            ],
            'data' => [
                'profilepic' => $user->profilepic,
            ],
        ], 200); // 200 OK status
    }
}

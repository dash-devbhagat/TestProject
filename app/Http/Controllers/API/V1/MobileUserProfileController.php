<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MobileUser;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Bonus;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

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

        $user->load('address');

        // Prepare the response data
        $profileData = [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'birthdate' => $user->birthdate,
            'is_profile_complete' => $user->is_profile_complete,
            'profilePicture' => $user->profilepic,
            'referral_code' => $user->referral_code,
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
        ];


        // Return a successful response with user profile data
        return response()->json([
            'data' => ['user' => $profileData],
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
            'phone' => 'required|string|max:15',
            'gender' => 'required|in:male,female,other',
            'birthdate' => 'required|date',
            'address_line' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'state_id' => 'required|exists:states,id',
            'zip_code' => 'required|string|max:20',
            'latitude' => 'required|nullable|numeric',
            'longitude' => 'required|nullable|numeric',
        ]);

        $validator->after(function ($validator) use ($request) {
            $city = \App\Models\City::find($request->city_id);
            $state = \App\Models\State::find($request->state_id);

            if (
                $city && $state && $city->state_id != $state->id
            ) {
                $validator->errors()->add('city_id', 'The selected city does not belong to the chosen state.');
            }
        });

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

        $user->updateAddress([
            'address_line' => $request->address_line,
            'city_id' => $request->city_id,
            'state_id' => $request->state_id,
            'zip_code' => $request->zip_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        // Mark the profile as complete
        $user->is_profile_complete = true;
        $user->save();

        $user->load('address');

        $address = $user->address ? [
            'address_line' => $user->address->address_line,
            'city_id' => $user->address->city ? $user->address->city->id : null,
            'city' => $user->address->city ? $user->address->city->name : null,
            'state_id' => $user->address->state ? $user->address->state->id : null, 
            'state' => $user->address->state ? $user->address->state->name : null,
            'zip_code' => $user->address->zip_code,
            'latitude' => $user->address->latitude,
            'longitude' => $user->address->longitude,
        ] : null;

        // Return a successful response
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
                'success' => true,
                'message' => 'Profile Complete successfully.',
            ],
        ], 200); // 200 OK status
    }

    public function updateProfile(Request $request)
    {
        // Validate the profile fields
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:mobile_users,email,' . $request->user()->id,
            'phone' => 'required|string|max:15',
            'gender' => 'required|in:male,female,other',
            'birthdate' => 'required|date',
            'address_line' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'state_id' => 'required|exists:states,id',
            'zip_code' => 'required|string|max:20',
            'latitude' => 'required|nullable|numeric',
            'longitude' => 'required|nullable|numeric',
        ]);

        $validator->after(function ($validator) use ($request) {
            $city = \App\Models\City::find($request->city_id);
            $state = \App\Models\State::find($request->state_id);

            if (
                $city && $state && $city->state_id != $state->id
            ) {
                $validator->errors()->add('city_id', 'The selected city does not belong to the chosen state.');
            }
        });

        // If validation fails, return the first validation error
        if ($validator->fails()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ],
            ], 200);
        }

        // Retrieve the authenticated user
        $user = $request->user();

        // Update the user's basic profile information
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->gender = $request->gender;
        $user->birthdate = $request->birthdate;

        // If address-related fields are provided, update the address
        if ($request->has('address_line') || $request->has('city_id') || $request->has('state_id') || $request->has('zip_code') || $request->has('latitude') || $request->has('longitude')) {
            $addressData = [
                'address_line' => $request->address_line,
                'city_id' => $request->city_id,
                'state_id' => $request->state_id,
                'zip_code' => $request->zip_code,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ];

            // Update the user's address or create a new one if none exists
            $user->updateAddress($addressData);
        }

        // Save the updated user
        $user->save();

        // Load the updated address
        $user->load('address');

        // Prepare the address data with only the required fields
        $address = $user->address ? [
            'address_line' => $user->address->address_line,
            'city_id' => $user->address->city ? $user->address->city->id : null,
            'city' => $user->address->city ? $user->address->city->name : null,
            'state_id' => $user->address->state ? $user->address->state->id : null, 
            'state' => $user->address->state ? $user->address->state->name : null,
            'zip_code' => $user->address->zip_code,
            'latitude' => $user->address->latitude,
            'longitude' => $user->address->longitude,
        ] : null;

        // Return the successful response with the updated profile and address
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
                'success' => true,
                'message' => 'Profile updated successfully.',
            ]
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

                    'data' => json_decode('{}'),
                    'meta' => [
                        'success' => false,
                        'message' => 'The profile picture must be 500x500 pixels or smaller.',
                    ]

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

            'data' => [
                'profilepic' => $user->profilepic,
            ],
            'meta' => [
                'success' => true,
                'message' => 'Profile picture updated successfully.',
            ]
        ], 200); // 200 OK status
    }

public function showBonusDetails(Request $request)
    {
        // Retrieve the authenticated user
        $user = Auth::user();

        // Fetch all bonuses associated with the user
        $bonusDetails = [];
        $totalBonusAmount = 0;
        $totalRemainingBonusAmount = 0;
        $totalBonusUsed = 0;

        $bonusTypes = [];

        // Loop through all payments to fetch bonuses
        foreach ($user->payments as $payment) {
            // Fetch the bonus associated with this payment
            $bonus = Bonus::find($payment->bonus_id);

            if ($bonus && $bonus->is_active) {
                $usedBonus = $payment->amount - $payment->remaining_amount;

                // Accumulate totals for each bonus type
                if (!isset($bonusTypes[$bonus->type])) {
                    $bonusTypes[$bonus->type] = [
                        'total_available' => 0,
                        'total_used' => 0,
                        'percentage' => $bonus->percentage,
                    ];
                }

                // Accumulate available and used bonuses
                $bonusTypes[$bonus->type]['total_available'] += $payment->remaining_amount + $usedBonus;
                $bonusTypes[$bonus->type]['total_used'] += $usedBonus;

                // Update the total amounts
                $totalBonusAmount += $payment->amount;
                $totalRemainingBonusAmount += $payment->remaining_amount;
                $totalBonusUsed += $usedBonus;
            }
        }

        // Prepare the bonus type details
        foreach ($bonusTypes as $type => $details) {
            $bonusDetails[] = [
                'bonus_type' => $type,
                'total_bonus_amount' => number_format($details['total_available'], 2, '.', ''),
                'remaining_bonus_amount' => number_format($details['total_available'] - $details['total_used'], 2, '.', ''),
                'percentage' => number_format($details['percentage'], 2, '.', ''),
                'total_used_bonus' => number_format($details['total_used'], 2, '.', ''),
            ];
        }

        // Return the response with bonus details
        return response()->json([
            'data' => [
                'total_bonus_amount' => number_format($totalBonusAmount, 2, '.', ''),
                'remaining_total_bonus' => number_format($totalRemainingBonusAmount, 2, '.', ''),
                'total_bonus_used' => number_format($totalBonusUsed, 2, '.', ''),
                'bonus_details' => $bonusDetails,
            ],
            'meta' => [
                'success' => true,
                'message' => 'Bonus details retrieved successfully.',
            ],
        ], 200);
    }
}

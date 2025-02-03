<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\MobileUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\EmailVerification;
use App\Models\Bonus;
use App\Models\Payment;
use Carbon\Carbon;



class MobileUserController extends Controller
{
    // Signup API
    public function signup(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:mobile_users',
            'password' => 'required|string|min:8|confirmed',
            'referral_code' => 'nullable|string|max:10',  // Optional referral code
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => $validator->getMessageBag()->first(),
                ],
            ], 200);
        }

        // Check if the referral code is provided
        if ($request->referral_code) {
            // Validate if the referral code exists in the database
            $referrer = MobileUser::where('referral_code', $request->referral_code)->first();

            if (!$referrer) {
                return response()->json([
                    'data' => json_decode('{}'),
                    'meta' => [
                        'success' => false,
                        'message' => 'The referral code is invalid or does not exist.',
                    ],
                ], 200);
            }
        }

        // Create a new user
        $user = MobileUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verification_token' => Str::random(60), // Generate a unique token for email verification
            'referred_by' => $referrer->id ?? null,  // Set the referring user's ID if referral code is valid
        ]);

        // Send email verification link
        Mail::to($user->email)->send(new EmailVerification($user));

        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => true,
                'message' => 'User registered successfully. Please check your email to verify your account.',
            ],
        ], 200);
    }




    // Signin API
    public function signin(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
            'fcm_token' => 'required|string',
            'device_type' => 'required|string|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => $validator->getMessageBag()->first(),
                ],
            ], 200);
        }

        // Check if the user exists
        $user = MobileUser::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'The provided credentials are incorrect.',
                ],
            ], 200);
        }

        // Check if the user's account is active
        if (!$user->is_active) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Your account is disabled. Please contact your admin.',
                ],
            ], 200); // 200 Forbidden status
        }

        // Check if email is verified
        if (!$user->email_verified_at) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Your email is not verified. Please verify your email before signing in.',
                ],
            ], 200); // 200 Bad Request status
        }

        $authToken = Str::random(60);
        $user->auth_token = $authToken;
        $user->auth_token_expires_at = now()->addMinutes(1200); // Set expiration time
        $user->save();

        $user->update([
            'auth_token' => $authToken,
            'fcm_token' => $request->fcm_token,
            'device_type' => $request->device_type,
        ]);

        $user->load('address');
        

        // **Assign missing bonuses**
        $activeBonuses = Bonus::whereNotIn('type', ['signup', 'referral'])
            ->where('is_active', true)
            ->get();

        foreach ($activeBonuses as $bonus) {
            // Check if user already has this bonus
            $existingPayment = Payment::where('user_id', $user->id)
                ->where('bonus_id', $bonus->id)
                ->exists();

            if (!$existingPayment) {
                Payment::create([
                    'user_id' => $user->id,
                    'bonus_id' => $bonus->id,
                    'amount' => $bonus->amount,
                    'remaining_amount' => $bonus->amount,
                    'payment_status' => 'completed',
                ]);
            }
        }

        // // Fetch all active bonuses except signup and referral
        //         $activeBonuses = Bonus::whereNotIn('type', ['signup', 'referral'])
        //         ->where('is_active', true)
        //         ->get();

        //         foreach ($activeBonuses as $bonus) {
        //             Payment::updateOrCreate(
        //                 [
        //                     'user_id' => $user->id,
        //                     'bonus_id' => $bonus->id,
        //                 ],
        //                 [
        //                     // Dynamically fetch the latest amount
        //                     'amount' => $bonus->amount, 
        //                     'remaining_amount' => $bonus->amount, 
        //                     'payment_status' => 'completed',
        //                 ]
        //             );
        //         }

        // Check if profile is complete (based on phone, gender, and birthdate)
        if (empty($user->phone) || empty($user->gender) || empty($user->birthdate) || empty($user->address_id)) {
            $user->is_profile_complete = false;
            $user->save();

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
                    'access_token' => $authToken,
                    'token_type' => 'Bearer',
                    'success' => true,
                    'message' => 'Your profile is incomplete. Please complete your profile.'
                ],
            ], 200); // 200 Bad Request status
        } else {
            // Profile is complete, so now generate the authentication token
            $user->is_profile_complete = true;
            $user->save();






            // Update user with the authentication token, fcm token, and device type


            // Return response with token and message
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
                    'accessToken' => $authToken,
                    'tokenType' => 'Bearer',
                    'status' => 200,
                    'success' => true,
                    'message' => 'Logged in successfully.',
                ],
            ], 200);
            // 200 OK status
        }
    }





    // Signout API
    public function signout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'No authenticated user found.',
                ],
            ], 200); // 200 Bad Request status
        }

        // Log the user out by deleting their tokens and setting auth_token to null
        $user->auth_token = null;
        $user->save();

        // Return success response
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => true,
                'message' => 'Logged out successfully',
            ],
        ], 200); // 200 OK status
    }


    public function verifyEmail($token)
    {
        // Find the user by the verification token
        $user = MobileUser::where('email_verification_token', $token)->first();

        if (!$user) {
            return view('emails.verification-result', [
                'success' => false,
                'message' => 'The verification link is invalid or has expired. Please try requesting a new verification email.',
            ]);
        }

        // Check if the user's email is already verified
        if ($user->email_verified_at) {
            return view('emails.verification-result', [
                'success' => true,
                'message' => 'Your email has already been verified. You can now log in.',
            ]);
        }

        // Verify the user's email
        $user->email_verified_at = now();
        $user->email_verification_token = null;

        // Generate a unique referral code for the user
        $user->referral_code = Str::random(8); // Adjust length as needed
        $user->save();

        // Assign signup bonus
        $signupBonus = Bonus::where('type', 'signup')->first();

        if ($signupBonus) {
            Payment::create([
                'user_id' => $user->id,
                'bonus_id' => $signupBonus->id,
                'amount' => $signupBonus->amount,
                'remaining_amount' => $signupBonus->amount,
                'payment_status' => 'completed',
                'parent_id' => $user->referred_by,
            ]);
        }

        // Handle referral bonus if the user was referred
        if ($user->referred_by) {
            $referralBonus = Bonus::where('type', 'referral')->first();

            if ($referralBonus) {
                Payment::create([
                    'user_id' => $user->referred_by,
                    'bonus_id' => $referralBonus->id,
                    'amount' => $referralBonus->amount,
                    'remaining_amount' => $referralBonus->amount,
                    'payment_status' => 'completed',
                    'child_id' => $user->id,
                ]);
            }
        }

        return view('emails.verification-result', [
            'success' => true,
            'message' => 'Congratulations! Your email has been verified successfully.',
        ]);
    }



    // Add this method to the MobileUserController

    public function changePassword(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed', // Confirmed ensures that the new password and new_password_confirmation match
        ]);

        // If validation fails, return errors in the expected format
        if ($validator->fails()) {
            return response()->json(
                [
                    'data' => json_decode('{}'),
                    'meta' => [
                        'success' => false,
                        'message' => $validator->errors()->first(), // Show only the first error message
                    ],
                ],
                200
            );
        }

        // Retrieve the authenticated user
        $user = $request->user();

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(
                [
                    'data' => json_decode('{}'),
                    'meta' => [
                        'success' => false,
                        'message' => 'The current password is incorrect.',
                    ],
                ],
                200
            );
        }

        // Check if the new password is the same as the current password
        if ($request->current_password === $request->new_password) {
            return response()->json(
                [
                    'data' => json_decode('{}'),
                    'meta' => [
                        'success' => false,
                        'message' => 'The new password cannot be the same as the current password.',
                    ],
                ],
                200
            );
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Return a success response
        return response()->json(
            [
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => true,
                    'message' => 'Password changed successfully.',
                ],
            ],
            200
        );
    }

    public function forgotPassword(Request $request)
    {
        // Validate email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:mobile_users,email',
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

        $user = MobileUser::where('email', $request->email)->first();

        // Generate OTP and expiry time
        $otp = rand(100000, 999999); // 6-digit OTP
        $otpExpiry = Carbon::now()->addMinutes(2); // OTP valid for 2 minutes

        // Update OTP and OTP expiry time
        $user->otp = (string) $otp;  // Ensure OTP is stored as a string
        $user->otp_expires_at = $otpExpiry;

        // Save the changes
        $user->save();

        // Log the OTP for debugging (remove in production)
        \Log::info("Generated OTP for {$user->email}: {$otp}");

        // Send OTP email
        // Send the email using the new view path
        Mail::send('emails.API.password-reset', ['user' => $user, 'otp' => $otp], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Password Reset Request');
        });

        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => true,
                'message' => 'An OTP has been sent to your email.',
            ],
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        // Validate OTP and new password
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:mobile_users,email',
            'otp' => 'required|digits:6',
            'new_password' => 'required|string|min:8|confirmed',
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

        // Fetch the user
        $user = MobileUser::where('email', $request->email)->first();

        // Verify OTP and its expiry
        if ((string) $user->otp !== (string) $request->otp || Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'The OTP is invalid or expired.',
                ],
            ], 200);
        }

        // Reset the password
        $user->update([
            'password' => Hash::make($request->new_password), // Encrypt the new password
            'otp' => null, // Clear the OTP
            'otp_expires_at' => null, // Clear the expiry time
        ]);

        // Ensure the database is actually being updated to NULL
        \DB::table('mobile_users')->where('email', $request->email)->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        // Send success email notification
        Mail::send('emails.API.password-reset-success', ['user' => $user], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Password Reset Successfully');
        });

        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => true,
                'message' => 'Password reset successfully.',
            ],
        ], 200);
    }
}

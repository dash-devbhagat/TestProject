<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\MobileUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\EmailVerification;


class MobileUserController extends Controller
{
    // Signup API
    public function signup(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:mobile_users',
            'password' => 'required|string|min:8|confirmed',
            'referral_code' => 'nullable|string|max:10',  // Optional referral code
        ]);

        // Check if the referral code is provided
        if ($request->referral_code) {
            // Validate if the referral code exists in the database
            $referrer = MobileUser::where('referral_code', $request->referral_code)->first();

            if (!$referrer) {
                return response()->json([
                    'message' => 'The referral code is invalid or does not exist.'
                ], 400);
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
            'message' => 'User registered successfully. Please check your email to verify your account.'
        ], 201);
    }



    // Signin API
    public function signin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
            'fcm_token' => 'required|string',
            'device_type' => 'required|in:android,ios',
        ]);

        $user = MobileUser::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if the email is verified
        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Your email is not verified. Please verify your email before signing in.'
            ], 400);
        }

        $authToken = $user->createToken('auth_token')->plainTextToken;

        $user->update([
            'auth_token' => $authToken,
            'fcm_token' => $request->fcm_token,
            'device_type' => $request->device_type,
        ]);

        return response()->json([
            'access_token' => $authToken,
            'token_type' => 'Bearer',
        ], 200);
    }


    // Signout API
    public function signout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();
            $user->update(['auth_token' => null]);
        }

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function verifyEmail($token)
    {
        // Find the user by the verification token
        $user = MobileUser::where('email_verification_token', $token)->first();

        if (!$user) {
            // If no user is found or the token is invalid
            return response()->json([
                'message' => 'The verification link is invalid or has expired. Please try requesting a new verification email.'
            ], 400);
        }

        // Check if the user's email is already verified
        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Your email has already been verified. You can now log in.'
            ], 200);
        }

        // Verify the user's email by setting the email_verified_at field
        $user->email_verified_at = now();
        $user->email_verification_token = null; // Clear the token after successful verification

        // Generate a unique referral code for the user
        $user->referral_code = Str::random(10);  // Adjust length as needed, 10 chars here

        $user->save();

        // Return a success response with the referral code
        return response()->json([
            'message' => 'Congratulations! Your email has been verified successfully.',
            'referral_code' => $user->referral_code,
            'user' => $user
        ], 200);
    }
}

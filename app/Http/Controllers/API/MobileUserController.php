<?php

namespace App\Http\Controllers;

use App\Models\MobileUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MobileUserController extends Controller
{
    // User Signup API
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:mobile_users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = MobileUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'auth_token' => Str::random(60), // Create random auth token
        ]);

        return response()->json([
            'message' => 'User registered successfully!',
            'data' => $user,
        ], 201);
    }

    // User Signin API
    public function signin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'fcm_token' => 'required|string',
            'device_type' => 'required|in:android,ios',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = MobileUser::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Update user with FCM token and device type
        $user->update([
            'fcm_token' => $request->fcm_token,
            'device_type' => $request->device_type,
            'auth_token' => Str::random(60), // New Auth Token
        ]);

        return response()->json([
            'message' => 'User signed in successfully!',
            'auth_token' => $user->auth_token,
            'user' => $user,
        ]);
    }

    // User Signout API
    public function signout(Request $request)
    {
        $user = MobileUser::where('auth_token', $request->auth_token)->first();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Set auth_token to null
        $user->update(['auth_token' => null]);

        return response()->json([
            'message' => 'User signed out successfully!',
        ]);
    }
}

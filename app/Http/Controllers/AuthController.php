<?php

// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'isDelete' => false])) {
            $request->session()->regenerate();

            // Check if profile is complete
            $user = Auth::user();
            if ($user->phone && $user->storename && $user->location && $user->latitude && $user->longitude && $user->logo) {
                // Set profile completion flag
                $user->isProfile = true;
                $user->save();

                return redirect()->route('dashboard');
            }

            // Redirect to profile completion page
            return redirect()->route('complete-profile');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // app/Http/Controllers/AuthController.php

    public function updateProfile(Request $request)
    {
        // Validate the profile fields
        $request->validate([
            'phone' => 'required',
            'storename' => 'required',
            'location' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Ensuring the logo is required and is a valid image file
        ]);

        // If validation passes, save the user's profile
        $user = Auth::user();
        $user->phone = $request->phone;
        $user->storename = $request->storename;
        $user->location = $request->location;
        $user->latitude = $request->latitude;
        $user->longitude = $request->longitude;

        // Handle logo upload (if exists)
        if ($request->hasFile('logo')) {
            // Validate the image size (100x100 px max)
            $image = $request->file('logo');
            $imageSize = getimagesize($image);

            if ($imageSize[0] > 100 || $imageSize[1] > 100) {
                return back()->withErrors(['logo' => 'The logo must be 100x100 pixels or smaller.']);
            }

            // Store the logo
            $logoPath = $image->store('logos', 'public');
            $user->logo = $logoPath;
        }

        // Mark the profile as complete
        $user->isProfile = true;
        $user->save();

        // Redirect to the dashboard
        return redirect()->route('dashboard');
    }
}

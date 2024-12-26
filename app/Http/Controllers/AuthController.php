<?php

// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
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

        // return back()->withErrors([
        //     'email' => 'The provided credentials do not match our records.',
        // ]);
        return redirect()->route('home')->with('error', 'The provided credentials do not match our records.');
    
    }

    public function showForgotPasswordForm()
    {
        return view('forgot-password'); 
    }

    public function handleForgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // return redirect()->route('home')->withErrors(['email' => 'Email address not found']);
            return redirect()->route('home')->with('error', 'Email address not found');
        }

        $randomPassword = Str::random(8); 

        // Update user's password in the database
        $user->password = Hash::make($randomPassword);
        $user->save();

        Mail::send('emails.password-reset', ['password' => $randomPassword], function($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your Password Reset');
        });

        return redirect()->route('home')->with('success', 'New password has been sent to your email');
    }

    public function logout(Request $request){
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

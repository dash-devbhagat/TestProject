<?php

// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\SendPasswordChangeMail;  // Correct import
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;





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

            if ($user->role === 'admin') { // Adjust based on your role attribute
                return redirect()->route('dashboard');
            }

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
        return redirect()->route('login.page')->with('error', 'The provided credentials do not match our records.');
    }

    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

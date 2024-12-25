<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function login(Request $request){

        // dd($request->all());
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'isDelete' => false])) {

            $request->session()->regenerate();

            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard'); 
            }

            return redirect()->route('user.dashboard'); 
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
}

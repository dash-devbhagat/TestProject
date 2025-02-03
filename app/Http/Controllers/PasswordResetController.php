<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showForgotPasswordForm()
    {
        return view('forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // Laravel automatically sends the email using the Password::sendResetLink() method. To customize the email:php artisan vendor:publish --tag=laravel-notifications

        // $status = Password::sendResetLink($request->only('email'));

        // return $status === Password::RESET_LINK_SENT
        //     ? back()->with('success', __($status))
        //     : back()->withErrors(['error' => __($status)]);

        $token = Str::random(16);
        // dd($token);
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        // Store the token in the user's table or a dedicated password reset table
        $user->password_reset_token = $token;
        $user->password_reset_token_created_at = now();
        $user->save();

        // Generate the password reset URL
        $url = url(route('password.reset', ['token' => $token, 'email' => $email], false));

        // Send the custom email
        Mail::to($email)->queue(new ResetPasswordMail($url));

        return back()->with('success', 'Password reset email sent!');
    }

    public function showResetPasswordForm(Request $request, $token)
    {
        // dd($token);

        $user = User::where('password_reset_token', $token)->first();

        if ($user) {
            // Check if the token is expired (more than 1 minute old)
            $tokenCreationTime = $user->password_reset_token_created_at;
            $expirationTime = now()->diffInMinutes($tokenCreationTime);

            // Expiration time is 2 minute
            if ($expirationTime <= 1) {
                // Token is valid, show reset password form
                return view('reset-password', ['token' => $token]);
            } else {
                // Token has expired
                return view('reset-password-error');
            }
        } else {
            return redirect()->route('forgot.password')->with('error', 'Password reset token does not exist.');
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('password_reset_token', $request->token)->first();

        $user->password = Hash::make($request->password);
        $user->password_reset_token = null;
        $user->password_reset_token_created_at = null;
        $user->save();

        return redirect()->route('login.page')->with('success', 'Your password has been changed successfully. Please log in.');
    }
}

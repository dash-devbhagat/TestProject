<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChangePasswordController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('login');
    })->name('login.page');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('forgot.password');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('sendResetLink');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');


Route::middleware(['auth','check.active'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard', ['user' => Auth::user()]);
    })->middleware('profile.complete')->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // for admin
    Route::resource('user', UserController::class);
    Route::post('/user/{id}/toggle-status', [UserController::class, 'toggleStatus']);
    

    // for users
    Route::get('/complete-profile', function () {
        // Redirect to dashboard if profile is complete
        if (Auth::user()->phone && Auth::user()->storename && Auth::user()->location && Auth::user()->latitude && Auth::user()->longitude && Auth::user()->logo) {
            return redirect()->route('dashboard');
        }

        return view('user.complete-profile');
    })->name('complete-profile');

    Route::post('/update-profile', [UserController::class, 'completeprofile'])->name('profile.update');


    Route::get('/change-password', [ChangePasswordController::class, 'showChangePasswordForm'])->name('change.password');
    Route::post('/change-password', [ChangePasswordController::class, 'changePassword'])->name('change.password.submit');

    Route::get('/edit-profile', [UserController::class, 'editProfile'])->name('user.edit-profile');
    Route::post('/update-profile', [UserController::class, 'updateProfile'])->name('user.update-profile');
});
Route::fallback(function () {
    return redirect()->route('login.page')->with('error', 'Page not found or unauthorized access.');
});

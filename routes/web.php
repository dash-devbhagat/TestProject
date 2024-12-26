<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


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
    })->name('home');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot.password');
Route::post('/forgot-password', [AuthController::class, 'handleForgotPassword'])->name('forgot.password.submit');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard', ['user' => Auth::user()]);
    })->middleware('profile.complete')->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // for admin
    // Route::get('/manage-users',[UserController::class,'index'])->name('manage.users');
    Route::resource('user', UserController::class);
    
    // for users
    Route::get('/complete-profile', function () {
        // Redirect to dashboard if profile is complete
        if (Auth::user()->phone && Auth::user()->storename && Auth::user()->location && Auth::user()->latitude && Auth::user()->longitude && Auth::user()->logo) {
            return redirect()->route('dashboard');
        }

        return view('user.complete-profile');
    })->name('complete-profile');
    
    Route::post('/update-profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    Route::post('/change-password-link', [AuthController::class, 'sendChangePasswordLink'])->name('password.link');
    Route::get('/change-password/{token}', [AuthController::class, 'showChangePasswordForm'])->name('password.change.form');
    Route::post('/change-password', [AuthController::class, 'handleChangePassword'])->name('password.change');
});
Route::fallback(function () {
    return redirect()->route('home')->with('error', 'Page not found or unauthorized access.');
});

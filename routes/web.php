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

Route::get('/', function () {
    return view('login');
})->name('home');

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot.password');
Route::post('/forgot-password', [AuthController::class, 'handleForgotPassword'])->name('forgot.password.submit');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard', ['user' => Auth::user()]);
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // for admin
    // Route::get('/manage-users',[UserController::class,'index'])->name('manage.users');
    Route::resource('user', UserController::class);
    
    // for users
    Route::get('/complete-profile', function () {
        return view('user.complete-profile');
    })->name('complete-profile');
    
    Route::post('/update-profile', [AuthController::class, 'updateProfile'])->name('profile.update');

});

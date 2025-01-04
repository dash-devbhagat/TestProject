<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\MobileUserController;
use App\Http\Controllers\API\V1\MobileUserProfileController;
use App\Http\Middleware\API\MobUserCheckProfile;
use App\Http\Middleware\API\CustomAuth;

// Authentication Routes
Route::post('v1/auth/signup', [MobileUserController::class, 'signup']);
Route::post('v1/auth/signin', [MobileUserController::class, 'signin']);
Route::middleware('custom.auth')->post('v1/auth/signout', [MobileUserController::class, 'signout']);
Route::middleware(['custom.auth'])->post('v1/auth/change-password', [MobileUserController::class, 'changePassword']);
Route::middleware('custom.auth')->post('v1/user/profile/complete', [MobileUserProfileController::class, 'completeprofile']);

// Email Verification
Route::get('v1/auth/verify/{token}', [MobileUserController::class, 'verifyEmail'])->name('api.verifyEmail');

// Profile Routes
Route::middleware(['custom.auth', 'mob.check.profile'])->group(function () {
    Route::get('v1/user/profile', [MobileUserProfileController::class, 'show']);
    Route::post('v1/user/profile/update', [MobileUserProfileController::class, 'updateProfile']);
    Route::post('v1/user/profile/picture', [MobileUserProfileController::class, 'updateProfilePic']);
});


// Profile completion is allowed without full profile check




// Route::post('mobile/signup', [MobileUserController::class, 'signup']);
// Route::post('mobile/signin', [MobileUserController::class, 'signin']);

// Route::middleware('auth:sanctum')->post('mobile/signout', [MobileUserController::class, 'signout']);
// Route::get('verify/{token}', [MobileUserController::class, 'verifyEmail'])->name('api.verifyEmail');
// Route::middleware(['auth:sanctum', MobUserCheckProfile::class])->get('mobile/profile', [MobileUserProfileController::class, 'show']);
// Route::middleware('auth:sanctum')->post('mobile/completeprofile', [MobileUserProfileController::class, 'completeprofile']);
// Route::middleware('auth:sanctum')->post('mobile/updateprofile', [MobileUserProfileController::class, 'updateProfile']);

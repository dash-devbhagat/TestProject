<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MobileUserController;
use App\Http\Controllers\API\MobileUserProfileController;
use App\Http\Middleware\API\MobUserCheckProfile;

// Authentication Routes
Route::post('mobile/signup', [MobileUserController::class, 'signup']);
Route::post('mobile/signin', [MobileUserController::class, 'signin']);
Route::middleware('auth:sanctum')->post('mobile/signout', [MobileUserController::class, 'signout']);

// Email Verification
Route::get('verify/{token}', [MobileUserController::class, 'verifyEmail'])->name('api.verifyEmail');

// Profile Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware([MobUserCheckProfile::class])->group(function () {
        Route::get('mobile/profile', [MobileUserProfileController::class, 'show']);
        Route::post('mobile/updateprofile', [MobileUserProfileController::class, 'updateProfile']);
    });

    // Profile completion is allowed without full profile check
    Route::post('mobile/completeprofile', [MobileUserProfileController::class, 'completeprofile']);
});

// Route::post('mobile/signup', [MobileUserController::class, 'signup']);
// Route::post('mobile/signin', [MobileUserController::class, 'signin']);

// Route::middleware('auth:sanctum')->post('mobile/signout', [MobileUserController::class, 'signout']);
// Route::get('verify/{token}', [MobileUserController::class, 'verifyEmail'])->name('api.verifyEmail');
// Route::middleware(['auth:sanctum', MobUserCheckProfile::class])->get('mobile/profile', [MobileUserProfileController::class, 'show']);
// Route::middleware('auth:sanctum')->post('mobile/completeprofile', [MobileUserProfileController::class, 'completeprofile']);
// Route::middleware('auth:sanctum')->post('mobile/updateprofile', [MobileUserProfileController::class, 'updateProfile']);

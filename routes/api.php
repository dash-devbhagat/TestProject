<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\MobileUserController;
use App\Http\Controllers\API\V1\MobileUserProfileController;
use App\Http\Middleware\API\MobUserCheckProfile;
use App\Http\Middleware\API\CustomAuth;
use App\Http\Controllers\API\V1\CategoryAPIController;
use App\Http\Controllers\API\V1\SubCategoryAPIController;

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
    Route::get('v1/categories', [CategoryAPIController::class, 'getAllCategories']);
    Route::post('v1/subcategories', [SubCategoryAPIController::class, 'getSubCategoriesByCategoryId']);
});

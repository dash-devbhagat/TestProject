<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MobileUserController;
use App\Http\Controllers\API\MobileUserProfileController;
use App\Http\Middleware\API\MobUserCheckProfile;

Route::post('mobile/signup', [MobileUserController::class, 'signup']);
Route::post('mobile/signin', [MobileUserController::class, 'signin'])
    ->middleware('auth:sanctum', 'mob.check.profile');
Route::middleware('auth:sanctum')->post('mobile/signout', [MobileUserController::class, 'signout']);
Route::get('verify/{token}', [MobileUserController::class, 'verifyEmail'])->name('api.verifyEmail');
Route::middleware(['auth:sanctum', MobUserCheckProfile::class])->get('mobile/profile', [MobileUserProfileController::class, 'show']);
Route::middleware('auth:sanctum')->post('mobile/completeprofile', [MobileUserProfileController::class, 'completeprofile']);
Route::middleware('auth:sanctum')->post('mobile/updateprofile', [MobileUserProfileController::class, 'updateProfile']);

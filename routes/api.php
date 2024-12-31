<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MobileUserController;
use App\Http\Controllers\API\MobileUserProfileController;

Route::post('mobile/signup', [MobileUserController::class, 'signup']);
Route::post('mobile/signin', [MobileUserController::class, 'signin']);
Route::middleware('auth:sanctum')->post('mobile/signout', [MobileUserController::class, 'signout']);
Route::get('verify/{token}', [MobileUserController::class, 'verifyEmail'])->name('api.verifyEmail');
Route::middleware('auth:sanctum')->get('mobile/profile', [MobileUserProfileController::class, 'show']);

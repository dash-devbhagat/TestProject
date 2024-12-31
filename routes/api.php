<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MobileUserController;

Route::post('mobile/signup', [MobileUserController::class, 'signup']);
Route::post('mobile/signin', [MobileUserController::class, 'signin']);
Route::middleware('auth:sanctum')->post('mobile/signout', [MobileUserController::class, 'signout']);
Route::get('verify-email/{token}', [MobileUserController::class, 'verifyEmail']);

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileUserController;

Route::post('signup', [MobileUserController::class, 'signup']);
Route::post('signin', [MobileUserController::class, 'signin']);
Route::post('signout', [MobileUserController::class, 'signout'])->middleware('auth:api');

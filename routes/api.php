<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\MobileUserController;
use App\Http\Controllers\API\V1\MobileUserProfileController;
use App\Http\Middleware\API\MobUserCheckProfile;
use App\Http\Middleware\API\CustomAuth;
use App\Http\Controllers\API\V1\CategoryAPIController;
use App\Http\Controllers\API\V1\SubCategoryAPIController;
use App\Http\Controllers\API\V1\StateAPIController;
use App\Http\Controllers\API\V1\CityAPIController;
use App\Http\Controllers\API\V1\ProductAPIController;
use App\Http\Controllers\API\V1\CartController;
use App\Http\Controllers\API\V1\TransactionAPIController;
use App\Http\Controllers\API\V1\OrderAPIController;
use App\Http\Controllers\API\V1\CouponAPIController;
use App\Http\Controllers\API\V1\BranchAPIController;
use App\Http\Controllers\API\V1\DealsAPIController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {

    // Public Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('signup', [MobileUserController::class, 'signup']);
        Route::post('signin', [MobileUserController::class, 'signin']);
        Route::get('verify/{token}', [MobileUserController::class, 'verifyEmail'])->name('api.verifyEmail');
        Route::post('forgot-password', [MobileUserController::class, 'forgotPassword']);
        Route::post('reset-password', [MobileUserController::class, 'resetPassword']);
    });

    // Public Resources
    Route::get('categories', [CategoryAPIController::class, 'getAllCategories']);
    Route::post('subcategories', [SubCategoryAPIController::class, 'getSubCategoriesByCategoryId']); // Expects category_id as query param
    Route::get('states', [StateAPIController::class, 'getAllStates']);
    Route::post('cities', [CityAPIController::class, 'getCitiesByStateId']); // Expects state_id as query param
    Route::post('products', [ProductAPIController::class, 'getActiveProducts']); // Expects filters as query params
    Route::get('coupons', [CouponAPIController::class, 'getActiveCoupons']);
    Route::get('deals', [DealsAPIController::class, 'getAllDeals']);

    // Authenticated Routes (Custom Auth)
    Route::middleware(['custom.auth'])->group(function () {
        Route::post('auth/signout', [MobileUserController::class, 'signout']);
        Route::post('auth/change-password', [MobileUserController::class, 'changePassword']);
        Route::post('user/profile/complete', [MobileUserProfileController::class, 'completeprofile']);

        // Profile-Completed User Routes
        Route::middleware(['mob.check.profile'])->group(function () {
            // Profile Management
            Route::prefix('user/profile')->group(function () {
                Route::get('', [MobileUserProfileController::class, 'show']);
                Route::post('update', [MobileUserProfileController::class, 'updateProfile']);
                Route::post('picture', [MobileUserProfileController::class, 'updateProfilePic']);
                Route::get('bonus/details', [MobileUserProfileController::class, 'showBonusDetails']);
            });

            // Cart Operations
            Route::prefix('cart')->group(function () {
                Route::post('add', [CartController::class, 'addToCart']);
                Route::get('', [CartController::class, 'viewCart']);
                Route::post('update', [CartController::class, 'updateCartItem']);
                Route::post('remove', [CartController::class, 'removeFromCart']);
                Route::post('clear', [CartController::class, 'clearCart']);
                Route::post('checkout', [CartController::class, 'checkout']);
                Route::post('apply-coupon', [CouponAPIController::class, 'applyCoupon']);
                Route::post('remove-coupon', [CouponAPIController::class, 'removeCoupon']);
            });

            // Orders & Payments
            Route::post('order/payment', [TransactionAPIController::class, 'processPayment']);
            Route::get('orders', [OrderAPIController::class, 'getAllOrders']);
            Route::post('order/cancel', [OrderAPIController::class, 'cancelOrder']);
            Route::post('order/details', [OrderAPIController::class, 'getOrderDetails']);

            // Branches
            Route::get('branches/nearby', [BranchAPIController::class, 'nearbyBranches']);

            //Deals
            Route::post('deals/redeem', [DealsAPIController::class, 'redeemDeal']);
            Route::post('deals/remove', [DealsAPIController::class, 'removeDeal']);
        });
    });
});




















// // Authentication Routes
// Route::post('v1/auth/signup', [MobileUserController::class, 'signup']);
// Route::post('v1/auth/signin', [MobileUserController::class, 'signin']);
// Route::middleware('custom.auth')->post('v1/auth/signout', [MobileUserController::class, 'signout']);
// Route::middleware(['custom.auth'])->post('v1/auth/change-password', [MobileUserController::class, 'changePassword']);
// Route::middleware('custom.auth')->post('v1/user/profile/complete', [MobileUserProfileController::class, 'completeprofile']);

// // Email Verification
// Route::get('v1/auth/verify/{token}', [MobileUserController::class, 'verifyEmail'])->name('api.verifyEmail');

// Route::post('v1/auth/forgot-password', [MobileUserController::class, 'forgotPassword']);
// Route::post('v1/auth/reset-password', [MobileUserController::class, 'resetPassword']);


// Route::get('v1/categories', [CategoryAPIController::class, 'getAllCategories']);
// Route::post('v1/subcategories', [SubCategoryAPIController::class, 'getSubCategoriesByCategoryId']);
// Route::get('v1/states', [StateAPIController::class, 'getAllStates']);
// Route::post('v1/cities', [CityAPIController::class, 'getCitiesByStateId']);
// Route::post('v1/products', [ProductAPIController::class, 'getActiveProducts']);
// Route::get('v1/coupons', [CouponAPIController::class, 'getActiveCoupons']);    

// // Profile Routes
// Route::middleware(['custom.auth', 'mob.check.profile'])->group(function () {
//     Route::get('v1/user/profile', [MobileUserProfileController::class, 'show']);
//     Route::post('v1/user/profile/update', [MobileUserProfileController::class, 'updateProfile']);
//     Route::post('v1/user/profile/picture', [MobileUserProfileController::class, 'updateProfilePic']);
//     Route::post('v1/cart/add', [CartController::class, 'addToCart']);
//     Route::get('v1/cart', [CartController::class, 'viewCart']);
//     Route::post('v1/cart/update', [CartController::class, 'updateCartItem']);
//     Route::post('v1/cart/remove', [CartController::class, 'removeFromCart']);
//     Route::post('v1/cart/clear', [CartController::class, 'clearCart']);
//     Route::post('v1/cart/checkout', [CartController::class, 'checkout']);
//     Route::post('v1/order/payment', [TransactionAPIController::class, 'processPayment']);
//     Route::post('v1/order/cancel', [OrderAPIController::class, 'cancelOrder']);
//     Route::get('v1/orders', [OrderAPIController::class, 'getAllOrders']);
//     Route::post('v1/order/details', [OrderAPIController::class, 'getOrderDetails']);
//     // Route::post('v1/order/viewbonus', [TransactionAPIController::class, 'viewBonus']);
//     // Route::post('v1/order/applybonus', [TransactionAPIController::class, 'applyBonus']);
//     Route::get('v1/user/bonus/details', [MobileUserProfileController::class, 'showBonusDetails']);
//     Route::post('v1/cart/apply-coupon', [CouponAPIController::class, 'applyCoupon']);

//     Route::get('v1/branches/nearby', [BranchAPIController::class, 'nearbyBranches']);

// });

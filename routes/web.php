<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\BonusPaymentHistoryController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ChargeController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\MobileUserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderPaymentHistoryController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentHistoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\TimingController;
use App\Http\Controllers\UserController;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;







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
    })->name('login.page');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('forgot.password');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('sendResetLink');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');


Route::middleware(['auth', 'check.active'])->group(function () {

    // for both
    Route::get('/dashboard', [AuthController::class, 'index'])->name('dashboard');

    Route::get('/change-password', [ChangePasswordController::class, 'showChangePasswordForm'])->name('change.password');
    Route::post('/change-password', [ChangePasswordController::class, 'changePassword'])->name('change.password.submit');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // for admin
    Route::resource('user', UserController::class);
    Route::post('/user/{id}/toggle-status', [UserController::class, 'toggleStatus']);

    Route::get('mobileUser', [MobileUserController::class, 'index'])->name('mobileUser.index');
    Route::get('mobileUser/{id}', [MobileUserController::class, 'show'])->name('mobileUser.show');
    Route::post('/mobileUser/{id}/toggle-status', [MobileUserController::class, 'toggleStatus']);

    Route::resource('order', OrderController::class);
    Route::post('/order/updatestatus/{id}', [OrderController::class, 'updateStatus'])->name('order.updateStatus');
    Route::get('/cancelled-orders', [OrderController::class, 'cancelledOrders'])->name('cancelled-orders');
    Route::get('/orders/table', [OrderController::class, 'table'])->name('orders.table');

    Route::resource('bonus', BonusController::class);
    Route::post('/bonus/{id}/toggle-status', [BonusController::class, 'toggleStatus']);
    Route::get('bonus-history', [BonusController::class, 'bonusHistory'])->name('bonusHistory');

    Route::resource('state', StateController::class);
    Route::post('/state/{id}/toggle-status', [StateController::class, 'toggleStatus']);

    Route::resource('city', CityController::class);
    Route::post('/city/{id}/toggle-status', [CityController::class, 'toggleStatus']);

    Route::resource('category', CategoryController::class);
    Route::post('/category/{id}/toggle-status', [CategoryController::class, 'toggleStatus']);

    Route::resource('sub-category', SubCategoryController::class);
    Route::post('/sub-category/{id}/toggle-status', [SubCategoryController::class, 'toggleStatus']);
    Route::get('/sub-category/fetch/{id}', [SubCategoryController::class, 'fetchSubCategory']);

    Route::resource('product', ProductController::class);
    Route::post('/product/{id}/toggle-status', [ProductController::class, 'toggleStatus']);

    Route::resource('charge', ChargeController::class);
    Route::post('/charge/{id}/toggle-status', [ChargeController::class, 'toggleStatus']);

    Route::resource('branch', BranchController::class);
    Route::post('/branch/{id}/toggle-status', [BranchController::class, 'toggleStatus']);
    Route::post('/branch/toggle-24x7/{id}', [BranchController::class, 'toggle24x7']);

    Route::resource('timing', TimingController::class);
    Route::post('/timing/{id}/toggle-status', [TimingController::class, 'toggleStatus']);

    Route::resource('coupon', CouponController::class);
    Route::post('/coupon/{id}/toggle-status', [CouponController::class, 'toggleStatus']);

    Route::resource('deal', DealController::class);
    Route::post('/deal/{id}/toggle-status', [DealController::class, 'toggleStatus']);
    Route::get('/get-product-variants/{product_id}', [DealController::class, 'getProductVariants'])->name('get.product.variants');
    Route::get('/get-product-price/{variantId}', [DealController::class, 'getProductPrice']);

    Route::get('/bonus-payment-history', [BonusPaymentHistoryController::class, 'index'])->name('ph.index');

    Route::get('/order-payment-history', [OrderPaymentHistoryController::class, 'index'])->name('oh.index');


    // for staff
    Route::get('/complete-profile', function () {
        // Redirect to dashboard if profile is complete
        if (Auth::user()->phone && Auth::user()->storename && Auth::user()->location && Auth::user()->latitude && Auth::user()->longitude && Auth::user()->logo) {
            return redirect()->route('dashboard');
        }

        return view('user.complete-profile');
    })->name('complete-profile');

    Route::post('/update-profile', [UserController::class, 'completeprofile'])->name('profile.update');

    Route::get('/edit-profile', [UserController::class, 'editProfile'])->name('user.edit-profile');
    Route::post('/update-profile', [UserController::class, 'updateProfile'])->name('user.update-profile');
});
Route::fallback(function () {
    return redirect()->route('login.page')->with('error', 'Page not found or unauthorized access.');
});

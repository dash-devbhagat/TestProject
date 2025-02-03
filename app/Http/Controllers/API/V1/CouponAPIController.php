<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVarient;
use App\Models\Charge;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\UserCouponUsage;

class CouponAPIController extends Controller
{
    public function getActiveCoupons(Request $request)
    {
        // Fetch active coupons from the database
        $coupons = Coupon::where('is_active', 1)->get();

        if ($coupons->isEmpty()) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'message' => 'No active coupons available.',
                ],
            ], 200);
        }

        // Return the active coupons
        return response()->json([
            'data' => (object) [
                'coupons_details' => $coupons->map(function ($coupon) {
                    return [
                        'coupon_id' => $coupon->id,
                        'coupon_code' => $coupon->coupon_code,
                        'coupon_name' => $coupon->name,
                        'coupon_description' => $coupon->description,
                        'coupon_image' => $coupon->image,
                        'amount' => $coupon->amount,
                    ];
                })
            ],
            'meta' => [
                'success' => true,
                'message' => 'Active coupons fetched successfully.',
            ],
        ], 200);
    }


    public function applyCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|exists:coupons,coupon_code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ],
            ], 200);
        }

        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'message' => 'Your cart is empty.',
                ],
            ], 200);
        }

        $coupon = Coupon::where('coupon_code', $request->coupon_code)->where('is_active', 1)->first();

        if (!$coupon) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'message' => 'Invalid or inactive coupon.',
                ],
            ], 200);
        }

        // Check if the coupon has already been used for this user
        $userCouponUsage = UserCouponUsage::where('user_id', $user->id)
            ->where('coupon_id', $coupon->id)
            ->whereNotNull('order_id') // If order_id is not null, coupon has been used
            ->first();

        if ($userCouponUsage) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'message' => 'Coupon has already been used.',
                ],
            ], 200);
        }

        // Check if coupon was used and hasn't been linked to an order (i.e., payment not done)
        $userCouponUsage = UserCouponUsage::where('user_id', $user->id)
            ->where('coupon_id', $coupon->id)
            ->whereNull('order_id') // Ensures it's not yet linked to an order
            ->first();

        if ($userCouponUsage) {
            // If coupon has already been applied but payment not done, remove old entry
            $userCouponUsage->delete();
        }

        // Apply coupon to cart
        $discountAmount = $coupon->amount;
        $cartTotal = $cart->cart_total;

        if ($discountAmount > $cartTotal) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'message' => 'Discount cannot exceed cart total.',
                ],
            ], 200);
        }

        // Store old cart total before applying discount
        $oldCartTotal = $cart->cart_total;

        $cart->cart_total = $cartTotal - $discountAmount;
        $cart->save();

        // Log coupon usage (does not have order_id yet)
        UserCouponUsage::create([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'used_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'cart_id' => $cart->id,
                'old_cart_total' => number_format($oldCartTotal, 2, '.', ''),
                'new_cart_total' => number_format($cart->cart_total, 2, '.', ''),
                'discount' => $discountAmount,
                'coupon_code' => $request->coupon_code,
                'coupon_name' => $coupon->name,
                'coupon_description' => $coupon->description, // Coupon description (if any)
                'coupon_image' => $coupon->image, // Coupon image (if any)
            ],
            'meta' => [
                'success' => true,
                'message' => 'Coupon applied successfully.',
            ],
        ], 200);
    }


}

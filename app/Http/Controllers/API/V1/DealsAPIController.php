<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Deal;
use App\Models\DealsRedeems;
use App\Models\DealComboProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
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

use Illuminate\Support\Facades\Validator;

use App\Models\Bonus;
use App\Models\Branch;
use App\Models\Timing;
use App\Models\UserCouponUsage;

class DealsAPIController extends Controller
{
    public function getAllDeals(Request $request)
    {
        // Get current date and time
        $currentDate = Carbon::now();

        // Fetch all active deals within the current date and time range
        $deals = Deal::where('is_active', 1)
            ->where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->with(['dealComboProducts'])
            ->get();

        // Format response based on deal type
        $formattedDeals = $deals->map(function ($deal) {
            $baseData = [
                'id' => $deal->id,
                'type' => $deal->type,
                'title' => $deal->title,
                'description' => $deal->description,
                'image' => $deal->image,
                'start_date' => $deal->start_date,
                'end_date' => $deal->end_date,
                'renewal_time' => $deal->renewal_time,
                'is_active' => $deal->is_active,
            ];

            switch ($deal->type) {
                case 'BOGO':
                    return array_merge($baseData, [
                        'buy_product_id' => $deal->buy_product_id,
                        'buy_variant_id' => $deal->buy_variant_id,
                        'buy_quantity' => $deal->buy_quantity,
                        'get_product_id' => $deal->get_product_id,
                        'get_variant_id' => $deal->get_variant_id,
                        'get_quantity' => $deal->get_quantity,
                    ]);

                case 'Combo':
                    return array_merge($baseData, [
                        'combo_products' => $deal->dealComboProducts->map(function ($combo) {
                            return [
                                'product_id' => $combo->product_id,
                                'variant_id' => $combo->variant_id,
                                'quantity' => $combo->quantity,
                            ];
                        }),
                        'combo_discounted_amount' => $deal->combo_discounted_amount,
                    ]);

                case 'Discount':
                    return array_merge($baseData, [
                        'min_cart_amount' => $deal->min_cart_amount,
                        'discount_type' => $deal->discount_type,
                        'discount_amount' => $deal->discount_amount,
                    ]);

                case 'Flat':
                    return array_merge($baseData, [
                        'product_id' => $deal->buy_product_id,
                        'variant_id' => $deal->buy_variant_id,
                        'quantity' => $deal->buy_quantity,
                        'discount_type' => $deal->discount_type,
                        'discount_amount' => $deal->discount_amount,
                    ]);

                default:
                    return $baseData; // Default fallback (should never happen)
            }
        });

        // Return the deals data
        return response()->json([
            'data' => [
                'deals_details' => $formattedDeals, // Wrap formattedDeals in 'deals_details'
            ],
            'meta' => [
                'success' => true,
                'message' => 'Deals fetched successfully',
            ],
        ], 200);
    }

    public function redeemDeal(Request $request)
    {
        $request->validate([
            'deal_id' => 'required|exists:deals,id',
        ]);

        $user = Auth::user();
        $deal = Deal::find($request->deal_id);

        if (!$deal || !$deal->is_active) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'message' => 'Invalid or inactive deal.',
                ],
            ], 400);
        }

        // Check if the user has already redeemed this deal
        $redeemRecord = DealsRedeems::where('deal_id', $deal->id)
            ->where('user_id', $user->id)
            ->where('is_redeemed', 1)
            ->latest('used_at')
            ->first();

        if ($redeemRecord) {
            $renewalTime = (int) $deal->renewal_time;

            if ($renewalTime === 0) {
                return response()->json([
                    'meta' => [
                        'success' => false,
                        'message' => 'You can only redeem this deal once.',
                    ],
                ], 400);
            }

            $lastUsedAt = $redeemRecord->used_at;
            $now = now();
            $daysSinceLastUse = $now->diffInDays($lastUsedAt);

            if ($daysSinceLastUse < $renewalTime) {
                return response()->json([
                    'meta' => [
                        'success' => false,
                        'message' => "You can redeem this deal again after $renewalTime days.",
                    ],
                ], 400);
            }
        }

        // Check if there's an unredeemed record (e.g., payment not completed)
        $unredeemedRecord = DealsRedeems::where('user_id', $user->id)
            ->where('deal_id', $deal->id)
            ->whereNull('order_id')
            ->first();

        if ($unredeemedRecord) {
            // Remove the old unredeemed record
            $unredeemedRecord->delete();
        }

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        // Add the paid item
        $this->addToCart($cart, $deal->buy_product_id, $deal->buy_variant_id, $deal->buy_quantity, false);

        // Add the free item (BOGO deal)
        $this->addToCart($cart, $deal->get_product_id, $deal->get_variant_id, $deal->get_quantity, true);

        // Mark the deal as redeemed
        DealsRedeems::create([
            'deal_id' => $deal->id,
            'user_id' => $user->id,
            'is_redeemed' => 0, // Mark as unredeemed until payment is successful
        ]);

        // Recalculate the cart total
        $cart->recalculateTotal();

        // Fetch cart items with product and variant details
        $cartItems = $cart->items->map(function ($item) {
            return [
                'cart_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => optional($item->product)->name,
                'variant_id' => $item->product_variant_id,
                'variant_name' => optional($item->variant)->unit,
                'quantity' => $item->quantity,
                'total_price' => number_format(optional($item->variant)->price * $item->quantity, 2, '.', ''),
                'is_free' => $item->is_free,
            ];
        });

        // BOGO Deal Details
        $dealDetails = [
            'deal_id' => $deal->id,
            'type' => $deal->type,
            'title' => $deal->title,
            'description' => $deal->description,
            'image' => $deal->image,
            'start_date' => $deal->start_date,
            'end_date' => $deal->end_date,
            'renewal_time' => $deal->renewal_time,
            'is_active' => $deal->is_active,
            'buy_product_id' => $deal->buy_product_id,
            'buy_variant_id' => $deal->buy_variant_id,
            'buy_quantity' => $deal->buy_quantity,
            'get_product_id' => $deal->get_product_id,
            'get_variant_id' => $deal->get_variant_id,
            'get_quantity' => $deal->get_quantity,
        ];

        return response()->json([
            'data' => [
                'cart_id' => $cart->id,
                'cart_items' => $cartItems,
                'cart_total' => $cart->cart_total,
                'saved_amount' => number_format($deal->get_quantity * optional($deal->getVariant)->price, 2, '.', ''),
                'deals_details' => $dealDetails,
            ],
            'meta' => [
                'success' => true,
                'message' => 'Deal redeemed and added to cart successfully.',
            ],
        ], 200);
    }

    private function addToCart($cart, $product_id, $variant_id, $quantity, $is_free = false)
    {
        $variant = ProductVarient::where('id', $variant_id)->where('product_id', $product_id)->first();
        if (!$variant)
            return;

        // Check for existing item with the same variant and free status
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product_id)
            ->where('product_variant_id', $variant_id)
            ->where('is_free', $is_free)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product_id,
                'product_variant_id' => $variant_id,
                'quantity' => $quantity,
                'is_free' => $is_free, // Mark as free if applicable
            ]);
        }
    }




}

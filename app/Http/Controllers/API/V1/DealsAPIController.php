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
            ->with([
                'buyProduct', // Load buy product
                'buyVariant', // Load buy variant
                'getProduct', // Load get product
                'getVariant', // Load get variant
                'dealComboProducts.product', // Load combo products and their related product
                'dealComboProducts.variant', // Load combo products and their related variant
            ])
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
                        'buy_product_name' => $deal->buyProduct->name ?? null, // Use buyProduct relationship
                        'buy_variant_id' => $deal->buy_variant_id,
                        'buy_variant_name' => $deal->buyVariant->unit ?? null, // Use buyVariant relationship
                        'buy_quantity' => $deal->buy_quantity,
                        'get_product_id' => $deal->get_product_id,
                        'get_product_name' => $deal->getProduct->name ?? null, // Use getProduct relationship
                        'get_variant_id' => $deal->get_variant_id,
                        'get_variant_name' => $deal->getVariant->unit ?? null, // Use getVariant relationship
                        'get_quantity' => $deal->get_quantity,
                    ]);

                case 'Combo':
                return array_merge($baseData, [
                    'actualamount' => $deal->actual_amount,
                    'combo_products' => $deal->dealComboProducts->map(function ($combo) {
                        return [
                            'product_id' => $combo->product_id,
                            'product_name' => $combo->product->name ?? null, // Use product relationship
                            'variant_id' => $combo->variant_id,
                            'variant_name' => $combo->variant->unit ?? null, // Use variant relationship
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
                        'product_name' => $deal->buyProduct->name ?? null, // Use buyProduct relationship
                        'variant_id' => $deal->buy_variant_id,
                        'variant_name' => $deal->buyVariant->unit ?? null, // Use buyVariant relationship
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
        $deal = Deal::with('dealComboProducts')->find($request->deal_id);

        if (!$deal || !$deal->is_active) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'message' => 'Invalid or inactive deal.',
                ],
            ], 400);
        }

        // Check if the user has any existing unredeemed deal (regardless of deal_id)
        $existingRedeem = DealsRedeems::where('user_id', $user->id)
            ->where('is_redeemed', 0)
            ->whereNull('order_id')
            ->exists();

        if ($existingRedeem) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'message' => 'You can redeem only one deal at a time.',
                ],
            ], 200);
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
                ], 200);
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
                ], 200);
            }
        }

        // Check if a coupon is already applied
        $activeCoupon = UserCouponUsage::where('user_id', $user->id)
            ->whereNull('order_id')
            ->exists();

        if ($activeCoupon) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'message' => 'A coupon is already applied. Deals cannot be applied with coupons.',
                ],
            ], 200);
        }

        // Check for an unredeemed record (e.g., pending payment) and remove it if present
        $unredeemedRecord = DealsRedeems::where('user_id', $user->id)
            ->where('deal_id', $deal->id)
            ->whereNull('order_id')
            ->first();

        if ($unredeemedRecord) {
            $unredeemedRecord->delete();
        }

        // Check for any unredeemed records (pending payment) and remove them
        $unredeemedRecords = DealsRedeems::where('user_id', $user->id)
            ->whereNull('order_id')
            ->get();

        foreach ($unredeemedRecords as $record) {
            $record->delete();
        }

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        // Process based on deal type
        if ($deal->type === 'BOGO') {
            $this->addToCart($cart, $deal->buy_product_id, $deal->buy_variant_id, $deal->buy_quantity, false, $deal->id);
            $this->addToCart($cart, $deal->get_product_id, $deal->get_variant_id, $deal->get_quantity, true, $deal->id);
        } elseif ($deal->type === 'Combo') {
            foreach ($deal->dealComboProducts as $combo) {
                $this->addToCart($cart, $combo->product_id, $combo->variant_id, $combo->quantity, false, $deal->id);
            }
        } elseif ($deal->type === 'Discount') {
            if ($cart->cart_total < $deal->min_cart_amount) {
                return response()->json([
                    'meta' => [
                        'success' => false,
                        'message' => 'Cart total must be at least ' . $deal->min_cart_amount . ' to redeem this deal.',
                    ],
                ], 400);
            }

            $savedAmount = $deal->discount_type === 'percentage'
                ? ($cart->cart_total * $deal->discount_amount / 100)
                : $deal->discount_amount;

            DealsRedeems::create([
                'deal_id' => $deal->id,
                'user_id' => $user->id,
                'is_redeemed' => 0,
            ]);

            return response()->json([
                'data' => [
                    'cart_id' => $cart->id,
                    'cart_items' => $cart->items->map(function ($item) {
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
                    }),
                    'original_cart_total' => number_format($cart->cart_total, 2, '.', ''),
                    'saved_amount' => number_format($savedAmount, 2, '.', ''),
                    'final_cart_total' => number_format($cart->cart_total - $savedAmount, 2, '.', ''),
                    'deals_details' => [
                        'id' => $deal->id,
                        'type' => $deal->type,
                        'title' => $deal->title,
                        'description' => $deal->description,
                        'image' => $deal->image,
                        'start_date' => $deal->start_date,
                        'end_date' => $deal->end_date,
                        'renewal_time' => $deal->renewal_time,
                        'is_active' => $deal->is_active,
                        'min_cart_amount' => $deal->min_cart_amount,
                        'discount_type' => $deal->discount_type,
                        'discount_amount' => $deal->discount_amount,
                    ],
                ],
                'meta' => [
                    'success' => true,
                    'message' => 'Discount deal applied successfully.',
                ],
            ], 200);
        } elseif ($deal->type === 'Flat') {
            $product = Product::find($deal->buy_product_id);
            $variant = ProductVarient::find($deal->buy_variant_id);
            if (!$product || !$variant) {
                return response()->json([
                    'meta' => [
                        'success' => false,
                        'message' => 'Product or variant not found.',
                    ],
                ], 400);
            }

            $this->addToCart($cart, $deal->buy_product_id, $deal->buy_variant_id, $deal->buy_quantity, false, $deal->id);

            DealsRedeems::create([
                'deal_id' => $deal->id,
                'user_id' => $user->id,
                'is_redeemed' => 0,
            ]);

            $cart->recalculateTotal();

            $savedAmount = 0;
            if ($deal->discount_type === 'fixed') {
                $savedAmount = $deal->discount_amount * $deal->buy_quantity;
            } else {
                $savedAmount = ($variant->price * $deal->discount_amount / 100) * $deal->buy_quantity;
            }

            $dealDetails = [
                'id' => $deal->id,
                'type' => $deal->type,
                'title' => $deal->title,
                'description' => $deal->description,
                'image' => $deal->image,
                'start_date' => $deal->start_date,
                'end_date' => $deal->end_date,
                'renewal_time' => $deal->renewal_time,
                'is_active' => $deal->is_active,
                'product_id' => $deal->buy_product_id,
                'product_name' => $product->name, // Fetch product name
                'variant_id' => $deal->buy_variant_id,
                'variant_name' => $variant->unit, // Fetch variant name
                'quantity' => $deal->buy_quantity,
                'discount_type' => $deal->discount_type,
                'discount_amount' => $deal->discount_amount,
            ];

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

            return response()->json([
                'data' => [
                    'cart_id' => $cart->id,
                    'cart_items' => $cartItems,
                    'cart_total' => number_format($cart->cart_total - $savedAmount, 2, '.', ''),
                    'saved_amount' => number_format($savedAmount, 2, '.', ''),
                    'deals_details' => $dealDetails,
                ],
                'meta' => [
                    'success' => true,
                    'message' => 'Flat deal redeemed and added to cart successfully.',
                ],
            ], 200);
        }

        DealsRedeems::create([
            'deal_id' => $deal->id,
            'user_id' => $user->id,
            'is_redeemed' => 0,
        ]);

        $cart->recalculateTotal();

        if ($deal->type === 'Combo') {
            $comboItemsTotal = 0;
            foreach ($deal->dealComboProducts as $combo) {
                $cartItem = $cart->items()
                    ->where('product_id', $combo->product_id)
                    ->where('product_variant_id', $combo->variant_id)
                    ->where('is_free', false)
                    ->first();
                if ($cartItem && $cartItem->quantity >= $combo->quantity) {
                    $comboItemsTotal += $cartItem->quantity * $cartItem->variant->price;
                }
            }
            $discountDifference = $comboItemsTotal - $deal->combo_discounted_amount;
            $cart->combo_discount = $discountDifference;
            $cart->save();
            $cart->recalculateTotal();
        }

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

        if ($deal->type === 'Combo') {
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
                'actualamount' => $deal->actual_amount,
                'combo_products' => $deal->dealComboProducts->map(function ($combo) {
                    $product = Product::find($combo->product_id);
                    $variant = ProductVarient::find($combo->variant_id);
                    return [
                        'product_id' => $combo->product_id,
                        'product_name' => $product ? $product->name : null, // Fetch product name
                        'variant_id' => $combo->variant_id,
                        'variant_name' => $variant ? $variant->unit : null, // Fetch variant name
                        'quantity' => $combo->quantity,
                    ];
                }),
                'combo_discounted_amount' => $deal->combo_discounted_amount,
            ];

            $originalTotal = 0;
            foreach ($deal->dealComboProducts as $combo) {
                $variant = ProductVarient::find($combo->variant_id);
                if ($variant) {
                    $originalTotal += $variant->price * $combo->quantity;
                }
            }
            $savedAmount = number_format($originalTotal - $deal->combo_discounted_amount, 2, '.', '');
        } else {
            $buyProduct = Product::find($deal->buy_product_id);
            $buyVariant = ProductVarient::find($deal->buy_variant_id);
            $getProduct = Product::find($deal->get_product_id);
            $getVariant = ProductVarient::find($deal->get_variant_id);

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
                'buy_product_name' => $buyProduct ? $buyProduct->name : null, // Fetch buy product name
                'buy_variant_id' => $deal->buy_variant_id,
                'buy_variant_name' => $buyVariant ? $buyVariant->unit : null, // Fetch buy variant name
                'buy_quantity' => $deal->buy_quantity,
                'get_product_id' => $deal->get_product_id,
                'get_product_name' => $getProduct ? $getProduct->name : null, // Fetch get product name
                'get_variant_id' => $deal->get_variant_id,
                'get_variant_name' => $getVariant ? $getVariant->unit : null, // Fetch get variant name
                'get_quantity' => $deal->get_quantity,
            ];
            $freeVariant = ProductVarient::find($deal->get_variant_id);
            if ($freeVariant) {
                $savedAmount = number_format($deal->get_quantity * $freeVariant->price, 2, '.', '');
            } else {
                $savedAmount = '0.00';
            }
        }

        return response()->json([
            'data' => [
                'cart_id' => $cart->id,
                'cart_items' => $cartItems,
                'cart_total' => $cart->cart_total,
                'saved_amount' => $savedAmount,
                'deals_details' => $dealDetails,
            ],
            'meta' => [
                'success' => true,
                'message' => 'Deal redeemed and added to cart successfully.',
            ],
        ], 200);
    }
    private function addToCart($cart, $product_id, $variant_id, $quantity, $is_free = false, $dealid = null)
    {
        $variant = ProductVarient::where('id', $variant_id)->where('product_id', $product_id)->first();
        if (!$variant)
            return;

        // Check for existing item with the same variant, free status, and dealid
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product_id)
            ->where('product_variant_id', $variant_id)
            ->where('is_free', $is_free)
            ->where('dealid', $dealid) // Include dealid in the check
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
                'is_free' => $is_free,
                'dealid' => $dealid, // Set the dealid
            ]);
        }
    }

    public function removeDeal(Request $request)
    {
        $user = Auth::user();

        // Find the active unredeemed deal for the user
        $redeem = DealsRedeems::where('user_id', $user->id)
            ->where('is_redeemed', 0)
            ->whereNull('order_id')
            ->first();

        if (!$redeem) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'message' => 'No active deal to remove.',
                ],
            ], 400);
        }

        $deal = Deal::find($redeem->deal_id);
        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
            // Remove cart items associated with this deal
            CartItem::where('cart_id', $cart->id)
                ->where('dealid', $deal->id)
                ->delete();

            // Reset combo discount if the deal was a Combo
            if ($deal->type === 'Combo') {
                $cart->combo_discount = 0;
                $cart->save();
            }

            // Recalculate the cart total
            $cart->recalculateTotal();
        }

        // Delete the redeem record
        $redeem->delete();

        return response()->json([
            'meta' => [
                'success' => true,
                'message' => 'Deal removed successfully.',
            ],
        ], 200);
    }



}

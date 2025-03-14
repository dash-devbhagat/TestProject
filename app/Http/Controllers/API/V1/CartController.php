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
use App\Models\Branch;
use App\Models\Timing;
use App\Models\UserCouponUsage;
use App\Models\Deal;
use App\Models\DealsRedeems;
use App\Models\DealComboProduct;

class CartController extends Controller
{
    // Add Product to Cart
    public function addToCart(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'required|exists:product_varients,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ],
            ], 200);
        }

        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        // Product and Variant
        $product = Product::find($request->product_id);
        $variant = ProductVarient::find($request->product_variant_id);

        // Ensure Variant Belongs to Product
        if ($variant->product_id !== $product->id) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'The selected product variant does not belong to the specified product.',
                ],
            ], 200);
        }

        // Always create a new cart item for non-deal additions
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => $request->quantity,
            'is_free' => false,
            'dealid' => null, // Explicitly set dealid to null
        ]);

        // Recalculate the cart total
        $cart->recalculateTotal();

        return response()->json([
            'data' => [
                'cart_id' => $cart->id,
                'cart_item_id' => $cartItem->id,
                'items' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_variant_id' => $variant->id,
                    'variant' => $variant->unit,
                    'price' => number_format($variant->price, 2, '.', ''),
                    'quantity' => $cartItem->quantity,
                    'is_free' => $cartItem->is_free,
                ],
                'total_price' => number_format($variant->price * $cartItem->quantity, 2, '.', ''),
                'cart_total' => number_format($cart->cart_total, 2, '.', ''),
            ],
            'meta' => [
                'success' => true,
                'message' => 'Product added to cart successfully.',
            ],
        ], 200);
    }




    // View Cart
    public function viewCart()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Cart is empty.',
                ],
            ], 200);
        }

        $cartItems = $cart->items;

        if ($cartItems->isEmpty()) {
            return response()->json([
                'data' => null,
                'meta' => [
                    'success' => false,
                    'message' => 'Cart is empty.',
                ],
            ], 200);
        }

        $cartTotal = $cart->cart_total;

        $mergedItems = [];
        $dealItems = [];

        foreach ($cartItems as $item) {
            $variant = $item->productVariant;
            $key = $item->product_id . '-' . $item->product_variant_id . '-' . $item->is_free;

            if ($item->dealid) {
                $dealItems[] = [
                    'cart_item_id' => $item->id,
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'product_variant_id' => $variant->id,
                    'variant' => $variant->unit,
                    'price' => number_format($variant->price, 2, '.', ''),
                    'quantity' => $item->quantity,
                    'is_free' => $item->is_free,
                    'total_price' => number_format($variant->price * $item->quantity, 2, '.', ''),
                    'dealid' => $item->dealid,
                ];
            } else {
                if (!isset($mergedItems[$key])) {
                    $mergedItems[$key] = [
                        'cart_item_id' => $item->id,
                        'product_id' => $item->product->id,
                        'product_name' => $item->product->name,
                        'product_variant_id' => $variant->id,
                        'variant' => $variant->unit,
                        'price' => number_format($variant->price, 2, '.', ''),
                        'quantity' => $item->quantity,
                        'is_free' => $item->is_free,
                        'total_price' => number_format($variant->price * $item->quantity, 2, '.', ''),
                    ];
                } else {
                    $mergedItems[$key]['quantity'] += $item->quantity;
                    $mergedItems[$key]['total_price'] = number_format(
                        $mergedItems[$key]['quantity'] * $variant->price,
                        2,
                        '.',
                        ''
                    );
                }
            }
        }

        // Prepare response data and remove empty keys
        $responseData = array_filter([
            'cart_id' => $cart->id,
            'items' => !empty($mergedItems) ? array_values($mergedItems) : null,
            'deal_items' => !empty($dealItems) ? $dealItems : null,
            'cart_total' => $cartTotal,
        ], function ($value) {
            return !is_null($value); // Remove null values
        });

        return response()->json([
            'data' => $responseData,
            'meta' => [
                'success' => true,
                'message' => 'Cart items fetched successfully.',
            ],
        ], 200);
    }






    // Update Cart Item
// Update Cart Item
    public function updateCartItem(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1',
            'product_variant_id' => 'required|exists:product_varients,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ],
            ], 200);
        }

        // Find the cart item
        $cartItem = CartItem::find($request->cart_item_id);
        if (!$cartItem) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Cart item not found.',
                ],
            ], 200);
        }

        // Check if the cart item is part of a deal
        if ($cartItem->dealid) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Deal items cannot be updated.',
                ],
            ], 200);
        }

        // Validate the product variant
        $productVariant = ProductVarient::find($request->product_variant_id);
        if (!$productVariant || $productVariant->product_id != $cartItem->product_id) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Invalid product variant.',
                ],
            ], 200);
        }

        // Update the cart item
        $cartItem->product_variant_id = $request->product_variant_id;
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        // Fetch product and variant details
        $product = $cartItem->product;
        $variant = $cartItem->productVariant;

        // Calculate the total price for the updated item
        $totalPrice = number_format($variant->price * $cartItem->quantity, 2, '.', '');

        // Find the cart and recalculate the total
        $cart = Cart::find($cartItem->cart_id);

        // Recalculate the cart total to apply any deals/discounts
        $cart->recalculateTotal();

        // Fetch the updated cart total
        // $cartTotal = $cart->cart_total;
        $cartTotal = number_format($cart->cart_total, 2, '.', '');


        // Return the response
        return response()->json([
            'data' => [
                'cart_id' => $cart->id,
                'cart_item_id' => $cartItem->id,
                'items' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_variant_id' => $variant->id,
                    'product_variant' => $variant->unit,
                    'quantity' => $cartItem->quantity,
                    'price' => number_format($variant->price, 2, '.', ''),
                    'total_price' => $totalPrice,
                ],
            ],
            'cart_total' => $cartTotal,
            'meta' => [
                'success' => true,
                'message' => 'Cart item updated successfully.',
            ],
        ], 200);
    }


    // Remove Item from Cart
    public function removeFromCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_item_id' => 'required|exists:cart_items,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ],
            ], 200);
        }

        $cartItem = CartItem::find($request->cart_item_id);

        // Get the cart associated with the item
        $cart = $cartItem->cart;

        // Check if the item is part of a deal
        if ($cartItem->dealid) {
            // Find all items in the cart that are part of the same deal
            $dealItems = CartItem::where('cart_id', $cart->id)
                ->where('dealid', $cartItem->dealid)
                ->get();

            // Remove all items associated with the deal
            foreach ($dealItems as $dealItem) {
                $dealItem->delete();
            }
        } else {
            // If the item is not part of a deal, just remove the item
            $cartItem->delete();
        }

        // Recalculate the cart total after removing the item(s)
        $cartTotal = $cart->items->sum(function ($item) {
            $variant = $item->productVariant;
            return $variant->price * $item->quantity;
        });

        // Update the cart total in the database
        $cart->cart_total = number_format($cartTotal, 2, '.', '');
        $cart->save();

        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => true,
                'message' => 'Cart item(s) removed successfully.',
                'cart_total' => $cart->cart_total, // Include the updated cart total
            ],
        ], 200);
    }



    // Clear Cart
    public function clearCart()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
            // Delete all items from the cart
            $cart->items()->delete();

            // Reset cart totals and combo discount
            $cart->cart_total = 0;
            $cart->combo_discount = 0;
            $cart->total_charges = 0; // If needed, reset other totals too
            $cart->grand_total = 0;   // If needed, reset other totals too
            $cart->save();

            // Clear any unredeemed deals associated with the user
            DealsRedeems::where('user_id', $user->id)
                ->where('is_redeemed', 0) // Only clear unredeemed deals
                ->whereNull('order_id')   // Ensure they are not linked to an order
                ->delete();

            // Clear any unused coupons associated with the user
            UserCouponUsage::where('user_id', $user->id)
                ->whereNull('order_id')   // Ensure they are not linked to an order
                ->delete();

            return response()->json([
                'data' => [],
                'meta' => [
                    'success' => true,
                    'message' => 'Cart cleared successfully.',
                ],
            ], 200);
        }

        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => false,
                'message' => 'Cart is empty.',
            ],
        ], 200);
    }



public function checkout(Request $request)
{
    $user = Auth::user();
    $cart = Cart::where('user_id', $user->id)->first();

    if (!$cart || $cart->items->isEmpty()) {
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => false,
                'message' => 'Your cart is empty.',
            ],
        ], 200);
    }

    // Validate the branch ID and timezone
    $validator = Validator::make($request->all(), [
        'branch_id' => 'required|exists:branches,id',
        'timezone' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => false,
                'message' => $validator->errors()->first(),
            ],
        ], 200);
    }

    // Retrieve branch with active timings
    $branch = Branch::with(['timings' => function ($query) {
        $query->where('is_active', 1);
    }])->find($request->branch_id);

    if (!$branch->is_active) {
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => false,
                'message' => 'Selected branch is not available.',
            ],
        ], 200);
    }

    // Check if branch is currently open
     $timezone = $request->timezone ?? 'Asia/Kolkata'; // Default to Indian timezone
    date_default_timezone_set($timezone);
    
    // Get current time in the specified timezone
    $now = \Carbon\Carbon::now($timezone);
    $currentTime = $now->format('H:i:s');
    $currentDay = $now->format('l'); // e.g., "Tuesday"

    $isOpen = $branch->isOpen24x7;

    if (!$isOpen) {
        foreach ($branch->timings as $timing) {
            if ($timing->day === $currentDay) {
                $openingCarbon = \Carbon\Carbon::createFromFormat('H:i:s', $timing->opening_time)->setTimezone($now->timezone);
                    $closingCarbon = \Carbon\Carbon::createFromFormat('H:i:s', $timing->closing_time)->setTimezone($now->timezone);
                    $currentCarbon = \Carbon\Carbon::createFromFormat('H:i:s', $currentTime)->setTimezone($now->timezone);

                if ($currentCarbon->gte($openingCarbon) && $currentCarbon->lte($closingCarbon)) {
                        $isOpen = true;
                        break;
                    }
            }
        }
    }

    if (!$isOpen) {
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => false,
                'message' => 'Selected branch is currently closed.',
            ],
        ], 200);
    }

        // Save selected branch to cart
        $cart->branch_id = $branch->id;
        $cart->save();

        // Use cart_total directly from the database and ensure a proper number format
        $cartTotal = (float) $cart->cart_total;
        $formattedCartTotal = number_format($cartTotal, 2, '.', '');

        /*
         * BONUS DEDUCTION LOGIC:
         */
        $totalBonusDeduction = 0;
        $bonusDeductionsDetails = []; // to keep per-bonus info

        // Assuming the user model has a relationship "payments" for bonus-related records
        foreach ($user->payments as $payment) {
            $bonus = Bonus::find($payment->bonus_id);
            if ($bonus && $bonus->is_active) {
                $availableBonus = (float) $payment->remaining_amount;
                $deduction = $availableBonus * ((float) $bonus->percentage / 100);
                $totalBonusDeduction += $deduction;
                $bonusDeductionsDetails[] = [
                    'bonus_payment_id' => $payment->id,
                    'bonus_type' => $bonus->type,
                    'available_bonus' => number_format($availableBonus, 2, '.', ''),
                    'percentage' => number_format($bonus->percentage, 2, '.', ''),
                    'potential_deduction' => number_format($deduction, 2, '.', ''),
                ];
            }
        }

        if ($totalBonusDeduction > $cartTotal) {
            // Do not apply bonus deduction if bonus > cart total.
            $appliedBonusDeduction = 0;
            $bonusDeductionsDetails = []; // clear details if bonus not applied
        } else {
            $appliedBonusDeduction = $totalBonusDeduction;
        }

        // Subtotal after bonus deduction
        $subtotalAfterBonus = $cartTotal - $appliedBonusDeduction;
        $formattedSubtotalAfterBonus = number_format($subtotalAfterBonus, 2, '.', '');

        // Apply Flat deal discounts
        $flatDeals = DealsRedeems::with([
            'deal' => function ($query) {
                $query->where('type', 'Flat');
            }
        ])
            ->where('user_id', $user->id)
            ->where('is_redeemed', 0)
            ->get();

        $flatDiscount = 0;
        $flatDealDetails = [];

        foreach ($flatDeals as $redeem) {
            $deal = $redeem->deal;
            if (!$deal) {
                continue;
            }

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $deal->buy_product_id)
                ->where('product_variant_id', $deal->buy_variant_id)
                ->where('is_free', false)
                ->first();

            if ($cartItem) {
                $quantity = min($cartItem->quantity, $deal->buy_quantity);
                if ($quantity > 0) {
                    if ($deal->discount_type === 'fixed') {
                        $discount = $deal->discount_amount * $quantity;
                    } else {
                        $pricePerItem = $cartItem->variant->price;
                        $discountPerItem = ($pricePerItem * $deal->discount_amount) / 100;
                        $discount = $discountPerItem * $quantity;
                    }
                    $flatDiscount += $discount;

                    $flatDealDetails[] = [
                        'deal_id' => $deal->id,
                        'deal_type' => $deal->type,
                        'title' => $deal->title,
                        'description' => $deal->description,
                        'image' => $deal->image,
                        'start_date' => $deal->start_date,
                        'end_date' => $deal->end_date,
                        'discount' => number_format($discount, 2, '.', ''),
                        'discount_type' => $deal->discount_type,
                        'product_id' => $deal->buy_product_id,
                        'product_name' => optional($deal->buyProduct)->name, // Fetch product name
                        'variant_id' => $deal->buy_variant_id,
                        'variant_name' => optional($deal->buyVariant)->unit, // Fetch variant name
                        'quantity_applied' => $quantity,
                    ];
                }
            }
        }

        // Apply flat discount to subtotal after bonus
        $subtotalAfterBonus -= $flatDiscount;
        if ($subtotalAfterBonus < 0) {
            $subtotalAfterBonus = 0;
        }

        // Update the final cart total
        $finalCartTotal = $subtotalAfterBonus;

        /*
         * Check if a deal has been redeemed.
         */
        $redeemedDeal = DealsRedeems::where('user_id', $user->id)
            ->whereNull('order_id') // Ensure it's not used in an order
            ->latest()
            ->first();

        $discountValue = 0;
        $dealDetails = [];

        if ($redeemedDeal) {
            $deal = Deal::find($redeemedDeal->deal_id);

            if ($deal) {
                if ($deal->type === 'Discount') {
                    // Check if original cart total meets the deal's minimum requirement
                    if ($cartTotal < $deal->min_cart_amount) {
                        // Delete the redeemed deal as conditions are no longer met
                        $redeemedDeal->delete();
                        $redeemedDeal = null;
                        $deal = null;
                    } else {
                        // Calculate discount based on the original cart total
                        if ($deal->discount_type === 'percentage') {
                            $discountValue = ($cartTotal * $deal->discount_amount) / 100;
                        } else {
                            $discountValue = $deal->discount_amount;
                        }
                        $discountValue = round($discountValue, 2);
                        $dealDetails[] = [
                            'deal_id' => $deal->id,
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
                            'saved_amount' => number_format($discountValue, 2, '.', ''),
                        ];
                    }
                } else {
                    // Handle other deal types (BOGO and Combo)
                    if ($deal->type === 'BOGO') {
                        $variant = ProductVarient::where('id', $deal->get_variant_id)
                            ->where('product_id', $deal->get_product_id)
                            ->first();
                        if ($variant) {
                            $savedAmount = $variant->price * $deal->get_quantity;
                        } else {
                            $savedAmount = 0;
                        }
                        $dealDetails[] = [
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
                            'buy_product_name' => optional($deal->buyProduct)->name, // Fetch buy product name
                            'buy_variant_id' => $deal->buy_variant_id,
                            'buy_variant_name' => optional($deal->buyVariant)->unit, // Fetch buy variant name
                            'buy_quantity' => $deal->buy_quantity,
                            'get_product_id' => $deal->get_product_id,
                            'get_product_name' => optional($deal->getProduct)->name, // Fetch get product name
                            'get_variant_id' => $deal->get_variant_id,
                            'get_variant_name' => optional($deal->getVariant)->unit, // Fetch get variant name
                            'get_quantity' => $deal->get_quantity,
                            'saved_amount' => number_format($savedAmount, 2, '.', ''),
                        ];
                    } elseif ($deal->type === 'Combo') {
                        $originalTotal = 0;
                        foreach ($deal->dealComboProducts as $combo) {
                            $variant = ProductVarient::find($combo->variant_id);
                            if ($variant) {
                                $originalTotal += $variant->price * $combo->quantity;
                            }
                        }
                        $savedAmount = number_format($originalTotal - $deal->combo_discounted_amount, 2, '.', '');
                        $dealDetails[] = [
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
                            'saved_amount' => $savedAmount,
                        ];
                    }
                }
            }
        }

        // Merge flat deal details into the main deal details array
        $dealDetails = array_merge($dealDetails, $flatDealDetails);

        // Apply discount (if any) on top of the bonus-adjusted subtotal.
        $finalCartTotal = $subtotalAfterBonus;
        if ($discountValue > 0) {
            $finalCartTotal = $subtotalAfterBonus - $discountValue;
            if ($finalCartTotal < 0) {
                $finalCartTotal = 0;
            }
        }
        $formattedFinalCartTotal = number_format($finalCartTotal, 2, '.', '');

        $cart->cart_total = $formattedFinalCartTotal; // Store the cart total in the database
        $cart->save();

        // -----------------------------
        // Additional charges calculation using the final cart total (after bonus & discount)
        // -----------------------------
        $charges = Charge::where('is_active', 1)->get();
        $additionalCharges = [];
        $totalAdditionalCharges = 0;

        foreach ($charges as $charge) {
            if ($charge->type === 'percentage') {
                $chargeAmount = ($finalCartTotal * $charge->value) / 100;
            } else { // Fixed amount
                $chargeAmount = $charge->value;
            }
            $chargeAmount = round($chargeAmount, 2);
            $additionalCharges[] = [
                'name' => $charge->name,
                'type' => $charge->type,
                'value' => $charge->value,
                'amount' => number_format($chargeAmount, 2, '.', ''),
            ];
            $totalAdditionalCharges += $chargeAmount;
        }

        $grandTotal = $finalCartTotal + $totalAdditionalCharges;
        $formattedGrandTotal = number_format($grandTotal, 2, '.', '');

        // Save total_charges and grand_total to the cart table
        $cart->total_charges = $totalAdditionalCharges;
        $cart->grand_total = $formattedGrandTotal;
        $cart->save();

        // Prepare Items for Response
        $items = [];
        $couponDetails = null; // Initialize coupon details as null

        // Check if coupon is applied
        $userCouponUsage = UserCouponUsage::where('user_id', $user->id)
            ->whereNull('order_id') // Ensures coupon hasn't been used in an order
            ->first();

        if ($userCouponUsage) {
            $coupon = Coupon::find($userCouponUsage->coupon_id);
            if ($coupon) {
                $couponDetails = [
                    'coupon_code' => $coupon->coupon_code,
                    'coupon_name' => $coupon->name,
                    'coupon_description' => $coupon->description,
                    'coupon_image' => $coupon->image,
                    'discount' => number_format($coupon->amount, 2, '.', ''),
                ];
            }
        }

        foreach ($cart->items as $cartItem) {
            $product = Product::find($cartItem->product_id);
            $variant = ProductVarient::find($cartItem->product_variant_id);

            // Fetch category and sub-category names
            $category = Category::find($product->category_id);
            $subCategory = SubCategory::find($product->sub_category_id);

            $items[] = [
                'cart_item_id' => $cartItem->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_variant_id' => $variant->id,
                'variant' => $variant->unit,
                'price' => number_format($variant->price, 2, '.', ''),
                'quantity' => $cartItem->quantity,
                'is_free' => $cartItem->is_free,
                'sku' => $product->sku,
                'image' => $product->image,
                'details' => $product->details,
                'category_id' => $product->category_id,
                'category_name' => $category ? $category->name : null,
                'sub_category_id' => $product->sub_category_id,
                'sub_category_name' => $subCategory ? $subCategory->name : null,
                'total_price' => number_format($variant->price * $cartItem->quantity, 2, '.', ''),
            ];
        }

        // Return response including bonus, discount (if applied) and coupon details
        return response()->json([
            'data' => [
                'cart_id' => $cart->id,
                'cart_total' => $formattedCartTotal,
                'bonus_deduction_applied' => number_format($appliedBonusDeduction, 2, '.', ''),
                'bonus_details' => $bonusDeductionsDetails,
                // 'deals_details' => $dealDetails,
                'deals_details' => empty($dealDetails) ? null : $dealDetails,
                'coupon_details' => empty($couponDetails) ? null : $couponDetails,
                'new_cart_total' => $formattedFinalCartTotal,
                'additional_charges' => $additionalCharges,
                'total_charges' => number_format($totalAdditionalCharges, 2, '.', ''),
                'grand_total' => $formattedGrandTotal,
                'selected_branch' => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'address' => $branch->address,
                    'logo' => $branch->logo,
                ],
            ],
            'items' => $items,
            'meta' => [
                'success' => true,
                'message' => 'Checkout successful',
            ],
        ], 200);
    }
}

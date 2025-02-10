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

        // Check if Product is Already in Cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant->id)
            ->where('is_free', false) // Only merge with paid items
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'quantity' => $request->quantity,
                'is_free' => 0, // Explicitly mark as paid
            ]);
        }

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
                'cart_total' => $cart->cart_total, // Include the updated cart total
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

        // Use the saved cart total from the database
        $cartTotal = $cart->cart_total;

        $items = $cartItems->map(function ($item) {
            $variant = $item->productVariant;
            return [
                'cart_item_id' => $item->id,
                'product_id' => $item->product->id,
                'product_name' => $item->product->name,
                'product_variant_id' => $variant->id,
                'variant' => $variant->unit,
                'price' => number_format($variant->price, 2, '.', ''),  // Fetch the price dynamically from product_varients
                'quantity' => $item->quantity,
                'is_free' => $item->is_free,
                'total_price' => number_format($variant->price * $item->quantity, 2, '.', ''),
            ];
        });

        return response()->json([
            'data' => [
                'cart_id' => $cart->id,
                'items' => $items,
                'cart_total' => $cartTotal, // Fetch cart total directly from the database
            ],
            'meta' => [
                'success' => true,
                'message' => 'Cart items fetched successfully.',
            ],
        ], 200);
    }



    // Update Cart Item
    public function updateCartItem(Request $request)
    {
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

        $cartItem->product_variant_id = $request->product_variant_id;
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        $product = $cartItem->product;
        $variant = $cartItem->productVariant;

        $totalPrice = number_format($variant->price * $cartItem->quantity, 2, '.', '');

        $cart = Cart::find($cartItem->cart_id);
        $cartTotal = $cart->items->sum(function ($item) {
            $variant = $item->productVariant;
            return $variant->price * $item->quantity;  // Fetch the price dynamically from product_varients
        });
        $cartTotal = number_format($cartTotal, 2, '.', '');
        $cart->cart_total = $cartTotal; // Store the cart total in the database
        $cart->save();

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
                    'price' => number_format($variant->price, 2, '.', ''),  // Fetch the price dynamically from product_varients
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

        // Remove the cart item
        $cartItem->delete();

        // Recalculate the cart total after removing the item
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
                'message' => 'Cart item removed successfully.',
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

        // Validate the branch ID
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
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

        $branch = Branch::find($request->branch_id);
        if (!$branch->is_active) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Selected branch is not available.',
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
         *
         * For each bonus payment (i.e. records from the payments table where a bonus is attached),
         * we calculate a potential deduction as:
         *
         *    deduction = remaining_amount * (bonus_percentage / 100)
         *
         * The total bonus deduction is the sum across all active bonus payments.
         * However, if the total bonus deduction exceeds the cart total, then we do not apply any bonus.
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

        /*
         * Check if a deal has been redeemed.
         * If a discount deal is redeemed, we calculate its discount value based on the ORIGINAL cart total
         * (even though the bonus deduction has already been applied) and then subtract it from the bonus-adjusted subtotal.
         * For non-discount deals (BOGO / Combo), we simply pass along their details.
         */
        $redeemedDeal = DealsRedeems::where('user_id', $user->id)->latest()->first();
        $discountValue = 0;
        $dealDetails = null;
        if ($redeemedDeal) {
            $deal = Deal::find($redeemedDeal->deal_id);
            if ($deal) {
                if ($deal->type === 'Discount') {
                    // Calculate discount based on the original cart total
                    if ($deal->discount_type === 'percentage') {
                        $discountValue = ($cartTotal * $deal->discount_amount) / 100;
                    } else {
                        $discountValue = $deal->discount_amount;
                    }
                    $discountValue = round($discountValue, 2);
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
                        'min_cart_amount' => $deal->min_cart_amount,
                        'discount_type' => $deal->discount_type,
                        'discount_amount' => $deal->discount_amount,
                        'saved_amount' => number_format($discountValue, 2, '.', ''),
                    ];
                } else {
                    // For BOGO and Combo deals, use your existing logic.
                    if ($deal->type === 'BOGO') {
                        $variant = ProductVarient::where('id', $deal->get_variant_id)
                            ->where('product_id', $deal->get_product_id)
                            ->first();
                        if ($variant) {
                            $savedAmount = $variant->price * $deal->get_quantity;
                        } else {
                            $savedAmount = 0;
                        }
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
                            'combo_products' => $deal->dealComboProducts->map(function ($combo) {
                                return [
                                    'product_id' => $combo->product_id,
                                    'variant_id' => $combo->variant_id,
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

        // Apply discount (if any) on top of the bonus-adjusted subtotal.
        // The discount is calculated using the original cart total.
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
                'original_cart_total' => $formattedCartTotal,
                'bonus_deduction_applied' => number_format($appliedBonusDeduction, 2, '.', ''),
                'bonus_details' => $bonusDeductionsDetails,
                'deals_details' => $dealDetails,
                'coupon_details' => $couponDetails,
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

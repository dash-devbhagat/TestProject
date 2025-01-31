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
        ]);
    }

    // Calculate the cart total
    $cartTotal = $cart->items->sum(function ($item) {
        $variant = $item->productVariant;
        return $variant->price * $item->quantity;
    });

    // Update cart total in the database
    $cart->cart_total = number_format($cartTotal, 2, '.', '');
    $cart->save();

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
        
        // Set the cart total to 0
        $cart->cart_total = 0;
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


public function checkout()
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

    // Use cart_total directly from the database
    $cartTotal = $cart->cart_total;

    // Format cart total
    $cartTotal = number_format($cartTotal, 2, '.', '');

    // Fetch Additional Charges
    $charges = Charge::where('is_active', 1)->get();

    $additionalCharges = [];
    $totalAdditionalCharges = 0;

    foreach ($charges as $charge) {
        if ($charge->type === 'percentage') {
            $chargeAmount = ($cartTotal * $charge->value) / 100;
        } else { // Rupees
            $chargeAmount = $charge->value;
        }

        $chargeAmount = number_format($chargeAmount, 2, '.', '');
        $additionalCharges[] = [
            'name' => $charge->name,
            'type' => $charge->type,
            'value' => $charge->value,
            'amount' => $chargeAmount,
        ];
        $totalAdditionalCharges += $chargeAmount;
    }

    // Grand Total
    $grandTotal = $cartTotal + $totalAdditionalCharges;
    $grandTotal = number_format($grandTotal, 2, '.', '');

        // Save total_charges and grand_total to the cart table
    $cart->total_charges = $totalAdditionalCharges;
    $cart->grand_total = $grandTotal;
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
            'sku' => $product->sku,
            'image' => $product->image,
            'details' => $product->details,
            'category_id' => $product->category_id,
            'category_name' => $category ? $category->name : null, // Include category name
            'sub_category_id' => $product->sub_category_id,
            'sub_category_name' => $subCategory ? $subCategory->name : null, // Added sub_category_id
            'total_price' => number_format($variant->price * $cartItem->quantity, 2, '.', ''),
        ];
    }

    // Return response without creating an order entry
    return response()->json([
        'data' => [
            'cart_id' => $cart->id,
            'new_cart_total' => $cartTotal,
            'additional_charges' => $additionalCharges,
            'total_charges' => number_format($totalAdditionalCharges, 2, '.', ''), // Added charges total here
            'grand_total' => $grandTotal,
            'coupon_details' => $couponDetails, // Include coupon details if applied
        ],
        'items' => $items,
        'meta' => [
            'success' => true,
            'message' => 'Checkout successful',
        ],
    ], 200);
}
}

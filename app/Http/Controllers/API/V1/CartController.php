<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVarient;
use App\Models\Charge;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

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
        $cartTotal = $cartItems->sum(function ($item) {
            $variant = $item->productVariant;
            return $variant->price * $item->quantity;  // Fetch the price dynamically from product_varients
        });

        $cartTotal = number_format($cartTotal, 2, '.', '');

        $items = $cartItems->map(function ($item) {
            $variant = $item->productVariant;
            return [
                'cart_item_id' => $item->id,
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
                'cart_total' => $cartTotal,
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
        $cartItem->delete();

        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => true,
                'message' => 'Cart item removed successfully.',
            ],
        ], 200);
    }

    // Clear Cart
    public function clearCart()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
            $cart->items()->delete();
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

        // Calculate Cart Total
        $cartTotal = $cart->items->sum(function ($item) {
            $variant = $item->productVariant;
            return $variant->price * $item->quantity;
        });

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

        // Generate Order Number (e.g., OR-2025-000001)
        // $orderNumber = 'OR-' . date('Y') . '-' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT);
        $orderNumber = 'OR-' . date('Y') . '-' . strtoupper(uniqid());


        // Create Order Entry
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'sub_total' => $cartTotal,
            'charges_total' => $totalAdditionalCharges,
            'grand_total' => $grandTotal,
            'address_id' => $user->address_id, // Assuming user's address is used
            'transaction_status' => 'pending', // Assuming pending status for now
            'order_number' => $orderNumber,    // Store the generated order number
        ]);

        // Create Order Items
        foreach ($cart->items as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'cart_id' => $cart->id,  // Adding the cart_id here
                'product_id' => $cartItem->product_id,
                'product_variant_id' => $cartItem->product_variant_id,
                'quantity' => $cartItem->quantity,
            ]);
        }

        // Clear Cart after Checkout
        // $cart->items()->delete();

        return response()->json([
            'data' => [
                'order' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,  // Added order number to the response
                'cart_total' => $cartTotal,
                'additional_charges' => $additionalCharges,
                'charges_total' => number_format($totalAdditionalCharges, 2, '.', ''), // Added charges total here
                'grand_total' => $grandTotal,
                ],
            ],
            'meta' => [
                'success' => true,
                'message' => 'Checkout successful, order placed.',
            ],
        ], 200);
    }
}

    // public function checkout()
    // {
    //     $user = Auth::user();
    //     $cart = Cart::where('user_id', $user->id)->first();

    //     if (!$cart || $cart->items->isEmpty()) {
    //         return response()->json([
    //             'data' => json_decode('{}'),
    //             'meta' => [
    //                 'success' => false,
    //                 'message' => 'Your cart is empty.',
    //             ],
    //         ], 200);
    //     }

    //     // Calculate Cart Total
    //     $cartTotal = $cart->items->sum(function ($item) {
    //         $variant = $item->productVariant;  // Fetch price dynamically from product_varients table
    //         return $variant->price * $item->quantity;
    //     });

    //     // Format cart total
    //     $cartTotal = number_format($cartTotal, 2, '.', '');

    //     // Fetch Additional Charges
    //     $charges = Charge::where('is_active', 1)->get();

    //     $additionalCharges = [];
    //     $totalAdditionalCharges = 0;

    //     foreach ($charges as $charge) {
    //         if ($charge->type === 'percentage') {
    //             $chargeAmount = ($cartTotal * $charge->value) / 100;
    //         } else { // Rupees
    //             $chargeAmount = $charge->value;
    //         }

    //         // Format charge amount
    //         $chargeAmount = number_format($chargeAmount, 2, '.', '');

    //         $additionalCharges[] = [
    //             'name' => $charge->name,
    //             'type' => $charge->type,
    //             'value' => $charge->value,
    //             'amount' => $chargeAmount,
    //         ];

    //         $totalAdditionalCharges += $chargeAmount;
    //     }

    //     // Grand Total
    //     $grandTotal = $cartTotal + $totalAdditionalCharges;

    //     // Format grand total
    //     $grandTotal = number_format($grandTotal, 2, '.', '');

    //     return response()->json([
    //         'data' => [
    //             'cart_total' => $cartTotal,
    //             'additional_charges' => $additionalCharges,
    //             'grand_total' => $grandTotal,
    //         ],
    //         'meta' => [
    //             'success' => true,
    //             'message' => 'Checkout details calculated successfully.',
    //         ],
    //     ], 200);
    // }
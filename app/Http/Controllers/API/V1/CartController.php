<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVarient;
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

        $price = $variant->price;

        // Check if Product is Already in Cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant->id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'quantity' => $request->quantity,
                'price' => $price,
            ]);
        }

        return response()->json([
            'data' => [
                'cart_id' => $cart->id,

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
        $cartTotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $cartTotal = number_format($cartTotal, 2, '.', '');

        $items = $cartItems->map(function ($item) {
            return [
                'cart_item_id' => $item->id,
                'product_name' => $item->product->name,
                'variant' => $item->productVariant->unit,
                'price' => number_format($item->price, 2, '.', ''),
                'quantity' => $item->quantity,
                'total_price' => number_format($item->price * $item->quantity, 2, '.', ''),
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

        $totalPrice = number_format($cartItem->price * $cartItem->quantity, 2, '.', '');

        $cart = Cart::find($cartItem->cart_id);
        $cartTotal = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        $cartTotal = number_format($cartTotal, 2, '.', '');

        return response()->json([
            'data' => [

                'cart_item_id' => $cartItem->id,
                'product_name' => $product->name,
                'product_variant' => $variant->unit,
                'quantity' => $cartItem->quantity,
                'price' => number_format($cartItem->price, 2, '.', ''),
                'total_price' => $totalPrice,
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
}

<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Bonus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Charge;
use App\Models\Product;
use App\Models\ProductVarient;
use App\Models\OrderItem;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\UserCouponUsage;

class TransactionAPIController extends Controller
{

public function processPayment(Request $request)
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

    // Validate payment details
    $validator = Validator::make($request->all(), [
        'cart_id' => 'required|exists:carts,id',
        'payment_mode' => 'required|in:online,cash on delivery',
        'payment_type' => 'nullable|required_if:payment_mode,online|in:credit,debit,upi', // Only required for 'online'
        'payment_status' => 'required|in:success,pending,failed',
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

    // Generate Order from Cart
    $cartTotal = $cart->cart_total;
    $charges = Charge::where('is_active', 1)->get();
    $totalAdditionalCharges = 0;

    foreach ($charges as $charge) {
        $totalAdditionalCharges += $charge->type === 'percentage'
            ? ($cartTotal * $charge->value) / 100
            : $charge->value;
    }

    $grandTotal = $cartTotal + $totalAdditionalCharges;

    $order = Order::create([
        'user_id' => $user->id,
        'status' => 'pending',
        'sub_total' => $cartTotal,
        'charges_total' => $totalAdditionalCharges,
        'grand_total' => $grandTotal,
        'address_id' => $user->address_id,
        'branch_id' => $cart->branch_id,
        'transaction_status' => 'pending',
        'order_number' => 'OR-' . date('Y') . '-' . strtoupper(uniqid()),
    ]);

    foreach ($cart->items as $cartItem) {
        OrderItem::create([
            'order_id' => $order->id,
            'cart_id' => $cart->id,
            'product_id' => $cartItem->product_id,
            'product_variant_id' => $cartItem->product_variant_id,
            'quantity' => $cartItem->quantity,
        ]);
    }

    // Generate a unique transaction number
    $transactionNumber = 'TXN-' . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);

    // Create a transaction
    $transaction = Transaction::create([
        'transaction_number' => $transactionNumber,
        'user_id' => $user->id,
        'order_id' => $order->id,
        'payment_mode' => $request->payment_mode,
        'payment_type' => $request->payment_mode === 'online' ? $request->payment_type : null,
        'payment_status' => $request->payment_status,
    ]);

    // Update Order based on payment status
    $message = '';
    if ($request->payment_status === 'success') 
    {
        $userCouponUsage = UserCouponUsage::where('user_id', $user->id)
                                          ->whereNull('order_id') // Check if it hasn't already been linked
                                          ->latest()
                                          ->first();
        if ($userCouponUsage) {
            $userCouponUsage->order_id = $order->id;
            $userCouponUsage->save();
        }                                  

        $order->status = 'pending';  // COD orders stay 'pending'
        $order->transaction_status = 'success';
        $message = 'Payment was successful , order placed.';
        
        // Update the payments table's remaining amount after successful payment
        foreach ($user->payments as $payment) 
        {
            // Calculate the remaining bonus after payment
            if ($payment->bonus_id) {
                $bonus = Bonus::find($payment->bonus_id);
                if ($bonus && $bonus->is_active) {
                    $remainingBonus = $payment->remaining_amount - ($payment->remaining_amount * $bonus->percentage / 100);
                    $payment->remaining_amount = $remainingBonus;
                    $payment->save();
                }
            }
        }
        
    } elseif ($request->payment_status === 'pending') 
    {
        $order->transaction_status = 'pending';
        $message = 'Payment is pending. Please try again later.';
    } else 
    {
        $order->transaction_status = 'failed';
        $message = 'Payment failed. Please try again.';
    }

    $order->transaction_id = $transaction->id;
    $order->save();

    // Clear the cart
    $cart->items()->delete();
    $cart->cart_total = 0;
    $cart->total_charges = 0;
    $cart->grand_total = 0;
    $cart->branch_id = null;
    $cart->save();

    return response()->json([
        'data' => [
            'transaction_id' => $transaction->id,
            'transaction_number' => $transaction->transaction_number,
            'order_id' => $order->id,
            'payment_status' => $request->payment_status,
        ],
        'meta' => [
            'success' => true,
            'message' => $message,
        ],
    ], 200);
}

public function applyBonus(Request $request)
{
    // Fetch authenticated user
    $user = Auth::user();

    // Retrieve the cart for the user
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

    // Fetch cart total from the carts table
    $cartTotal = $cart->cart_total;

    if ($cartTotal <= 0) {
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => false,
                'message' => 'Cart is empty or total is zero.',
            ],
        ], 200);
    }

    // Initialize variables to store total bonus used and details
    $totalBonusUsed = 0;
    $bonusDetails = [];
    $bonusTypes = [];
    $totalRemainingBonusAmount = 0;  // Initialize remaining bonus amount

    // Loop through user's payments to fetch and apply bonuses
    foreach ($user->payments as $payment) {
        $bonus = Bonus::find($payment->bonus_id);

        if ($bonus && $bonus->is_active) {
            // Calculate bonus usage based on the dynamic percentage
            $bonusUsage = ($payment->remaining_amount * $bonus->percentage) / 100;

            // Update total bonus used
            $totalBonusUsed += $bonusUsage;

            // Deduct bonus usage from payment remaining amount
            $payment->remaining_amount -= $bonusUsage;

            // Group bonuses by type
            if (!isset($bonusTypes[$bonus->type])) {
                $bonusTypes[$bonus->type] = [
                    'total_available' => 0,
                    'total_used' => 0,
                    'remaining_bonus' => 0,
                    'percentage' => $bonus->percentage,
                ];
            }

            $bonusTypes[$bonus->type]['total_available'] += $payment->remaining_amount + $bonusUsage;
            $bonusTypes[$bonus->type]['total_used'] += $bonusUsage;
            $bonusTypes[$bonus->type]['remaining_bonus'] = $bonusTypes[$bonus->type]['total_available'] - $bonusTypes[$bonus->type]['total_used'];

            // Track remaining bonus amount
            $totalRemainingBonusAmount += $payment->remaining_amount;
        }
    }

    // Validate that bonuses were applied
    if ($totalBonusUsed <= 0) {
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => false,
                'message' => 'No bonus available to apply.',
            ],
        ], 200);
    }

    // Check if cart total allows bonus application
    if ($cartTotal > $totalBonusUsed) {
        // Convert grouped bonuses into an array
        foreach ($bonusTypes as $type => $details) {
            $bonusDetails[] = [
                'bonus_type' => $type,
                'percentage' => number_format($details['percentage'], 2, '.', ''),
                'total_available' => number_format($details['total_available'], 2, '.', ''),
                'bonus_used' => number_format($details['total_used'], 2, '.', ''),
                'remaining_bonus_amount' => number_format($details['remaining_bonus'], 2, '.', ''),
            ];
        }

        // Apply bonus to cart total
        $oldCartTotal = $cartTotal;
        $newCartTotal = $cartTotal - $totalBonusUsed;
        $discount = $oldCartTotal - $newCartTotal;

        // Update cart total in the database
        $cart->cart_total = number_format($newCartTotal, 2, '.', '');
        $cart->save();

        return response()->json([
            'data' => [
                'cart_id' => $cart->id,
                'old_cart_total' => number_format($oldCartTotal, 2, '.', ''),
                'new_cart_total' => number_format($newCartTotal, 2, '.', ''),
                'discount' => number_format($discount, 2, '.', ''),
                'total_bonus_used' => number_format($totalBonusUsed, 2, '.', ''),
                'remaining_total_bonus' => number_format($totalRemainingBonusAmount, 2, '.', ''), // Add this line
                'bonus_details' => $bonusDetails,
            ],
            'meta' => [
                'success' => true,
                'message' => 'Bonuses applied successfully.',
            ],
        ], 200);
    } else {
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => false,
                'message' => 'Cart total is less than the total bonus used.',
            ],
        ], 200);
    }
}

}























//     public function viewBonus(Request $request)
// {
//     // Fetch authenticated user
//     $user = Auth::user();

//     // Retrieve the cart for the user
//     $cart = Cart::where('user_id', $user->id)->first();

//     if (!$cart) {
//         return response()->json([
//             'data' => json_decode('{}'),
//             'meta' => [
//                 'success' => false,
//                 'message' => 'Cart is empty.',
//             ],
//         ], 200);
//     }

//     // Fetch cart total from the carts table
//     $cartTotal = $cart->cart_total;

//     if ($cartTotal <= 0) {
//         return response()->json([
//             'data' => json_decode('{}'),
//             'meta' => [
//                 'success' => false,
//                 'message' => 'Cart is empty or total is zero.',
//             ],
//         ], 200);
//     }

//     // Initialize variables to store total bonus used and details
//     $totalBonusUsed = 0;
//     $bonusDetails = [];
//     $bonusTypes = [];
//     $totalRemainingBonusAmount = 0;  // Initialize remaining bonus amount

//     // Loop through user's payments to fetch and apply bonuses
//     foreach ($user->payments as $payment) {
//         $bonus = Bonus::find($payment->bonus_id);

//         if ($bonus && $bonus->is_active) {
//             // Calculate bonus usage based on the dynamic percentage
//             $bonusUsage = ($payment->remaining_amount * $bonus->percentage) / 100;

//             // Update total bonus used
//             $totalBonusUsed += $bonusUsage;

//             // Deduct bonus usage from payment remaining amount
//             $payment->remaining_amount -= $bonusUsage;

//             // Group bonuses by type
//             if (!isset($bonusTypes[$bonus->type])) {
//                 $bonusTypes[$bonus->type] = [
//                     'total_available' => 0,
//                     'total_used' => 0,
//                     'remaining_bonus' => 0,
//                     'percentage' => $bonus->percentage,
//                 ];
//             }

//             $bonusTypes[$bonus->type]['total_available'] += $payment->remaining_amount + $bonusUsage;
//             $bonusTypes[$bonus->type]['total_used'] += $bonusUsage;
//             $bonusTypes[$bonus->type]['remaining_bonus'] = $bonusTypes[$bonus->type]['total_available'] - $bonusTypes[$bonus->type]['total_used'];

//             // Track remaining bonus amount
//             $totalRemainingBonusAmount += $payment->remaining_amount;
//         }
//     }

//     // Validate that bonuses were applied
//     if ($totalBonusUsed <= 0) {
//         return response()->json([
//             'data' => json_decode('{}'),
//             'meta' => [
//                 'success' => false,
//                 'message' => 'No bonus available to apply.',
//             ],
//         ], 200);
//     }

//     // Check if cart total allows bonus application
//     if ($cartTotal > $totalBonusUsed) {
//         // Convert grouped bonuses into an array
//         foreach ($bonusTypes as $type => $details) {
//             $bonusDetails[] = [
//                 'bonus_type' => $type,
//                 'percentage' => number_format($details['percentage'], 2, '.', ''),
//                 'total_available' => number_format($details['total_available'], 2, '.', ''),
//                 'bonus_used' => number_format($details['total_used'], 2, '.', ''),
//                 'remaining_bonus_amount' => number_format($details['remaining_bonus'], 2, '.', ''),
//             ];
//         }

//         // Apply bonus to cart total
//         $oldCartTotal = $cartTotal;
//         $newCartTotal = $cartTotal - $totalBonusUsed;
//         $discount = $oldCartTotal - $newCartTotal;

//         // Update cart total in the database
//         $cart->cart_total = number_format($newCartTotal, 2, '.', '');

//         return response()->json([
//             'data' => [
//                 'cart_id' => $cart->id,
//                 'old_cart_total' => number_format($oldCartTotal, 2, '.', ''),
//                 'new_cart_total' => number_format($newCartTotal, 2, '.', ''),
//                 'discount' => number_format($discount, 2, '.', ''),
//                 'total_bonus_used' => number_format($totalBonusUsed, 2, '.', ''),
//                 'remaining_total_bonus' => number_format($totalRemainingBonusAmount, 2, '.', ''), // Add this line
//                 'bonus_details' => $bonusDetails,
//             ],
//             'meta' => [
//                 'success' => true,
//                 'message' => 'Bonuses details for orders.',
//             ],
//         ], 200);
//     } else {
//         return response()->json([
//             'data' => json_decode('{}'),
//             'meta' => [
//                 'success' => false,
//                 'message' => 'Cart total is less than the total bonus used.',
//             ],
//         ], 200);
//     }
// }


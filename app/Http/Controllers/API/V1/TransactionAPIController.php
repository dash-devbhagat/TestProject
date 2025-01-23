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

class TransactionAPIController extends Controller
{
    public function processPayment(Request $request)
    {
        $user = Auth::user();
            $cart = Cart::where('user_id', $user->id)->first();
        // Validate payment details
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
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

        // Fetch the Order
        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Order not found.',
                ],
            ], 404);
        }

        if ($order->transaction_status === 'success') {
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => false,
                'message' => 'Payment has already been completed for this order.',
            ],
        ], 200);
    }

        // Ensure the order is in "pending" status
        if ($order->status !== 'pending') {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Only pending orders can be paid for.',
                ],
            ], 400);
        }

        // Generate a unique transaction number
        $transactionNumber = 'TXN-' . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);


        // Create a transaction
        $transaction = Transaction::create([
            'transaction_number' => $transactionNumber,
            'user_id' => Auth::user()->id,
            'order_id' => $order->id,
            'payment_mode' => $request->payment_mode,
            'payment_type' => $request->payment_mode === 'online' ? $request->payment_type : null,
            'payment_status' => $request->payment_status,
        ]);

        // Update the Order
        if (
            $request->payment_status === 'success'
        ) {
            $order->status = 'pending';  // COD orders stay 'pending'
            $order->transaction_status = 'success';
            $message = 'Payment was successful.';
        } elseif ($request->payment_status === 'pending') {
            $order->transaction_status = 'pending';
            $message = 'Payment is pending. Please try again later.';
        } else {
            $order->transaction_status = 'failed';
            $message = 'Payment failed. Please try again.';
        }
        $order->transaction_id = $transaction->id;
        $order->save();

                // Clear Cart after Checkout
        $cart->items()->delete();
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

    // Fetch cart items and calculate total cart value
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

    // Calculate the total cart value
    $cartTotal = $cartItems->sum(function ($item) {
        return $item->quantity * $item->productVariant->price;
    });

    // Initialize variables to store total bonus used and details
    $totalBonusUsed = 0;
    $bonusDetails = [];
    $bonusTypes = [];

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
            $payment->save();

            // Group bonuses by type
            if (!isset($bonusTypes[$bonus->type])) {
                $bonusTypes[$bonus->type] = [
                    'total_available' => 0,
                    'total_used' => 0,
                    'percentage' => $bonus->percentage,
                ];
            }

            $bonusTypes[$bonus->type]['total_available'] += $payment->remaining_amount + $bonusUsage;
            $bonusTypes[$bonus->type]['total_used'] += $bonusUsage;
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
            ];
        }

        // Apply bonus to cart total
        $oldCartTotal = $cartTotal;
        $newCartTotal = $cartTotal - $totalBonusUsed;

        // Update user's isbonusused column to true
        $user->isbonusused = true;
        $user->save();

        return response()->json([
            'data' => [
                'cart_id' => $cart->id,
                'old_cart_total' => number_format($oldCartTotal, 2, '.', ''),
                'new_cart_total' => number_format($newCartTotal, 2, '.', ''),
                'total_bonus_used' => number_format($totalBonusUsed, 2, '.', ''),
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

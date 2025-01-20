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

    // Validation to ensure 'order_id' is provided
    $validator = Validator::make($request->all(), [
        'order_id' => 'required|exists:orders,id',
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
        ], 200);
    }

    // Fetch the authenticated user
    $user = Auth::user();

    // Check if the authenticated user is the owner of the order
    if ($order->user_id !== $user->id) {
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => false,
                'message' => 'You are not authorized to apply bonus on this order.',
            ],
        ], 403); // Forbidden status
    }

    // Initialize variables to store total bonus used and details
    $totalBonusUsed = 0;
    $bonusDetails = [];
    $bonusTypes = [];

    // Loop through all payments to fetch bonuses
    foreach ($user->payments as $payment) {
        // Fetch the bonus associated with this payment (check if bonus exists)
        $bonus = Bonus::find($payment->bonus_id);

        if ($bonus && $bonus->is_active) {
            // Calculate the bonus usage based on the dynamic percentage
            $bonusUsage = ($payment->remaining_amount * $bonus->percentage) / 100;

            // Update total bonus used
            $totalBonusUsed += $bonusUsage;

            // Update the remaining amount in the payment table
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

            // Accumulate totals for each bonus type
            $bonusTypes[$bonus->type]['total_available'] += $payment->remaining_amount + $bonusUsage;
            $bonusTypes[$bonus->type]['total_used'] += $bonusUsage;
        }
    }

        // Add validation to ensure total bonus used is greater than 0
    if ($totalBonusUsed <= 0) {
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => false,
                'message' => 'No bonus available to apply.',
            ],
        ], 200);
    }

    // Only apply bonus if order grand total is greater than the total bonus used
    if ($order->grand_total > $totalBonusUsed) {
        // Convert the grouped bonuses into an array
        foreach ($bonusTypes as $type => $details) {
            $bonusDetails[] = [
                'bonus_type' => $type,
                'total_available' => number_format($details['total_available'], 2, '.', ''),
                'percentage' => number_format($details['percentage'], 2, '.', ''),
                'bonus_used' => number_format($details['total_used'], 2, '.', ''),
            ];
        }

        // Update the order grand total by subtracting the total bonus used
        $oldGrandTotal = $order->grand_total;
        $newGrandTotal = $order->grand_total - $totalBonusUsed;
        $order->grand_total = number_format($newGrandTotal, 2, '.', '');
         $order->total_bonus_used = number_format($totalBonusUsed, 2, '.', '');

        // Save the updated order
        $order->save();
    } else {
        // If grand total is less than total bonus used, return an error
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => false,
                'message' => 'Grand total is less than the total bonus used.',
            ],
        ], 200);
    }


    return response()->json([
        'data' => [
            'order_id' => $order->id,
            'old_grand_total' => number_format($oldGrandTotal, 2, '.', ''),
            'new_grand_total' => $order->grand_total,
            'total_bonus_used' => number_format($totalBonusUsed, 2, '.', ''),
            'bonus_details' => $bonusDetails,
        ],
        'meta' => [
            'success' => true,
            'message' => 'Bonuses applied successfully.',
        ],
    ], 200);
}

}

<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Order;

class TransactionAPIController extends Controller
{
    public function processPayment(Request $request)
    {
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

    public function cancelOrder(Request $request)
    {
        // Validate the request to ensure an orderId is provided
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $orderId = $request->input('order_id');

        // Fetch the order by ID
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::user()->id)  // Ensure the user is canceling their own order
            ->first();

        // Check if order exists
        if (!$order) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Order not found or you are not authorized to cancel this order.',
                ],
            ], 200);
        }

        // Check if the order is in a cancellable state (e.g., "pending")
        if ($order->status !== 'pending') {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Only pending orders can be canceled.',
                ],
            ], 200);
        }

        // Check if payment was successful
        $refundMessage = '';
        if ($order->transaction_status === 'success') {
            $refundMessage = 'Since your payment was successful, a refund will be processed.';
        }

        // Update order status to 'cancelled'
        $order->status = 'cancelled';
        $order->save();

        return response()->json([
            'data' => [
                'order_id' => $order->id,
                'order_status' => $order->status,
            ],
            'meta' => [
                'success' => true,
                'message' => 'Order successfully canceled.' . ($refundMessage ? ' ' . $refundMessage : ''),
            ],
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderAPIController extends Controller
{
    // Get All Orders and Details for the Authenticated User
    public function getAllOrders(Request $request)
    {
        $user = Auth::user();

        // Fetch all orders for the authenticated user
        $orders = Order::where('user_id', $user->id)
            ->with(['items.product', 'items.productVariant', 'transactions']) // Include related models
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform data to include only relevant details
        $ordersData = $orders->map(function ($order) {
            return [
                 'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'sub_total' => number_format(
                    $order->sub_total,
                    2,
                    '.',
                    ''
                ),
                'charges_total' => number_format($order->charges_total, 2, '.', ''),
                'grand_total' => number_format($order->grand_total, 2, '.', ''),
                'transaction_status' => $order->transaction_status,
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_id' => $item->product->id,
                        'product_name' => $item->product->name,
                        'variant_id' => $item->productVariant->id,
                        'variant' => $item->productVariant->unit,
                        'quantity' => $item->quantity,
                        'price_per_unit' => number_format($item->productVariant->price, 2, '.', ''),
                        'total_price' => number_format($item->productVariant->price * $item->quantity, 2, '.', ''),
                    ];
                }),
                'transactions' => $order->transactions->map(function ($transaction) {
                    return [
                        'transaction_number' => $transaction->transaction_number,
                        'payment_mode' => $transaction->payment_mode,
                        'payment_status' => $transaction->payment_status,
                    ];
                }),
                'order_at' => $order->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
             'data' => [
            'orders_details' => $ordersData, // Wrap ordersData inside 'orderdata'
        ],
            'meta' => [
                'success' => true,
                'message' => 'Orders retrieved successfully.',
            ],
        ], 200);
    }

    public function getOrderDetails(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id,user_id,' . Auth::id(),
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

        $orderId = $request->order_id;
        $user = Auth::user();

        // Fetch the specific order for the authenticated user
        $order = Order::where('user_id', $user->id)
            ->where('id', $orderId)
            ->with(['items.product', 'items.productVariant', 'transactions']) // Include related models
            ->first();

        if (!$order) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Order not found.',
                ],
            ], 404);
        }

        // Transform order data to include only relevant details
        $orderData = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'sub_total' => number_format($order->sub_total, 2, '.', ''),
            'charges_total' => number_format($order->charges_total, 2, '.', ''),
            'grand_total' => number_format($order->grand_total, 2, '.', ''),
            'transaction_status' => $order->transaction_status,
            'items' => $order->items->map(function ($item) {
                return [
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'variant_id' => $item->productVariant->id,
                    'variant' => $item->productVariant->unit,
                    'quantity' => $item->quantity,
                    'price_per_unit' => number_format($item->productVariant->price, 2, '.', ''),
                    'total_price' => number_format($item->productVariant->price * $item->quantity, 2, '.', ''),
                ];
            }),
            'transactions' => $order->transactions->map(function ($transaction) {
                return [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'payment_mode' => $transaction->payment_mode,
                    'payment_status' => $transaction->payment_status,
                ];
            }),
            'order_date' => $order->created_at->format('Y-m-d H:i:s'),
        ];

        return response()->json([
            'data' => [
            'order_details' => $orderData, // Wrap ordersData inside 'orderdata'
        ],
            'meta' => [
                'success' => true,
                'message' => 'Order details retrieved successfully.',
            ],
        ], 200);
    }

    public function cancelOrder(Request $request)
    {
        // Validation to ensure order_id is provided
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

        $orderId = $request->input('order_id');
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

        // Check if order is in a cancellable state (e.g., "pending")
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

        // Update the order status to 'cancelled'
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

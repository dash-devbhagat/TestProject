<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Charge;
use App\Models\UserCouponUsage;
use App\Models\Coupon;

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

    // Fetch active charges
    $charges = Charge::where('is_active', 1)->get();

    // Fetch user coupon usages
    $userCouponUsages = UserCouponUsage::where('user_id', $user->id)->get();

    // Transform data to include only relevant details
    $ordersData = $orders->map(function ($order) use ($charges, $userCouponUsages) {
        $additionalCharges = [];
        $totalAdditionalCharges = 0;
        $couponDetails = null;

        // Check if a coupon was used for this order
        $couponUsage = $userCouponUsages->firstWhere('order_id', $order->id);
        if ($couponUsage) {
            $coupon = Coupon::find($couponUsage->coupon_id);
            if ($coupon && $coupon->is_active) {
                $couponDetails = [
                    'coupon_name' => $coupon->name,
                    'coupon_code' => $coupon->coupon_code,
                    'description' => $coupon->description,
                    'amount' => number_format($coupon->amount, 2, '.', ''),
                    'image' => $coupon->image,
                ];
            }
        }

        foreach ($charges as $charge) {
            if ($charge->type === 'percentage') {
                $chargeAmount = ($order->sub_total * $charge->value) / 100;
            } else { // Fixed amount
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

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'sub_total' => number_format($order->sub_total, 2, '.', ''),
            'grand_total' => number_format($order->grand_total, 2, '.', ''),
            'transaction_status' => $order->transaction_status,
            'additional_charges' => $additionalCharges,
            'charges_total' => number_format($order->charges_total, 2, '.', ''),
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
            'coupon_details' => $couponDetails, // Add coupon details
            'branch_details' => $order->branch ? [
                'branch_id' => $order->branch->id,
                'branch_name' => $order->branch->name,
                'branch_address' => $order->branch->address,
                'branch_logo' => $order->branch->logo,
                'description' => $order->branch->description,
            ] : null, // Add branch details
        ];
    });

    return response()->json([
        'data' => [
            'orders_details' => $ordersData,
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

    // Fetch the specific order for the authenticated user with branch details
    $order = Order::where('user_id', $user->id)
        ->where('id', $orderId)
        ->with(['items.product', 'items.productVariant', 'transactions', 'branch']) // Include branch relationship
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

    // Fetch Additional Charges
    $charges = Charge::where('is_active', 1)->get();

    $additionalCharges = [];
    $totalAdditionalCharges = 0;

    foreach ($charges as $charge) {
        if ($charge->type === 'percentage') {
            $chargeAmount = ($order->sub_total * $charge->value) / 100;
        } else { // Fixed amount
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

    // Coupon details initialization
    $couponDetails = null;

    // Check if coupon was applied to this order
    $userCouponUsage = UserCouponUsage::where('user_id', $user->id)
                                      ->where('order_id', $order->id)
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

    // Transform order data to include only relevant details
    $orderData = [
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'status' => $order->status,
        'sub_total' => number_format($order->sub_total, 2, '.', ''),
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
        'additional_charges' => $additionalCharges,
        'charges_total' => number_format($order->charges_total, 2, '.', ''),
        'transactions' => $order->transactions->map(function ($transaction) {
            return [
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'payment_mode' => $transaction->payment_mode,
                'payment_status' => $transaction->payment_status,
            ];
        }),
        'coupon_details' => $couponDetails, // Include coupon details if applied
        'order_date' => $order->created_at->format('Y-m-d H:i:s'),
        'branch_details' => [
            'branch_id' => $order->branch->id ?? null,
            'branch_name' => $order->branch->name ?? null,
            'branch_address' => $order->branch->address ?? null,
            'branch_logo' => $order->branch->logo ?? null,
            'branch_description' => $order->branch->description ?? null,
        ],
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

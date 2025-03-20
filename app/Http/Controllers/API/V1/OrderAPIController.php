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
use App\Models\ProductVarient;
use App\Models\Payment;
use App\Models\Bonus;
use App\Models\Deal;
use App\Models\Product;
use App\Models\Branch;
use App\Models\DealsRedeems;

class OrderAPIController extends Controller
{
    // Get All Orders and Details for the Authenticated User
public function getAllOrders(Request $request)
{
    $user = Auth::user();

    // Fetch all orders for the authenticated user
    $orders = Order::where('user_id', $user->id)
        ->with([
            'items.product.category',
            'items.product.subCategory',
            'items.productVariant',
            'transactions',
            'dealsRedeems.deal.buyProduct',
            'dealsRedeems.deal.buyVariant',
            'dealsRedeems.deal.getProduct',
            'dealsRedeems.deal.getVariant',
            'dealsRedeems.deal.dealComboProducts.product',
            'dealsRedeems.deal.dealComboProducts.variant',
        ])
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

        // Fetch bonus details for the order
        $bonusDetails = [];
        $totalBonusDeduction = 0;

        $payments = Payment::where('user_id', $order->user_id)
            ->where('created_at', '<=', $order->created_at)
            ->get();

        foreach ($payments as $payment) {
            $bonus = Bonus::find($payment->bonus_id);
            if ($bonus && $bonus->is_active) {
                $availableBonus = (float) $payment->remaining_amount;
                $deduction = $availableBonus * ((float) $bonus->percentage / 100);
                $totalBonusDeduction += $deduction;
                $bonusDetails[] = [
                    'bonus_payment_id' => $payment->id,
                    'bonus_type' => $bonus->type,
                    'available_bonus' => number_format($availableBonus, 2, '.', ''),
                    'percentage' => number_format($bonus->percentage, 2, '.', ''),
                    'potential_deduction' => number_format($deduction, 2, '.', ''),
                ];
            }
        }

        // Apply bonus deduction to the order subtotal
        $subtotalAfterBonus = $order->sub_total - $totalBonusDeduction;
        if ($subtotalAfterBonus < 0) {
            $subtotalAfterBonus = 0;
        }

        $dealDetails = [];
        foreach ($order->dealsRedeems as $redeem) {
            $deal = $redeem->deal;
            if (!$deal) {
                continue;
            }

            $dealData = [
                'deal_id' => $deal->id,
                'type' => $deal->type,
                'title' => $deal->title,
                'description' => $deal->description,
                'image' => $deal->image,
                'start_date' => $deal->start_date,
                'end_date' => $deal->end_date,
                'renewal_time' => $deal->renewal_time,
                'is_active' => $deal->is_active,
            ];

            switch ($deal->type) {
                case 'Discount':
                    if ($deal->discount_type === 'percentage') {
                        $discountValue = ($order->sub_total * $deal->discount_amount) / 100;
                    } else {
                        $discountValue = $deal->discount_amount;
                    }
                    $dealData['saved_amount'] = number_format($discountValue, 2, '.', '');
                    $dealData['min_cart_amount'] = $deal->min_cart_amount;
                    $dealData['discount_type'] = $deal->discount_type;
                    $dealData['discount_amount'] = $deal->discount_amount;
                    break;

                case 'BOGO':
                    $getVariant = ProductVarient::find($deal->get_variant_id);
                    $savedAmount = $getVariant ? $getVariant->price * $deal->get_quantity : 0;
                    $dealData['saved_amount'] = number_format($savedAmount, 2, '.', '');
                    $dealData['buy_product_id'] = $deal->buy_product_id;
                    $dealData['buy_product_name'] = optional($deal->buyProduct)->name;
                    $dealData['buy_variant_id'] = $deal->buy_variant_id;
                    $dealData['buy_variant_name'] = optional($deal->buyVariant)->unit;
                    $dealData['buy_quantity'] = $deal->buy_quantity;
                    $dealData['get_product_id'] = $deal->get_product_id;
                    $dealData['get_product_name'] = optional($deal->getProduct)->name;
                    $dealData['get_variant_id'] = $deal->get_variant_id;
                    $dealData['get_variant_name'] = optional($deal->getVariant)->unit;
                    $dealData['get_quantity'] = $deal->get_quantity;
                    break;

                case 'Combo':
                    $originalTotal = 0;
                    foreach ($deal->dealComboProducts as $combo) {
                        $variant = ProductVarient::find($combo->variant_id);
                        if ($variant) {
                            $originalTotal += $variant->price * $combo->quantity;
                        }
                    }
                    $savedAmount = $originalTotal - $deal->combo_discounted_amount;
                    $dealData['saved_amount'] = number_format($savedAmount, 2, '.', '');
                    $dealData['actualamount'] = $deal->actual_amount;
                    $dealData['combo_products'] = $deal->dealComboProducts->map(function ($combo) {
                        $product = Product::find($combo->product_id);
                        $variant = ProductVarient::find($combo->variant_id);
                        return [
                            'product_id' => $combo->product_id,
                            'product_name' => $product ? $product->name : null,
                            'variant_id' => $combo->variant_id,
                            'variant_name' => $variant ? $variant->unit : null,
                            'quantity' => $combo->quantity,
                        ];
                    });
                    $dealData['combo_discounted_amount'] = $deal->combo_discounted_amount;
                    break;

                case 'Flat':
                    $buyProductId = $deal->buy_product_id;
                    $buyVariantId = $deal->buy_variant_id;
                    $orderItem = $order->items->where('product_id', $buyProductId)
                        ->where('product_variant_id', $buyVariantId)
                        ->where('is_free', false)
                        ->first();

                    $quantityApplied = 0;
                    if ($orderItem) {
                        $quantity = $orderItem->quantity;
                        $quantityApplied = min($quantity, $deal->buy_quantity);
                    }

                    if ($deal->discount_type === 'fixed') {
                        $discount = $deal->discount_amount * $quantityApplied;
                    } else {
                        $pricePerItem = $orderItem ? $orderItem->productVariant->price : 0;
                        $discountPerItem = ($pricePerItem * $deal->discount_amount) / 100;
                        $discount = $discountPerItem * $quantityApplied;
                    }

                    $dealData['discount'] = number_format($discount, 2, '.', '');
                    $dealData['discount_type'] = $deal->discount_type;
                    $dealData['product_id'] = $deal->buy_product_id;
                    $dealData['product_name'] = optional($deal->buyProduct)->name;
                    $dealData['variant_id'] = $deal->buy_variant_id;
                    $dealData['variant_name'] = optional($deal->buyVariant)->unit;
                    $dealData['quantity_applied'] = $quantityApplied;
                    break;

                default:
                    continue 2;
            }

            $dealDetails[] = $dealData;
        }

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'order_status' => $order->status,
            'order_at' => $order->created_at->format('Y-m-d H:i:s'),
            'transaction_status' => $order->transaction_status,
            'sub_total' => number_format($order->sub_total, 2, '.', ''),
            'additional_charges' => $additionalCharges,
            'charges_total' => number_format($order->charges_total, 2, '.', ''),
            'grand_total' => number_format($order->grand_total, 2, '.', ''),
            'items' => $order->items->map(function ($item) {
                $product = $item->product;
                $category = $product->category;
                $subCategory = $product->subCategory;
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'variant_id' => $item->productVariant->id,
                    'variant' => $item->productVariant->unit,
                    'quantity' => $item->quantity,
                    'price_per_unit' => number_format($item->productVariant->price, 2, '.', ''),
                    'total_price' => number_format($item->productVariant->price * $item->quantity, 2, '.', ''),
                    'sku' => $product->sku,
                    'image' => $product->image,
                    'details' => $product->details,
                    'category_id' => $product->category_id,
                    'category_name' => $category ? $category->name : null,
                    'sub_category_id' => $product->sub_category_id,
                    'sub_category_name' => $subCategory ? $subCategory->name : null,
                    'is_free' => $item->is_free ?? 0,
                ];
            }),
            'transactions' => $order->transactions->map(function ($transaction) {
                return [
                    'transaction_number' => $transaction->transaction_number,
                    'payment_mode' => $transaction->payment_mode,
                    'payment_status' => $transaction->payment_status,
                ];
            }),
            
            'coupon_details' => $couponDetails,
            'deals_details' => empty($dealDetails) ? null : $dealDetails,
            'bonus_details' => $bonusDetails,
            'branch_details' => $order->branch ? [
                'branch_id' => $order->branch->id,
                'branch_name' => $order->branch->name,
                'branch_address' => $order->branch->address,
                'branch_logo' => $order->branch->logo,
                'description' => $order->branch->description,
            ] : null,
        ];
    });

    return response()->json([
        'data' => [
            'orders_details' => $ordersData,
        ],
        'meta' => [
            'accessToken' => $user->auth_token,
            'tokenType' => 'Bearer',
            'success' => true,
            'message' => 'Orders retrieved successfully.',
        ],
    ], 200);
}



// public function getOrderDetails(Request $request)
// {
//     // Validation
//     $validator = Validator::make($request->all(), [
//         'order_id' => 'required|integer|exists:orders,id,user_id,' . Auth::id(),
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'data' => json_decode('{}'),
//             'meta' => [
//                 'success' => false,
//                 'message' => $validator->errors()->first(),
//             ],
//         ], 200);
//     }

//     $orderId = $request->order_id;
//     $user = Auth::user();

//     // Fetch the specific order for the authenticated user with branch details
//     $order = Order::where('user_id', $user->id)
//         ->where('id', $orderId)
//         ->with(['items.product', 'items.productVariant', 'transactions', 'branch']) // Include branch relationship
//         ->first();

//     if (!$order) {
//         return response()->json([
//             'data' => json_decode('{}'),
//             'meta' => [
//                 'success' => false,
//                 'message' => 'Order not found.',
//             ],
//         ], 404);
//     }

//     // Fetch Additional Charges
//     $charges = Charge::where('is_active', 1)->get();

//     $additionalCharges = [];
//     $totalAdditionalCharges = 0;

//     foreach ($charges as $charge) {
//         if ($charge->type === 'percentage') {
//             $chargeAmount = ($order->sub_total * $charge->value) / 100;
//         } else { // Fixed amount
//             $chargeAmount = $charge->value;
//         }

//         $chargeAmount = number_format($chargeAmount, 2, '.', '');
//         $additionalCharges[] = [
//             'name' => $charge->name,
//             'type' => $charge->type,
//             'value' => $charge->value,
//             'amount' => $chargeAmount,
//         ];
//         $totalAdditionalCharges += $chargeAmount;
//     }

//     // Coupon details initialization
//     $couponDetails = null;

//     // Check if coupon was applied to this order
//     $userCouponUsage = UserCouponUsage::where('user_id', $user->id)
//                                       ->where('order_id', $order->id)
//                                       ->first();

//     if ($userCouponUsage) {
//         $coupon = Coupon::find($userCouponUsage->coupon_id);
//         if ($coupon) {
//             $couponDetails = [
//                 'coupon_code' => $coupon->coupon_code,
//                 'coupon_name' => $coupon->name,
//                 'coupon_description' => $coupon->description,
//                 'coupon_image' => $coupon->image,
//                 'discount' => number_format($coupon->amount, 2, '.', ''),
//             ];
//         }
//     }

//     // Transform order data to include only relevant details
//     $orderData = [
//         'order_id' => $order->id,
//         'order_number' => $order->order_number,
//         'status' => $order->status,
//         'sub_total' => number_format($order->sub_total, 2, '.', ''),
//         'grand_total' => number_format($order->grand_total, 2, '.', ''),
//         'transaction_status' => $order->transaction_status,
//         'items' => $order->items->map(function ($item) {
//             return [
//                 'product_id' => $item->product->id,
//                 'product_name' => $item->product->name,
//                 'variant_id' => $item->productVariant->id,
//                 'variant' => $item->productVariant->unit,
//                 'quantity' => $item->quantity,
//                 'price_per_unit' => number_format($item->productVariant->price, 2, '.', ''),
//                 'total_price' => number_format($item->productVariant->price * $item->quantity, 2, '.', ''),
//             ];
//         }),
//         'additional_charges' => $additionalCharges,
//         'charges_total' => number_format($order->charges_total, 2, '.', ''),
//         'transactions' => $order->transactions->map(function ($transaction) {
//             return [
//                 'transaction_id' => $transaction->id,
//                 'transaction_number' => $transaction->transaction_number,
//                 'payment_mode' => $transaction->payment_mode,
//                 'payment_status' => $transaction->payment_status,
//             ];
//         }),
//         'coupon_details' => $couponDetails, // Include coupon details if applied
//         'order_date' => $order->created_at->format('Y-m-d H:i:s'),
//         'branch_details' => [
//             'branch_id' => $order->branch->id ?? null,
//             'branch_name' => $order->branch->name ?? null,
//             'branch_address' => $order->branch->address ?? null,
//             'branch_logo' => $order->branch->logo ?? null,
//             'branch_description' => $order->branch->description ?? null,
//         ],
//     ];

//     return response()->json([
//         'data' => [
//             'order_details' => $orderData, // Wrap ordersData inside 'orderdata'
//         ],
//         'meta' => [
//             'accessToken' => $user->auth_token,
//             'tokenType' => 'Bearer',
//             'success' => true,
//             'message' => 'Order details retrieved successfully.',
//         ],
//     ], 200);
// }

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
                                    'accessToken' => $user->auth_token,
                    'tokenType' => 'Bearer',
                'success' => false,
                'message' => $validator->errors()->first(),
            ],
        ], 200);
    }

    $orderId = $request->order_id;
    $user = Auth::user();

    // Fetch the specific order with all relationships
    $order = Order::where('user_id', $user->id)
        ->where('id', $orderId)
        ->with([
            'items.product.category',
            'items.product.subCategory',
            'items.productVariant',
            'transactions',
            'dealsRedeems.deal.buyProduct',
            'dealsRedeems.deal.buyVariant',
            'dealsRedeems.deal.getProduct',
            'dealsRedeems.deal.getVariant',
            'dealsRedeems.deal.dealComboProducts.product',
            'dealsRedeems.deal.dealComboProducts.variant',
            'branch'
        ])
        ->first();

    if (!$order) {
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                                    'accessToken' => $user->auth_token,
                    'tokenType' => 'Bearer',
                'success' => false,
                'message' => 'Order not found.',
            ],
        ], 404);
    }

    // Fetch active charges
    $charges = Charge::where('is_active', 1)->get();

    // Fetch user coupon usages
    $userCouponUsages = UserCouponUsage::where('user_id', $user->id)->get();

    // Initialize components
    $additionalCharges = [];
    $totalAdditionalCharges = 0;
    $couponDetails = null;
    $bonusDetails = [];
    $totalBonusDeduction = 0;
    $dealDetails = [];

    // Coupon Details
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

    // Additional Charges Calculation
    foreach ($charges as $charge) {
        $chargeAmount = ($charge->type === 'percentage')
            ? ($order->sub_total * $charge->value) / 100
            : $charge->value;

        $chargeAmount = number_format($chargeAmount, 2, '.', '');
        $additionalCharges[] = [
            'name' => $charge->name,
            'type' => $charge->type,
            'value' => $charge->value,
            'amount' => $chargeAmount,
        ];
        $totalAdditionalCharges += $chargeAmount;
    }

    // Bonus Details Calculation
    $payments = Payment::where('user_id', $user->id)
        ->where('created_at', '<=', $order->created_at)
        ->get();

    foreach ($payments as $payment) {
        $bonus = Bonus::find($payment->bonus_id);
        if ($bonus && $bonus->is_active) {
            $availableBonus = (float) $payment->remaining_amount;
            $deduction = $availableBonus * ((float) $bonus->percentage / 100);
            $totalBonusDeduction += $deduction;
            $bonusDetails[] = [
                'bonus_payment_id' => $payment->id,
                'bonus_type' => $bonus->type,
                'available_bonus' => number_format($availableBonus, 2, '.', ''),
                'percentage' => number_format($bonus->percentage, 2, '.', ''),
                'potential_deduction' => number_format($deduction, 2, '.', ''),
            ];
        }
    }

    // Deal Details Processing
    foreach ($order->dealsRedeems as $redeem) {
        $deal = $redeem->deal;
        if (!$deal) continue;

        $dealData = [
            'deal_id' => $deal->id,
            'type' => $deal->type,
            'title' => $deal->title,
            'description' => $deal->description,
            'image' => $deal->image,
            'start_date' => $deal->start_date,
            'end_date' => $deal->end_date,
            'renewal_time' => $deal->renewal_time,
            'is_active' => $deal->is_active,
        ];

        switch ($deal->type) {
            case 'Discount':
                $discountValue = ($deal->discount_type === 'percentage')
                    ? ($order->sub_total * $deal->discount_amount) / 100
                    : $deal->discount_amount;
                $dealData['saved_amount'] = number_format($discountValue, 2, '.', '');
                $dealData['min_cart_amount'] = $deal->min_cart_amount;
                $dealData['discount_type'] = $deal->discount_type;
                $dealData['discount_amount'] = $deal->discount_amount;
                break;

            case 'BOGO':
                $getVariant = ProductVarient::find($deal->get_variant_id);
                $savedAmount = $getVariant ? $getVariant->price * $deal->get_quantity : 0;
                $dealData['saved_amount'] = number_format($savedAmount, 2, '.', '');
                $dealData['buy_product_id'] = $deal->buy_product_id;
                $dealData['buy_product_name'] = optional($deal->buyProduct)->name;
                $dealData['buy_variant_id'] = $deal->buy_variant_id;
                $dealData['buy_variant_name'] = optional($deal->buyVariant)->unit;
                $dealData['buy_quantity'] = $deal->buy_quantity;
                $dealData['get_product_id'] = $deal->get_product_id;
                $dealData['get_product_name'] = optional($deal->getProduct)->name;
                $dealData['get_variant_id'] = $deal->get_variant_id;
                $dealData['get_variant_name'] = optional($deal->getVariant)->unit;
                $dealData['get_quantity'] = $deal->get_quantity;
                break;

            case 'Combo':
                $originalTotal = $deal->dealComboProducts->sum(function ($combo) {
                    $variant = ProductVarient::find($combo->variant_id);
                    return $variant ? $variant->price * $combo->quantity : 0;
                });
                $savedAmount = $originalTotal - $deal->combo_discounted_amount;
                $dealData['saved_amount'] = number_format($savedAmount, 2, '.', '');
                $dealData['combo_products'] = $deal->dealComboProducts->map(function ($combo) {
                    return [
                        'product_id' => $combo->product_id,
                        'product_name' => optional($combo->product)->name,
                        'variant_id' => $combo->variant_id,
                        'variant_name' => optional(ProductVarient::find($combo->variant_id))->unit,
                        'quantity' => $combo->quantity,
                    ];
                });
                $dealData['combo_discounted_amount'] = $deal->combo_discounted_amount;
                break;

            case 'Flat':
                $orderItem = $order->items->firstWhere([
                    'product_id' => $deal->buy_product_id,
                    'product_variant_id' => $deal->buy_variant_id,
                    'is_free' => false
                ]);

                $quantityApplied = $orderItem ? min($orderItem->quantity, $deal->buy_quantity) : 0;
                $pricePerItem = $orderItem ? $orderItem->productVariant->price : 0;

                $discount = ($deal->discount_type === 'fixed')
                    ? $deal->discount_amount * $quantityApplied
                    : ($pricePerItem * $deal->discount_amount / 100) * $quantityApplied;

                $dealData['discount'] = number_format($discount, 2, '.', '');
                $dealData['discount_type'] = $deal->discount_type;
                $dealData['product_id'] = $deal->buy_product_id;
                $dealData['product_name'] = optional($deal->buyProduct)->name;
                $dealData['variant_id'] = $deal->buy_variant_id;
                $dealData['variant_name'] = optional($deal->buyVariant)->unit;
                $dealData['quantity_applied'] = $quantityApplied;
                break;

            default:
                continue 2;
        }

        $dealDetails[] = $dealData;
    }

    // Build the order data structure
    $orderData = [
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'order_status' => $order->status,
        'order_at' => $order->created_at->format('Y-m-d H:i:s'),
        'transaction_status' => $order->transaction_status,
        'sub_total' => number_format($order->sub_total, 2, '.', ''),
        'additional_charges' => $additionalCharges,
        'charges_total' => number_format($order->charges_total, 2, '.', ''),
        'grand_total' => number_format($order->grand_total, 2, '.', ''),
        'items' => $order->items->map(function ($item) {
            $product = $item->product;
            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'variant_id' => $item->productVariant->id,
                'variant' => $item->productVariant->unit,
                'quantity' => $item->quantity,
                'price_per_unit' => number_format($item->productVariant->price, 2, '.', ''),
                'total_price' => number_format($item->productVariant->price * $item->quantity, 2, '.', ''),
                'sku' => $product->sku,
                'image' => $product->image,
                'details' => $product->details,
                'category_id' => $product->category_id,
                'category_name' => optional($product->category)->name,
                'sub_category_id' => $product->sub_category_id,
                'sub_category_name' => optional($product->subCategory)->name,
                'is_free' => $item->is_free ?? 0,
            ];
        }),
        'transactions' => $order->transactions->map(function ($transaction) {
            return [
                'transaction_number' => $transaction->transaction_number,
                'payment_mode' => $transaction->payment_mode,
                'payment_status' => $transaction->payment_status,
            ];
        }),
        'coupon_details' => $couponDetails,
        'deals_details' => empty($dealDetails) ? null : $dealDetails,
        'bonus_details' => $bonusDetails,
        'branch_details' => $order->branch ? [
            'branch_id' => $order->branch->id,
            'branch_name' => $order->branch->name,
            'branch_address' => $order->branch->address,
            'branch_logo' => $order->branch->logo,
            'description' => $order->branch->description,
        ] : null,
    ];

    return response()->json([
        'data' => [
            'order_details' => $orderData,
        ],
        'meta' => [
            'accessToken' => $user->auth_token,
            'tokenType' => 'Bearer',
            'success' => true,
            'message' => 'Order details retrieved successfully.',
        ],
    ], 200);
}

    public function cancelOrder(Request $request)
    {
        $user = Auth::user();
        // Validation to ensure order_id is provided
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                                        'accessToken' => $user->auth_token,
                    'tokenType' => 'Bearer',
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
                                        'accessToken' => $user->auth_token,
                    'tokenType' => 'Bearer',
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
                    'accessToken' => $user->auth_token,
                    'tokenType' => 'Bearer',
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
                'accessToken' => $user->auth_token,
            'tokenType' => 'Bearer',
                'success' => true,
                'message' => 'Order successfully canceled.' . ($refundMessage ? ' ' . $refundMessage : ''),
            ],
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Mail\OrderStatusUpdated;
use App\Models\Charge;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect('dashboard');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with(['user', 'address', 'items', 'transactions'])->findOrFail($id);
        // return $order;
        $charges = Charge::where('is_active', 1)->get();

        return view('admin.order.view_order', compact('order','charges'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }
        $order->status = $request->status;
        // dd($order->status);
        $order->save();

        $additionalMessages = [
            'pending' => 'Your order is currently pending. We are reviewing your order details and will update you shortly.',
            'in progress' => 'Your order is now in progress. Our team is actively working on preparing and shipping your order. Thank you for your patience!',
            'delivered' => 'Great news! Your order has been delivered. We hope you enjoy your purchase. Please let us know if you need any assistance.',
            'cancelled' => 'We regret to inform you that your order has been cancelled. If this was not your intention or if you have any concerns, please contact our support team.',
        ];

        $additionalMessage = $additionalMessages[$order->status] ?? '';

        // Send email to the user
        Mail::to($order->user->email)->queue(new OrderStatusUpdated($order, $additionalMessage));

        return response()->json(['success' => true, 'message' => 'Order status updated successfully']);
    }

    public function cancelledOrders()
    {
        $cancelledOrders = Order::where('status', 'cancelled')->get();

        return view('admin.order.cancelled_orders', compact('cancelledOrders'));
    }

    public function table(Request $request)
    {
        $status = $request->query('status');
        $orders = Order::where('status', $status)->get();

        return view('admin.order.table_format', compact('orders', 'status'));
    }
}

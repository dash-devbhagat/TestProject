<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderPaymentHistoryController extends Controller
{
    public function index(){
        // $payments = Payment::with(['user', 'bonus','paymentParent','paymentChild'])
        // ->orderBy('created_at', 'desc')
        // ->get();
        // return $payments;

        // return view('admin.order_payment_history.order_payment_history', compact('payments'));
        return view('admin.order_payment_history.order_payment_history');
    }
}

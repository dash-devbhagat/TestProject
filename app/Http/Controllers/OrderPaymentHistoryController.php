<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class OrderPaymentHistoryController extends Controller
{
    public function index(){
        $transactions = Transaction::with(['user', 'order'])
        ->orderBy('created_at', 'desc')
        ->get();
        // return $transactions;
        return view('admin.order_payment_history.order_payment_history',compact('transactions'));
    }
}

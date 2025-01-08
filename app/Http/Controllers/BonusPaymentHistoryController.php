<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class BonusPaymentHistoryController extends Controller
{
    public function index(){
        $payments = Payment::with(['user', 'bonus','paymentParent','paymentChild'])
        ->orderBy('created_at', 'desc')
        ->get();
        // return $payments;

        return view('admin.payment_history.payment_history', compact('payments'));
    }
}
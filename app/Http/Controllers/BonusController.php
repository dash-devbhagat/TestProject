<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use App\Models\MobileUser;
use App\Models\Payment;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bonuses = Bonus::where('is_active',true)->get();
        // return $bonus;

        return view('admin.bonus.manage_bonus', compact('bonuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {

    // }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     // dd($request->all());
    
    //     $request->validate([
    //         'type' => 'required|string|max:255',
    //         'amount' => 'required|numeric|min:0',
    //         'percentage' => 'required|numeric|min:0|max:100',
    //     ]);
    
    //     // Save bonus to database
    //     Bonus::create([
    //         'type' => $request->type,
    //         'amount' => $request->amount,
    //         'percentage' => $request->percentage,
    //     ]);
    
    //     return redirect()->route('bonus.index')->with('success', 'Bonus Created Successfully.');

    // }
    
    public function store(Request $request)
{
    // Validate incoming data
    $request->validate([
        'type' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'percentage' => 'required|numeric|min:0|max:100',
    ]);
    
    // Save bonus to database
    $bonus = Bonus::create([
        'type' => $request->type,
        'amount' => $request->amount,
        'percentage' => $request->percentage,
    ]);
    
    // Fetch all active users (ensure they are not deactivated)
    $activeUsers = MobileUser::where('is_active', true)->get();
    
    // Fetch all active bonuses except signup and referral
    $activeBonuses = Bonus::whereNotIn('type', ['signup', 'referral'])
                          ->where('is_active', true)
                          ->get();
    
    // Apply bonuses to all active users
    foreach ($activeUsers as $user) {
        foreach ($activeBonuses as $bonus) {
            // Update or create a payment record for each user
            Payment::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'bonus_id' => $bonus->id,
                ],
                [
                    'amount' => $bonus->amount,          
                    'remaining_amount' => $bonus->amount,  
                    'payment_status' => 'completed',      
                ]
            );
        }
    }

    return redirect()->route('bonus.index')->with('success', 'Bonus Created Successfully.');
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $bonus = Bonus::findOrFail($id);
        return response()->json(['bonus' => $bonus]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         // Validate the request
        $validatedData = $request->validate([
        'type' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'percentage' => 'required|numeric|min:0|max:100',
         ]);

        $bonus = Bonus::findOrFail($id);

        $bonus->type = $validatedData['type'];
        $bonus->amount = $validatedData['amount'];
        $bonus->percentage = $validatedData['percentage'];

        $bonus->save();

        session()->flash('success', 'Bonus Updated Successfully.');

        return response()->json(['success' => true, 'bonus' => $bonus]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bonus = Bonus::findOrFail($id);

        $bonus->delete();
    
        return redirect()->route('bonus.index')->with('success', 'Bonus Deleted Successfully.');
    }

    // public function toggleStatus($id)
    // {
    //     // dd($id);
    //     $bonus = Bonus::findOrFail($id);
        
    //     $bonus->is_active = !$bonus->is_active;
    //     $bonus->save();

    //     return response()->json([
    //         'success' => true,
    //         'status' => $bonus->is_active ? 'activated' : 'deactivated',
    //         'message' => $bonus->is_active ? 'Bonus activated successfully.' : 'Bonus deactivated successfully.'
    //     ]);
    // }

    public function toggleStatus($id)
    {
        $bonus = Bonus::findOrFail($id);
        $previousStatus = $bonus->is_active;

        // Toggle the bonus status
        $bonus->is_active = !$bonus->is_active;
        $bonus->save();

        // If the bonus is deactivated and was previously active
        if (!$bonus->is_active && $previousStatus == true) {
            // Fetch all payments with the bonus applied and remove the bonus from them
            $payments = Payment::where('bonus_id', $bonus->id)
                            ->where('remaining_amount', '>', 0) 
                            ->get();
            
            foreach ($payments as $payment) {
                $payment->remaining_amount = 0;
                $payment->amount = 0; 
                $payment->save();
            }
        }

        return response()->json([
            'success' => true,
            'status' => $bonus->is_active ? 'activated' : 'deactivated',
            'message' => $bonus->is_active ? 'Bonus activated successfully.' : 'Bonus deactivated successfully.'
        ]);
    }


    public function bonusHistory(){

        $bonuses = Bonus::where('is_active',false)->get();

        return view('admin.bonus.bonus_history', compact('bonuses'));
    }
}
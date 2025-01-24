<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
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
    public function store(Request $request)
    {
        // dd($request->all());
    
        $request->validate([
            'type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);
    
        // Save bonus to database
        Bonus::create([
            'type' => $request->type,
            'amount' => $request->amount,
            'percentage' => $request->percentage,
        ]);
    
        return redirect()->route('bonus.index')->with('success', 'Bonus Created Successfully.');

        // session()->flash('success', 'Bonus Created Successfully.');
    
        // return response()->json(['success' => true]);
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

    public function toggleStatus($id)
    {
        // dd($id);
        $bonus = Bonus::findOrFail($id);
        
        $bonus->is_active = !$bonus->is_active;
        $bonus->save();

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
<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $charges = Charge::all();
      
        return view('admin.charge.manage_charges', compact('charges'));
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
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric',
        ]);

        Charge::create($request->all());

        return redirect()->route('charge.index')->with('success', 'Charge Created Successfully.');
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
        $charge = Charge::findOrFail($id);
        return response()->json([
            'charge' => $charge
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric',
        ]);

        $charge = Charge::findOrFail($id);

        $charge->name = $validatedData['name'];
        $charge->type = $validatedData['type'];
        $charge->value = $validatedData['value'];

        $charge->save();

        session()->flash('success', 'Charge Updated Successfully.');

        return response()->json(['success' => true, 'charge' => $charge]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $charge = Charge::findOrFail($id);

        $charge->delete();
    
        return redirect()->route('charge.index')->with('success', 'Charge Deleted Successfully.');
    }

    public function toggleStatus($id)
    {
        $charge = Charge::findOrFail($id);
        
        $charge->is_active = !$charge->is_active;
        $charge->save();

        return response()->json([
            'success' => true,
            'status' => $charge->is_active ? 'activated' : 'deactivated',
            'message' => $charge->is_active ? 'Charge activated successfully.' : 'Charge deactivated successfully.'
        ]);
    }
}
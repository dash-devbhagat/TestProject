<?php

namespace App\Http\Controllers;

use App\Models\State;
use Illuminate\Http\Request;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $states = State::all();
        // return $states;

        return view('admin.state.manage_states', compact('states'));
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
        // dd($request->all());

        $request->validate([
            'name' => 'required|string|max:255',
        ]);
    
        // Save bonus to database
        State::create([
            'name' => $request->name,
        ]);
    
        return redirect()->route('state.index')->with('success', 'State Created Successfully.');
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
        $state = State::findOrFail($id);
        return response()->json(['state' => $state]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());

         // Validate the request
         $validatedData = $request->validate([
            'name' => 'required|string|max:255',
             ]);
    
            $state = State::findOrFail($id);
    
            $state->name = $validatedData['name'];
    
            $state->save();
    
            session()->flash('success', 'State Updated Successfully.');
    
            return response()->json(['success' => true, 'state' => $state]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $state = State::findOrFail($id);

        $state->delete();
    
        return redirect()->route('state.index')->with('success', 'State Deleted Successfully.');
    }

    public function toggleStatus($id){
        $state = State::findOrFail($id);
        
        $state->is_active = !$state->is_active;
        $state->save();

        return response()->json([
            'success' => true,
            'status' => $state->is_active ? 'activated' : 'deactivated',
            'message' => $state->is_active ? 'State activated successfully.' : 'State deactivated successfully.'
        ]);
    }
}

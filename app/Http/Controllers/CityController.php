<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cities = City::with('state')->get();
        $states = State::all();

        return view('admin.city.manage_cities', compact('cities','states'));
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
        // Validate input data
        $validated = $request->validate([
            'state_id' => 'required|exists:states,id',
            'city_name' => 'required|string|max:255',
        ]);

        // Create the subcategory
        City::create([
            'state_id' => $validated['state_id'],
            'name' => $validated['city_name'],
        ]);

        return redirect()->route('city.index')->with('success', 'City Created Successfully.');
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
        $city = City::findOrFail($id);
        $states =State::all();
        return response()->json([
            'city' => $city,
            'states' => $states,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());

         // Validate the request
         $validated = $request->validate([
            'state_id' => 'required|exists:states,id',
            'name' => 'required|string|max:255',
             ]);
    
            $city = City::findOrFail($id);
    
            $city->state_id = $validated['state_id'];
            $city->name = $validated['name'];
    
            $city->save();
    
            session()->flash('success', 'City Updated Successfully.');
    
            return response()->json(['success' => true, 'city' => $city]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $city = City::findOrFail($id);

        $city->delete();
    
        return redirect()->route('city.index')->with('success', 'City Deleted Successfully.');
    }

    public function toggleStatus($id){
        $city = City::findOrFail($id);
        
        $city->is_active = !$city->is_active;
        $city->save();

        return response()->json([
            'success' => true,
            'status' => $city->is_active ? 'activated' : 'deactivated',
            'message' => $city->is_active ? 'City activated successfully.' : 'City deactivated successfully.'
        ]);
    }
}

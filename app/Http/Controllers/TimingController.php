<?php

namespace App\Http\Controllers;

use App\Models\Timing;
use Illuminate\Http\Request;

class TimingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'branch_id' => 'required|exists:branches,id',
            'timings' => 'required|array',
            'timings.*.day' => 'required|string',
            'timings.*.opening_time' => 'required|date_format:H:i',
            'timings.*.closing_time' => 'required|date_format:H:i|after:timings.*.opening_time',
        ]);

        foreach ($request->timings as $timing) {
            Timing::create([
                'branch_id' => $request->branch_id,
                'day' => $timing['day'],
                'opening_time' => $timing['opening_time'],
                'closing_time' => $timing['closing_time'],
            ]);
        }

        return redirect()->back()->with('success', 'Timings Created Successfully');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        
        // Validate the request
        $request->validate([
            'day' => 'required|string',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
        ]);

        // Find the timing entry by ID
        $timing = Timing::findOrFail($id);

        // Update the timing details
        $timing->update([
            'day' => $request->day,
            'opening_time' => $request->opening_time,
            'closing_time' => $request->closing_time,
        ]);

        // Redirect back with success message
        return redirect()->back()->with('success', 'Timing updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function toggleStatus($id)
    {
        $timing = Timing::findOrFail($id);
        
        $timing->is_active = !$timing->is_active;
        $timing->save();

        return response()->json([
            'success' => true,
            'status' => $timing->is_active ? 'activated' : 'deactivated',
            'message' => $timing->is_active ? 'Timing activated successfully.' : 'Timing deactivated successfully.'
        ]);
    }
}

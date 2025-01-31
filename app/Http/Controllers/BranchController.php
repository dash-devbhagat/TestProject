<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Timing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::all();
        return view('admin.branch.manage_branches', compact('branches'));
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
            'address' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'longtitude' => 'required|numeric',
            'latitude' => 'required|numeric',
        ]);

        // Check if an image was uploaded
        if ($request->hasFile('logo')) {
            // Store the image in the public storage folder and get the path
            $path = $request->file('logo')->store('images/branches', 'public');
        } else {
            // If no image is uploaded, set a default value (optional)
            $path = null;
        }

        // Manually store the data without one single variable
        $branch = new Branch();
        $branch->name = $request->name;
        $branch->address = $request->address;
        $branch->longtitude = $request->longtitude;
        $branch->latitude = $request->latitude;
        $branch->description = $request->description;
        $branch->logo = $path;

        $branch->save();

        return redirect()->route('branch.index')->with('success', 'Branch Created Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $branch = Branch::with('timings')->findOrFail($id);

        // Get existing timings from the database for the branch
        $existingTimings = $branch->timings->pluck('day')->toArray();
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return view('admin.branch.view_branch', compact('branch', 'existingTimings', 'daysOfWeek'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $branch = Branch::findOrFail($id);
        return response()->json([
            'branch' => $branch
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'longtitude' => 'required|numeric',
            'latitude' => 'required|numeric',
        ]);

        $branch = Branch::findOrFail($id);

        $branch->name = $request->name;
        $branch->address = $request->address;
        $branch->description = $request->description;
        $branch->latitude = $request->latitude;
        $branch->longtitude = $request->longtitude;

        // Handle Image Upload
        if ($request->hasFile('logo')) {
            if ($branch->logo) {
                Storage::disk('public')->delete($branch->logo);
            }

            $path = $request->file('logo')->store('images/branches', 'public');
            $branch->logo = $path;
        }

        $branch->save();

        session()->flash('success', 'Branch Updated Successfully.');

        return response()->json(['success' => true, 'branch' => $branch]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $branch = Branch::find($id);

        if ($branch) {
            Timing::where('branch_id', $branch->id)->delete();

            $branch->delete();

            return redirect()->route('branch.index')->with('success', 'Branch and It\'s timings are deleted successfully!');
        }

        return redirect()->route('branch.index')->with('error', 'Branch not found!');
    }

    public function toggleStatus($id)
    {
        $branch = Branch::findOrFail($id);

        $branch->is_active = !$branch->is_active;
        $branch->save();

        return response()->json([
            'success' => true,
            'status' => $branch->is_active ? 'activated' : 'deactivated',
            'message' => $branch->is_active ? 'Branch activated successfully.' : 'Branch deactivated successfully.'
        ]);
    }
}

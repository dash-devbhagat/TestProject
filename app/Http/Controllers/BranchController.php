<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Timing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::with('manager')->get();
        $users = User::whereNull('branch_id')->where('id', '!=', 1)->get();
        // dd($users);
        return view('admin.branch.manage_branches', compact('branches', 'users'));
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches', 'name')
            ],
            'address' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'manager_id' => [
                'required',
                'exists:users,id',
                Rule::unique('branches', 'manager_id')
            ],
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
        $branch->longitude = $request->longitude;
        $branch->latitude = $request->latitude;
        $branch->description = $request->description;
        $branch->logo = $path;

        $branch->save();

        // Assign manager to the branch
        if ($request->manager_id) {
            $branch->manager_id = $request->manager_id;
            $branch->save();

            // Assign branch to the user
            $user = User::where('id', $request->manager_id)->first();
            $user->branch_id = $branch->id;
            $user->save();
            // User::where('id', $request->manager_id)->update(['branch_id' => $branch->id]);
        }

        return redirect()->route('branch.index')->with('success', 'Branch Created Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $branch = Branch::with(['timings', 'manager'])->findOrFail($id);

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
        $users = User::where(function ($query) use ($branch) {
            $query->whereNull('branch_id')
                ->orWhere('id', $branch->manager_id);
        })
            ->where('id', '!=', 1)
            ->get();

        // dd($branch);
        return response()->json([
            'branch' => $branch,
            'users' => $users
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches', 'name')->ignore($id)
            ],
            'address' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'manager_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('branches', 'manager_id')->ignore($id)
            ],
        ]);

        $branch = Branch::findOrFail($id);

        $branch->name = $request->name;
        $branch->address = $request->address;
        $branch->description = $request->description;
        $branch->latitude = $request->latitude;
        $branch->longitude = $request->longitude;

        // Handle Image Upload
        if ($request->hasFile('logo')) {
            if ($branch->logo) {
                Storage::disk('public')->delete($branch->logo);
            }

            $path = $request->file('logo')->store('images/branches', 'public');
            $branch->logo = $path;
        }

        // Assign manager to the branch
        if ($request->manager_id) {

            // Clear previous manager's branch assignment
            User::where('branch_id', $branch->id)->update(['branch_id' => null]);

            $branch->manager_id = $request->manager_id;

            // Assign branch to the user
            User::where('id', $request->manager_id)->update(['branch_id' => $branch->id]);
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

    public function toggle24x7($id)
    {
        $branch = Branch::findOrFail($id);

        $branch->isOpen24x7 = !$branch->isOpen24x7;
        $branch->save();

        return response()->json([
            'success' => true,
            'status' => $branch->isOpen24x7 ? 'enabled' : 'disabled',
            'message' => $branch->isOpen24x7
                ? 'Branch is now open 24x7.'
                : 'Branch is no longer open 24x7.'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Mail\UserActivatedMail;
use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $users = User::where('role', 'User')->where('isdelete', 0)->orderBy('id', 'desc')->get();
        // return $users;

        return view('admin.user.manage_users', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     return view('admin.view_user');
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);


        // $user = User::create([
        //     'name' => $validated['name'],
        //     'email' => $validated['email'],
        //     'password' => bcrypt($validated['password']),
        // ]);

        // Mail::to($user->email)->queue(new WelcomeUserMail($user, $validated['password']));

        // session()->flash('success', 'User Created Successfully.');

        // Check if the email exists and is marked as deleted
        $user = User::where('email', $validated['email'])->where('isDelete', 1)->first();

        if ($user) {
            // Update the existing user's record
            $user->update([
                'name' => $validated['name'],
                'password' => bcrypt($validated['password']),
                'isDelete' => 0, // Restore the user
            ]);

            // Optional: Send a welcome email if needed
            Mail::to($user->email)->queue(new WelcomeUserMail($user, $validated['password']));

            session()->flash('success', 'Staff Restored and Updated Successfully.');
        } else {

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

            Mail::to($user->email)->queue(new WelcomeUserMail($user, $validated['password']));

            session()->flash('success', 'Staff Created Successfully.');
        }

        return response()->json(['success' => true]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('admin.user.view_user', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $id)
    {
        $user = User::findOrFail($id);
        return response()->json(['user' => $user]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        // dd($request->all());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:10',
        ]);

        $user = User::findOrFail($id);
        $user->update($validated);

        session()->flash('success', 'Staff Updated Successfully.');

        return response()->json(['success' => true, 'user' => $user]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // $user->delete();

        // Using Soft Delete
        $user->isdelete = true;
        $user->save();

        return redirect()->route('user.index')->with('success', 'Staff Deleted Successfully.');
    }

    public function completeprofile(Request $request)
    {
        // Validate the profile fields
        $request->validate([
            'phone' => 'required',
            'storename' => 'required',
            'location' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Ensure the logo is valid
        ]);

        // If validation passes, save the user's profile
        $user = $request->user(); // Retrieve the authenticated user
        $user->phone = $request->phone;
        $user->storename = $request->storename;
        $user->location = $request->location;
        $user->latitude = $request->latitude;
        $user->longitude = $request->longitude;

        // Handle logo upload (if exists)
        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $imageSize = getimagesize($image);

            if ($imageSize[0] > 100 || $imageSize[1] > 100) {
                return back()->withErrors(['logo' => 'The logo must be 100x100 pixels or smaller.']);
            }

            if ($user->logo && Storage::disk('public')->exists($user->logo)) {
                Storage::disk('public')->delete($user->logo);
            }

            // Store the logo
            $logoPath = $image->store('logos', 'public');
            $user->logo = $logoPath;
        }

        // Mark the profile as complete
        $user->isProfile = true;
        $user->save();

        // Redirect to the dashboard
        return redirect()->route('dashboard');
    }


    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        // Toggle the user's active status
        $user->is_active = !$user->is_active;
        $user->save();

        // Send an email if the user is activated
        if ($user->is_active) {
            Mail::to($user->email)->queue(new UserActivatedMail($user));
        }

        return response()->json([
            'success' => true,
            'status' => $user->is_active ? 'activated' : 'deactivated',
            'message' => $user->is_active ? 'Staff activated successfully.' : 'Staff deactivated successfully.'
        ]);
    }

    public function editProfile()
    {
        $user = Auth::user(); // Get the currently authenticated user
        return view('user.edit-profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        // Validate the profile fields
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'phone' => 'required|string|max:15',
            'storename' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user(); // Retrieve the authenticated user
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->storename = $request->storename;
        $user->location = $request->location;
        $user->latitude = $request->latitude;
        $user->longitude = $request->longitude;

        // Handle logo upload (if exists)
        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $imageSize = getimagesize($image);

            if ($imageSize[0] > 100 || $imageSize[1] > 100) {
                return back()->withErrors(['logo' => 'The logo must be 100x100 pixels or smaller.']);
            }

            if ($user->logo && Storage::disk('public')->exists($user->logo)) {
                Storage::disk('public')->delete($user->logo);
            }

            // Store the logo
            $logoPath = $image->store('logos', 'public');
            $user->logo = $logoPath;
        }

        // Mark the profile as complete
        $user->save();

        // Redirect to the dashboard
        return redirect()->route('dashboard');
    }

}
    



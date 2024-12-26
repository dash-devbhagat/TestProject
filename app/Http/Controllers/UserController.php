<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $users = User::where('role','User')->where('isdelete', 0)->get();
        // return $users;

        return view('admin.manage_users', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     return view('admin.create_user');
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        Mail::to($user->email)->queue(new WelcomeUserMail($user, $validated['password']));

        session()->flash('success', 'User Created Successfully.');

        return response()->json(['success' => true]);
    }

    /**
     * Display the specified resource.
     */
    // public function show(User $user)
    // {
    //     return view('admin.show_user', compact('user'));
    // }

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

        session()->flash('success', 'User Updated Successfully.');

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

        return redirect()->route('user.index')->with('success', 'User Deleted Successfully.');
    }
}
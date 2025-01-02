<?php

namespace App\Http\Controllers;

use App\Mail\MobileUserActivatedMail;
use App\Mail\UserActivatedMail;
use App\Models\MobileUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MobileUserController extends Controller
{
    public function index()
    {
        $mobileUsers = MobileUser::all();
        // return $mobileUsers;

        return view('admin.manage_mobileUser', compact('mobileUsers'));
    }

    public function show(string $id)
    {
        // $user = MobileUser::where('id',$id)->first();
        $user = MobileUser::with('payments.bonus')->find($id);
        // return $user;
        return view('admin.view_mobileUser', compact('user'));
    }

    public function toggleStatus($id)
    {
        // dd($id);
        $user = MobileUser::findOrFail($id);

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
            'message' => $user->is_active ? 'User activated successfully.' : 'User deactivated successfully.'
        ]);
    }
}

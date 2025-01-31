<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->get();
        // return $coupons;
        return view('admin.coupon.manage_coupon', compact('coupons'));
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
        // Validate input data
        $validated = $request->validate([
            'coupon_name' => 'required|string|max:255|unique:coupons,name',
            'coupon_amount' => 'required|numeric|min:0',
            'coupon_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'coupon_details' => 'nullable|string',
        ]);

        // Check if an image was uploaded
        if ($request->hasFile('coupon_image')) {
            // Store the image in the public storage folder and get the path
            $path = $request->file('coupon_image')->store('images/coupons', 'public');
        } else {
            // If no image is uploaded, set a default value (optional)
            $path = null;
        }

        // Generate the coupon code based on the coupon name
        $coupon_name = strtoupper($request->coupon_name); // Convert the coupon name to uppercase
        $prefix = substr($coupon_name, 0, 4); // Take the first 4 characters of the coupon name

        // Generate random digits to append to the coupon code
        $random_digits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT); // 4 random digits, padded with zeros

        // Combine the prefix with the random digits
        $coupon_code = $prefix . $random_digits;

        // Ensure the coupon code is unique
        while (Coupon::where('coupon_code', $coupon_code)->exists()) {
            $random_digits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT); // Regenerate random digits if the code exists
            $coupon_code = $prefix . $random_digits;
        }

        // Create the new Coupon
        $coupon = Coupon::create([
            'name' => $request->coupon_name,
            'amount' => $request->coupon_amount,
            'coupon_code' => $coupon_code,
            'description' => $request->coupon_details,
            'image' => $path,
        ]);

        return redirect()->route('coupon.index')->with('success', 'Coupon Created Successfully.');
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
        $coupon = Coupon::findOrFail($id);
        return response()->json([
            'coupon' => $coupon
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'coupon_name' => 'required|string|max:255|unique:coupons,name,' . $coupon->id,  // Exclude the current coupon's name
            'coupon_amount' => 'required|numeric|min:0',
            'coupon_code' => 'required|string|max:255|unique:coupons,coupon_code,' . $coupon->id,  // Exclude the current coupon's coupon_code
            'coupon_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'coupon_details' => 'nullable|string',
        ]);

        $coupon->name = $validated['coupon_name'];
        $coupon->amount = $validated['coupon_amount'];
        $coupon->coupon_code = $validated['coupon_code'];
        $coupon->description = $validated['coupon_details'];

        // Handle Image Upload
        if ($request->hasFile('coupon_image')) {
            if ($coupon->image) {
                Storage::disk('public')->delete($coupon->image);
            }

            $imagePath = $request->file('coupon_image')->store('images/coupons', 'public');
            $coupon->image = $imagePath;
        }

        $coupon->save();

        // return redirect()->route('category.index')->with('success', 'Category Updated Successfully.');
        session()->flash('success', 'Coupon Updated Successfully.');

        return response()->json(['success' => true, 'coupon' => $coupon]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $coupon = Coupon::findOrFail($id);

        if ($coupon->image) {
            Storage::disk('public')->delete($coupon->image);
        }

        $coupon->delete();

        return redirect()->route('coupon.index')->with('success', 'Coupon Deleted Successfully.');
    }

    public function toggleStatus($id)
    {
        $coupon = Coupon::findOrFail($id);

        $coupon->is_active = !$coupon->is_active;
        $coupon->save();

        return response()->json([
            'success' => true,
            'status' => $coupon->is_active ? 'activated' : 'deactivated',
            'message' => $coupon->is_active ? 'Coupon activated successfully.' : 'Coupon deactivated successfully.'
        ]);
    }
}

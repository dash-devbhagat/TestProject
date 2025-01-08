<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of subcategories.
     */
    public function index()
    {
        $subcategories = SubCategory::with('category')->get();
        $categories = Category::all(); 
        return view('admin.sub_category.manage_subcategories', compact('subcategories', 'categories'));
    }

    /**
     * Store a newly created subcategory in storage.
     */
    public function store(Request $request)
    {
        // Validate input data
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_name' => 'required|string|max:255',
            'sub_category_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('sub_category_image')) {
            $imagePath = $request->file('sub_category_image')->store('images/subcategories', 'public');
        }else{
            $imagePath = null;
        }

        // Create the subcategory
        SubCategory::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['sub_category_name'],
            'image' => $imagePath,
        ]);

        return redirect()->route('sub-category.index')->with('success', 'SubCategory Created Successfully.');
    }

    /**
     * Show the form for editing the specified subcategory.
     */
    public function edit($id)
    {
        $subcategory = SubCategory::findOrFail($id);
        $categories = Category::all();
        return response()->json([
            'subcategory' => $subcategory,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified subcategory in storage.
     */
    public function update(Request $request, $id)
    {
        $subcategory = SubCategory::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_name' => 'required|string|max:255',
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $subcategory->category_id = $validated['category_id'];
        $subcategory->name = $validated['sub_category_name'];

        // Handle image update
        if ($request->hasFile('category_image')) {
            if ($subcategory->image) {
                Storage::disk('public')->delete($subcategory->image);
            }
            $subcategory->image = $request->file('category_image')->store('images/subcategories', 'public');
        }

        $subcategory->save();

        // return redirect()->route('sub-category.index')->with('success', 'SubCategory Updated Successfully.');
        session()->flash('success', 'SubCategory Updated Successfully.');

        return response()->json(['success' => true, 'category' => $subcategory]);
    }

    /**
     * Remove the specified subcategory from storage.
     */
    public function destroy($id)
    {
        $subcategory = SubCategory::findOrFail($id);

        if ($subcategory->image) {
            Storage::disk('public')->delete($subcategory->image);
        }

        $subcategory->delete();

        return redirect()->route('sub-category.index')->with('success', 'SubCategory Deleted Successfully.');
    }

    public function toggleStatus($id)
    {
        $subcategory = SubCategory::findOrFail($id);
        
        $subcategory->is_active = !$subcategory->is_active;
        $subcategory->save();


        return response()->json([
            'success' => true,
            'status' => $subcategory->is_active ? 'activated' : 'deactivated',
            'message' => $subcategory->is_active ? 'Sub-Category activated successfully.' : 'Sub-Category deactivated successfully.'
        ]);
    }

    public function fetchSubCategory($id){
        // return SubCategory::where('category_id', $id)->get();
        $subCategories = SubCategory::where('category_id', $id)->where('is_active', 1)->get();
        return $subCategories;
    }
}
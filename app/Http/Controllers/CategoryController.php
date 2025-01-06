<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      
        $categories = Category::with('subCategories')->get();
        // return $categories;
        return view('admin.category.manage_categories', compact('categories'));
    
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
            'category_name' => 'required|string|max:255',
            'category_image' => 'nullable|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Check if an image was uploaded
        if ($request->hasFile('category_image')) {
            // Store the image in the public storage folder and get the path
            $path = $request->file('category_image')->store('images/categories', 'public');
        } else {
            // If no image is uploaded, set a default value (optional)
            $path = null;
        }

        // Create the new category
        Category::create([
            'name' => $request->category_name,
            'image' => $path,
        ]);

        return redirect()->route('category.index')->with('success', 'Category Created Successfully.');
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
        $category = Category::findOrFail($id);
        return response()->json([
            'category' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'category_name' => 'string|max:255',
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $category->name = $validated['category_name'];

        // Handle Image Upload
        if ($request->hasFile('category_image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $imagePath = $request->file('category_image')->store('images/categories', 'public');
            $category->image = $imagePath;
        }

        $category->save();

        // return redirect()->route('category.index')->with('success', 'Category Updated Successfully.');
        session()->flash('success', 'Category Updated Successfully.');

        return response()->json(['success' => true, 'category' => $category]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route('category.index')->with('success', 'Category Deleted Successfully.');
    }

    public function toggleStatus($id)
    {
        $category = Category::findOrFail($id);
        
        // Toggle the user's active status
        $category->is_active = !$category->is_active;
        $category->save();

        return response()->json([
            'success' => true,
            'status' => $category->is_active ? 'activated' : 'deactivated',
            'message' => $category->is_active ? 'Category activated successfully.' : 'Category deactivated successfully.'
        ]);
    }

}
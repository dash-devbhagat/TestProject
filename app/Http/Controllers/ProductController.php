<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVarient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('productVarients')->get();
        // return $products;
        return view('admin.product.manage_products', compact('products'));
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
        // dd($request->all());
          // Validate input data
          $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'product_image' => 'nullable|max:2048',
            'productvarient.*.unit' => 'nullable|string|max:255',
            'productvarient.*.price' => 'nullable|numeric',
        ]);

          // Check if an image was uploaded
          if ($request->hasFile('product_image')) {
            // Store the image in the public storage folder and get the path
            $path = $request->file('product_image')->store('images/products', 'public');
        } else {
            // If no image is uploaded, set a default value (optional)
            $path = null;
        }

        // Generate the SKU: 'PROD-xxxx'
        // We get the next product count to generate a unique sequential number
        $productCount = Product::count() + 1;  // Increment the count for the SKU

        // Format the SKU as 'PROD-XXXX' with zero-padding to 4 digits
        $sku = 'PRODUCT-' . str_pad($productCount, 4, '0', STR_PAD_LEFT);

        // Create the new category
        $product = Product::create([
            'name' => $request->product_name,
            'image' => $path,
            'sku' => $sku,
        ]);

        // Storing product variants
        if ($request->productvarient) {
            foreach ($request->productvarient as $variant) {
                ProductVarient::create([
                    'product_id' => $product->id,
                    'unit' => $variant['unit'],
                    'price' => $variant['price'],
                ]);
            }
        }

        // return redirect()->route('product.index')->with('success', 'Product Created Successfully.');
        session()->flash('success', 'Product Created Successfully.');

        return response()->json(['success' => true]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::with('productVarients')->find($id);
        // return $product;
        return response()->json([
            'product' => $product,
            // 'productVariants' => $product->productVariants,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());

        // print_r($request->name);exit;
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'product_name' => 'string|max:255',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'product_varients.*.unit' => 'string|max:255',  
            'product_varients.*.price' => 'numeric',
        ]);

        $product->name = $request->name;

        // Handle Image Upload
        if ($request->hasFile('product_image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $imagePath = $request->file('product_image')->store('images/products', 'public');
            $product->image = $imagePath;
        }

        $product->save();

          // Update or create product variants
            if ($request->has('product_varients')) {
                // Clear existing variants for the product
                ProductVarient::where('product_id', $product->id)->delete();

                // Create new product variants
                foreach ($request->product_varients as $variant) {
                    ProductVarient::create([
                        'product_id' => $product->id,
                        'unit' => $variant['unit'],
                        'price' => $variant['price'],
                    ]);
                }
            }

        return redirect()->route('product.index')->with('success', 'Product Updated Successfully.');
        // session()->flash('success', 'Product Updated Successfully.');

        // return response()->json(['success' => true, 'product' => $product]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Product::where('id',$id)->delete();
        ProductVarient::where('product_id', $id)->delete();

        return redirect()->route('product.index')->with('success', 'Product Deleted Successfully.');
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        
        // Toggle the user's active status
        $product->is_active = !$product->is_active;
        $product->save();

        return response()->json([
            'success' => true,
            'status' => $product->is_active ? 'activated' : 'deactivated',
            'message' => $product->is_active ? 'Product activated successfully.' : 'Product deactivated successfully.'
        ]);
    }
}
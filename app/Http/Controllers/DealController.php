<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Product;
use App\Models\ProductVarient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DealController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $deals = Deal::orderBy('created_at', 'desc')->get();
        $products = Product::all();
        return view('admin.deal.manage_deals', compact('deals', 'products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::all();
        return view('admin.deal.create_deal', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());

        // Validate common fields
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:BOGO,Combo,Discount',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'product_id' => 'nullable|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_varients,id',
        ]);

        // Additional validation based on deal type
        if ($request->type == 'BOGO') {
            $request->validate([
                'min_quantity' => 'nullable|integer|min:1',
                'free_quantity' => 'nullable|integer|min:1',
                'b_free_product_id' => 'nullable|exists:products,id',
                'b_free_product_variant_id' => 'nullable|exists:product_varients,id',
            ]);
        } elseif ($request->type == 'Combo') {
            $request->validate([
                'quantity' => 'nullable|integer|min:1',
                'camount' => 'nullable|numeric|min:0',
                'c_free_product_id' => 'nullable|exists:products,id',
                'c_free_product_variant_id' => 'nullable|exists:product_varients,id',
            ]);
        } elseif ($request->type == 'Discount') {
            $request->validate([
                'damount' => 'nullable|numeric|min:0',
                'percentage' => 'nullable|numeric|min:0|max:100',
            ]);
        }

         // Check if an image was uploaded
         if ($request->hasFile('image')) {
            // Store the image in the public storage folder and get the path
            $path = $request->file('image')->store('images/deals', 'public');
        } else {
            // If no image is uploaded, set a default value (optional)
            $path = null;
        }

        // Logic to store free product and variant id value
        $free_product_id = null;
        $free_product_variant_id = null;
        if ($request->type == 'BOGO') {
            $free_product_id = $request->b_free_product_id;
            $free_product_variant_id = $request->b_free_product_variant_id;
        } elseif ($request->type == 'Combo') {
            $free_product_id = $request->c_free_product_id;
            $free_product_variant_id = $request->c_free_product_variant_id;
        }

        // Logic to store amount value
        $amount = null;
        if ($request->type == 'Combo' && $request->has('camount')) {
            $amount = $request->camount;
        } elseif ($request->type == 'Discount' && $request->has('damount')) {
            $amount = $request->damount;
        }

        // Create the Deal
        $deal = Deal::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'image' => $path,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'product_id' => $request->product_id,
            'product_variant_id' => $request->product_variant_id,
            'min_quantity' => $request->min_quantity ?? null,
            'free_quantity' => $request->free_quantity ?? null,
            'free_product_id' => $free_product_id,
            'free_product_variant_id' => $free_product_variant_id,
            'quantity' => $request->quantity ?? null,
            'amount' => $amount,
            'percentage' => $request->percentage ?? null,
        ]);

        return redirect()->route('deal.index')->with('success', 'Deal Created Successfully!');
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deal = Deal::findOrFail($id);
        
        if ($deal->image) {
            Storage::disk('public')->delete($deal->image);
        }

        $deal->delete();

        return redirect()->route('deal.index')->with('success', 'Deal Deleted Successfully.');
    }

    public function toggleStatus($id)
    {
        $deal = Deal::findOrFail($id);

        $deal->is_active = !$deal->is_active;
        $deal->save();

        return response()->json([
            'success' => true,
            'status' => $deal->is_active ? 'activated' : 'deactivated',
            'message' => $deal->is_active ? 'Deal activated successfully.' : 'Deal deactivated successfully.'
        ]);
    }

    public function getProductVariants($product_id)
    {
        $variants = ProductVarient::where('product_id', $product_id)
            ->select('id', 'unit')
            ->get();
        return response()->json($variants);
    }
}

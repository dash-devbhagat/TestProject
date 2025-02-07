<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealComboProduct;
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
            'type' => 'required|in:BOGO,Combo,Discount,Flat',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'renewal_time' => 'nullable|integer|min:1',
        ]);

        // Additional validation based on deal type
        if ($request->type == 'BOGO') {
            $request->validate([
                'buy_product_id' => 'nullable|exists:products,id',
                'buy_variant_id' => 'nullable|exists:product_varients,id',
                'buy_quantity' => 'nullable|integer|min:1',
                'get_product_id' => 'nullable|exists:products,id',
                'get_variant_id' => 'nullable|exists:product_varients,id',
                'get_quantity' => 'nullable|integer|min:1',
            ]);
        } elseif ($request->type == 'Combo') {
            $request->validate([
                'product_id' => 'nullable|array|min:1',
                'product_id.*' => 'exists:products,id',
                'product_variant_id' => 'nullable|array|min:1',
                'product_variant_id.*' => 'exists:product_varients,id',
                'quantity' => 'nullable|array|min:1',
                'quantity.*' => 'integer|min:1',
                'combo_discounted_amount' => 'nullable|numeric|min:0',
            ]);
        } elseif ($request->type == 'Discount') {
            $request->validate([
                'min_cart_amount' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:fixed,percentage',
                'discount_amount' => 'nullable|numeric|min:0',
            ]);
        } elseif ($request->type == 'Flat') {
            $request->validate([
                'flat_product_id' => 'nullable|exists:products,id',
                'flat_variant_id' => 'nullable|exists:product_varients,id',
                'flat_quantity' => 'nullable|integer|min:1',
                'flat_discount_type' => 'nullable|in:fixed,percentage',
                'flat_discount_amount' => 'nullable|numeric|min:0',
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

        // Create the deal with all necessary fields
        $deal = Deal::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'image' => $path,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'renewal_time' => $request->renewal_time,
            // BOGO & Flat
            'buy_product_id' => $request->type == 'BOGO' ? $request->buy_product_id : $request->flat_product_id,
            'buy_variant_id' => $request->type == 'BOGO' ? $request->buy_variant_id : $request->flat_variant_id,
            'buy_quantity' => $request->type == 'BOGO' ? $request->buy_quantity : $request->flat_quantity,
            'get_product_id' => $request->type == 'BOGO' ? $request->get_product_id : null,
            'get_variant_id' => $request->type == 'BOGO' ? $request->get_variant_id : null,
            'get_quantity' => $request->type == 'BOGO' ? $request->get_quantity : null,
            // Combo
            'combo_discounted_amount' => $request->type == 'Combo' ? $request->combo_discounted_amount : null,
            // Discount & Flat
            'min_cart_amount' => $request->type == 'Discount' ? $request->min_cart_amount : null,
            'discount_type' => $request->type == 'Discount' ? $request->discount_type : $request->flat_discount_type,
            'discount_amount' => $request->type == 'Discount' ? $request->discount_amount : $request->flat_discount_amount,
            // Flat
            // 'buy_product_id' => $request->type == 'Flat' ? $request->flat_product_id : null,
            // 'buy_variant_id' => $request->type == 'Flat' ? $request->flat_variant_id : null,
            // 'buy_quantity' => $request->type == 'Flat' ? $request->flat_quantity : null,
            // 'discount_type' => $request->type == 'Flat' ? $request->flat_discount_type : null,
            // 'discount_amount' => $request->type == 'Flat' ? $request->flat_discount_amount : null,
        ]);

        // If it's a combo deal, store related products in deal_combo_products table
        if ($request->type == 'Combo') {
            foreach ($request->product_id as $index => $productId) {
                DealComboProduct::create([
                    'deal_id' => $deal->id,
                    'product_id' => $productId,
                    'variant_id' => $request->product_variant_id[$index],
                    'quantity' => $request->quantity[$index],
                ]);
            }
        }

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

    public function getProductPrice($variantId)
    {
        $variant = ProductVarient::find($variantId);

        if ($variant) {
            return response()->json(['price' => $variant->price]);
        }

        return response()->json(['price' => 0], 404);
    }
}

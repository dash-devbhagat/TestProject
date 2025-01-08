<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ProductAPIController extends Controller
{
    public function getActiveProducts(Request $request)
    {
        // Validation rules
        $rules = [
            'category_id' => 'sometimes|numeric|exists:categories,id',
            'sub_category_id' => 'sometimes|numeric|exists:sub_categories,id',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ],
            ], 200);
        }

        // Check if sub_category_id is provided without category_id
        if ($request->has('sub_category_id') && !$request->has('category_id')) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'category_id is required when sub_category_id is provided.',
                ],
            ], 200);
        }

        // Start with fetching active products with active category and optional subcategory
        $query = Product::where('is_active', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->where(function ($query) {
                $query->whereHas('subCategory', function ($subQuery) {
                    $subQuery->where('is_active', true);
                })
                    ->orWhereNull('sub_category_id'); // Include products without a subcategory
            })
            ->with(['productVarients:product_id,unit,price'])
            ->select('id', 'name', 'sku', 'image', 'details');

        // Filter by category_id if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter by sub_category_id if provided
        if ($request->has('sub_category_id')) {
            $query->where('sub_category_id', $request->input('sub_category_id'));
        }

        // Execute the query
        $products = $query->get();

        if ($products->isEmpty()) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'success' => false,
                    'message' => 'No products found.',
                ],
            ], 200);
        }

        // Format the response
        $response = $products->map(function ($product) {
            return [
                'name' => $product->name,
                'sku' => $product->sku,
                'image' => $product->image,
                'details' => $product->details,
                'variants' => $product->productVarients->map(function ($variant) {
                    return [
                        'unit' => $variant->unit,
                        'price' => $variant->price,
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $response,
            'meta' => [
                'success' => true,
            ],
        ]);
    }
}

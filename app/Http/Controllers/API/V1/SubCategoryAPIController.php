<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SubCategoryAPIController extends Controller
{
    // Fetch subcategories of only active categories
    public function getSubCategoriesByCategoryId(Request $request)
    {

        // Validate that category_id is provided in the request
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Category ID is required and should be an integer.',
                ],
            ], 200);
        }

        // Check if the category exists and is active
        $category = Category::where('id', $request->category_id)
            ->where('is_active', true)
            ->first();

        // If category is not active or doesn't exist
        if (!$category) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Category not found or inactive.',
                ],
            ], 200);
        }

        // Fetch active subcategories for the active category
        $subcategories = SubCategory::where('category_id', $category->id)
            ->where('is_active', true)
            ->select('id', 'name', 'image')
            ->get();

        // Return the response
        if ($subcategories->isEmpty()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'Subcategories not found for this category.',
                ],
            ], 200);
        }

         $subcategories = $subcategories->map(function ($subcategory) use ($category) {
        $subcategory->category_id = $category->id; // Add the category_id
        return $subcategory;
        });

        return response()->json([
            'data' => (object) ['subcategories' => $subcategories],
            'meta' => [
                'success' => true,
                'message' => 'Subcategories fetched successfully.',
            ],
        ]);
    }
}

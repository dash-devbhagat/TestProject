<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryAPIController extends Controller
{
    // Fetch all categories along with their subcategories
    public function getAllCategories()
    {
        $categories = Category::where('is_active', true)->select('id', 'name', 'image')->get();


        if ($categories->isEmpty()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'No categories found.',
                ],
            ], 200); // Return a 200 status with an appropriate message
        }

        // Format the response as required
        return response()->json([
            'data' => [
                'categories' => $categories,
            ],
            'meta' => [
                'success' => true,
                'message' => 'Categories fetched successfully.',
            ],
        ]);
    }
}

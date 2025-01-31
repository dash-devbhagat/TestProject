<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BranchAPIController extends Controller
{
    public function nearbyBranches(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric|min:1', // Radius in kilometers, default 10
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ],
            ], 200);
        }

        $latitude = $request->latitude;
        $longitude = $request->longitude;
       $radius = $request->radius ?? 2; // Default radius 2 km

        // Calculate distance using Haversine formula
        $branches = Branch::selectRaw('branches.*, 
            (6371 * ACOS(
                COS(RADIANS(?)) * COS(RADIANS(branches.latitude)) *
                COS(RADIANS(branches.longitude) - RADIANS(?)) +
                SIN(RADIANS(?)) * SIN(RADIANS(branches.latitude))
            )) AS distance', [$latitude, $longitude, $latitude])
            ->where('is_active', 1)
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->with('timings')
            ->get();

        // Format the response
        $formattedBranches = $branches->map(function ($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'address' => $branch->address,
                'description' => $branch->description,
                'logo' => $branch->logo ? url(Storage::url($branch->logo)) : null,
                'distance' => round($branch->distance, 2) . ' km',
                'timings' => $branch->timings->map(function ($timing) {
                    return [
                        'day' => $timing->day,
                        'opening_time' => $timing->opening_time,
                        'closing_time' => $timing->closing_time,
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $formattedBranches,
            'meta' => [
                'success' => true,
                'message' => 'Nearby branches retrieved successfully.',
            ],
        ], 200);
    }
}
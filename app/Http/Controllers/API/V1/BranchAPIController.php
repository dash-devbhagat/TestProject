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
        'radius' => 'nullable|numeric|min:1', // Radius in kilometers, default 2
        'timezone' => 'nullable|string', // Allow client to send timezone
    ]);

    if ($validator->fails()) {
        return response()->json([
            'data' => json_decode('{}'),
            'meta' => [
                'success' => false,
                'message' => $validator->errors()->first(),
            ],
        ], 200);
    }

    $latitude = $request->latitude;
    $longitude = $request->longitude;
    $radius = $request->radius ?? 2; // Default radius 2 km
    
    // Set timezone to user's timezone if provided, or use a fixed timezone
    $timezone = $request->timezone ?? 'Asia/Kolkata'; // Default to Indian timezone
    date_default_timezone_set($timezone);
    
    // Get current time in the specified timezone
    $now = \Carbon\Carbon::now($timezone);
    $currentTime = $now->format('H:i:s');
    $currentDay = $now->format('l'); // e.g., "Tuesday"
    
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
        ->with(['timings' => function ($query) {
            $query->where('is_active', 1); // Fetch only active timings
        }])
        ->get();

    // Format the response
    $formattedBranches = $branches->map(function ($branch) use ($currentTime, $currentDay, $now) {
        $isOpen = false;

        // Check if the branch is open 24x7
        if ($branch->isOpen24x7) {
            $isOpen = true;
        } else {
            // If not 24x7, check the current day's timings
            foreach ($branch->timings as $timing) {
                if ($timing->day == $currentDay) {
                    // Parse times for proper comparison
                    $openingCarbon = \Carbon\Carbon::createFromFormat('H:i:s', $timing->opening_time)->setTimezone($now->timezone);
                    $closingCarbon = \Carbon\Carbon::createFromFormat('H:i:s', $timing->closing_time)->setTimezone($now->timezone);
                    $currentCarbon = \Carbon\Carbon::createFromFormat('H:i:s', $currentTime)->setTimezone($now->timezone);
                    
                    if ($currentCarbon->gte($openingCarbon) && $currentCarbon->lte($closingCarbon)) {
                        $isOpen = true;
                        break;
                    }
                }
            }
        }

        // Get timings data for all days
        $timingsData = $branch->timings->map(function ($timing) {
            return [
                'day' => $timing->day,
                'opening_time' => $timing->opening_time,
                'closing_time' => $timing->closing_time,
            ];
        });

        return [
            'id' => $branch->id,
            'name' => $branch->name,
            'address' => $branch->address,
            'description' => $branch->description,
            'logo' => $branch->logo ? url(Storage::url($branch->logo)) : null,
            'distance' => round($branch->distance, 2) . ' km',
            'status' => $isOpen ? 'open' : 'closed', // Branch status based on current time or 24x7 flag
            'is_24x7' => (bool)$branch->isOpen24x7, // Add 24x7 flag to response
            'timings' => $branch->isOpen24x7 ? null : ($timingsData->isEmpty() ? null : $timingsData), // Set timings to null if empty
        ];
    });

    return response()->json([
        'data' => ['branches_details' => $formattedBranches],
        'meta' => [
            'success' => true,
            'message' => 'Nearby branches retrieved successfully.',
        ],
    ], 200);
}

}
    // public function nearbyBranches(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'latitude' => 'required|numeric',
    //         'longitude' => 'required|numeric',
    //         'radius' => 'nullable|numeric|min:1', // Radius in kilometers, default 10
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'data' => json_decode('{}'),
    //             'meta' => [
    //                 'success' => false,
    //                 'message' => $validator->errors()->first(),
    //             ],
    //         ], 200);
    //     }

    //     $latitude = $request->latitude;
    //     $longitude = $request->longitude;
    //    $radius = $request->radius ?? 2; // Default radius 2 km

    //     // Calculate distance using Haversine formula
    //     $branches = Branch::selectRaw('branches.*, 
    //         (6371 * ACOS(
    //             COS(RADIANS(?)) * COS(RADIANS(branches.latitude)) *
    //             COS(RADIANS(branches.longitude) - RADIANS(?)) +
    //             SIN(RADIANS(?)) * SIN(RADIANS(branches.latitude))
    //         )) AS distance', [$latitude, $longitude, $latitude])
    //         ->where('is_active', 1)
    //         ->having('distance', '<', $radius)
    //         ->orderBy('distance')
    //         ->with(['timings' => function ($query) {
    //         $query->where('is_active', 1); // Fetch only active timings
    //     }])
    //         ->get();

    //     // Format the response
    //     $formattedBranches = $branches->map(function ($branch) {
    //         return [
    //             'id' => $branch->id,
    //             'name' => $branch->name,
    //             'address' => $branch->address,
    //             'description' => $branch->description,
    //             'logo' => $branch->logo ? url(Storage::url($branch->logo)) : null,
    //             'distance' => round($branch->distance, 2) . ' km',
    //             'timings' => $branch->timings->map(function ($timing) {
    //                 return [
    //                     'day' => $timing->day,
    //                     'opening_time' => $timing->opening_time,
    //                     'closing_time' => $timing->closing_time,
    //                 ];
    //             }),
    //         ];
    //     });

    //     return response()->json([
    //         'data' => ['branches_details' => $formattedBranches],
    //         'meta' => [
    //             'success' => true,
    //             'message' => 'Nearby branches retrieved successfully.',
    //         ],
    //     ], 200);
    // }
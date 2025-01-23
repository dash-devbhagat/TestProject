<?php

namespace App\Http\Controllers\API\V1;

use App\Models\State;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CityAPIController extends Controller
{
    // Fetch all cities for a given state using POST request
    public function getCitiesByStateId(Request $request)
    {
        // Validate that state_id is present in the request body
        $validator = Validator::make($request->all(), [
            'state_id' => 'required|exists:states,id', // Ensure state_id is valid
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'State ID is required and must be valid.',
                ],
            ], 200);
        }

        // Find the state using the validated state_id
        $state = State::find($request->state_id);

        if (!$state) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'State not found.',
                ],
            ], 404); // Return a 404 status if state not found
        }

        // Fetch the cities associated with the state
        $cities = $state->cities()->select('id', 'name')->get();

        if ($cities->isEmpty()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'No cities found for this state.',
                ],
            ], 200); // Return a 200 status if no cities are found
        }

        // Return the cities data in the required format
        return response()->json([
            'data' => $cities->map(function ($city) use ($state) {
            return [
                'id' => $city->id,
                'name' => $city->name,
                'state_id' => $state->id,
            ];
        }),
            'meta' => [
                'success' => true,
                'message' => 'Cities fetched successfully.',

            ],
        ]);
    }
}

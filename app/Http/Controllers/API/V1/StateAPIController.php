<?php

namespace App\Http\Controllers\API\V1;

use App\Models\State;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StateAPIController extends Controller
{
    // Fetch all states
    public function getAllStates()
    {
        $states = State::orderBy('id')->select('id', 'name')->get();

        if ($states->isEmpty()) {
            return response()->json([
                'data' => json_decode('{}'),
                'meta' => [
                    'success' => false,
                    'message' => 'No states found.',
                ],
            ], 200); // Return a 200 status with an appropriate message
        }

        return response()->json([
            'data' => (object) ['States' => $states],
            'meta' => [
                'success' => true,
                'message' => 'States fetched successfully.',
            ],
        ]);
    }
}

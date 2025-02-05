<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Deal;
use App\Models\DealsRedeem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class DealsAPIController extends Controller
{
    public function getAllDeals(Request $request)
    {
        // Get current date and time
        $currentDate = Carbon::now();

        // Fetch all active deals that are within the current date and time range
        $deals = Deal::where('is_active', 1)
            ->where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->get();

        // Return the deals data
        return response()->json([
            'data' => $deals,
            'meta' => [
                'success' => true,
                'message' => 'Deals fetched successfully',
            ],
        ], 200);
    }
}

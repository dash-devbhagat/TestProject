<?php

// app/Http/Middleware/CheckProfileCompletion.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckProfileCompletion
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Check if any required fields are missing
        if (
            !$user->phone ||
            !$user->storename ||
            !$user->location ||
            !$user->latitude ||
            !$user->longitude
        ) {
            // Redirect to profile completion page
            return redirect()->route('complete-profile');
        }

        return $next($request);
    }
}

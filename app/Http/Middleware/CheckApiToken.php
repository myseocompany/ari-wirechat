<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->bearerToken();
        $valid  = env('API_STATIC_TOKEN');

        

        if (!$header || $header !== $valid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}

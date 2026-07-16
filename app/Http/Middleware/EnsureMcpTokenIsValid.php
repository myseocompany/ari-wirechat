<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMcpTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('mcp.token', '');
        $providedToken = (string) $request->bearerToken();

        if ($expectedToken !== '' && $providedToken !== '' && hash_equals($expectedToken, $providedToken)) {
            return $next($request);
        }

        $user = Auth::guard('api')->user();

        if ($user !== null && method_exists($user, 'tokenCan') && $user->tokenCan('mcp:use')) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Unauthorized.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}

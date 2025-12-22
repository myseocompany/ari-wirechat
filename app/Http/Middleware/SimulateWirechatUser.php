<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SimulateWirechatUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $simulatedUserId = config('wirechat.simulated_user_id');

        if ($simulatedUserId === null) {
            return $next($request);
        }

        $userModel = config('wirechat.user_model');
        $simulatedUser = $userModel::query()->find($simulatedUserId);

        if (! $simulatedUser) {
            return $next($request);
        }

        Auth::setUser($simulatedUser);
        $request->setUserResolver(static fn () => $simulatedUser);

        return $next($request);
    }
}

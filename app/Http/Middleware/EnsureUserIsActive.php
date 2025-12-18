<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $statusName = strtolower($user->status->name ?? '');
            $isInactive = $statusName !== '' && in_array($statusName, ['inactivo', 'inactive', 'inactiva'], true);

            if ($isInactive) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'status' => 'Tu cuenta está inactiva. Comunícate con el administrador.',
                ]);
            }
        }

        return $next($request);
    }
}

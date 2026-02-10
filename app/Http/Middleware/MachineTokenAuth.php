<?php

namespace App\Http\Middleware;

use App\Models\MachineToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MachineTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorization = $request->header('Authorization');
        if (! $authorization || ! str_starts_with($authorization, 'Bearer ')) {
            return response()->json(['message' => 'Missing bearer token.'], Response::HTTP_UNAUTHORIZED);
        }

        $plainToken = trim(substr($authorization, 7));
        if ($plainToken === '') {
            return response()->json(['message' => 'Missing bearer token.'], Response::HTTP_UNAUTHORIZED);
        }

        $tokenHash = hash('sha256', $plainToken);
        $machineToken = MachineToken::query()
            ->with('machine')
            ->where('token_hash', $tokenHash)
            ->whereNull('revoked_at')
            ->first();

        if (! $machineToken) {
            return response()->json(['message' => 'Invalid token.'], Response::HTTP_UNAUTHORIZED);
        }

        $signatureHeader = $request->header('X-Signature');
        if (! $signatureHeader) {
            return response()->json(['message' => 'Missing signature.'], Response::HTTP_UNAUTHORIZED);
        }

        $signature = str_starts_with($signatureHeader, 'sha256=')
            ? substr($signatureHeader, 7)
            : $signatureHeader;

        $expectedSignature = hash_hmac('sha256', $request->getContent(), $plainToken);
        if (! hash_equals($expectedSignature, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], Response::HTTP_UNAUTHORIZED);
        }

        $machineToken->markUsed();
        $request->attributes->set('machine', $machineToken->machine);
        $request->attributes->set('machineToken', $machineToken);

        return $next($request);
    }
}

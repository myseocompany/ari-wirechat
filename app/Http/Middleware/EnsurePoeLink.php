<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enlaces del formulario POE: firma válida, o enlace sin firma solo durante el
 * periodo de transición (config poe.legacy_links_until). Nunca revela datos del cliente.
 */
class EnsurePoeLink
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasValidSignature()) {
            return $next($request);
        }

        // Una firma presente pero inválida (id alterado, URL editada) se rechaza siempre
        $tampered = $request->query->has('signature');
        $graceOpen = now()->lt(Carbon::parse(config('poe.legacy_links_until'))->endOfDay());

        if (! $tampered && $graceOpen) {
            Log::info('poe.legacy_link', $this->context($request, 'legacy_unsigned_link'));

            return $next($request);
        }

        Log::warning('poe.rejected', $this->context($request, $tampered ? 'invalid_signature' : 'unsigned_link_expired'));

        abort(403, 'Enlace no válido. Solicite un enlace nuevo a su asesor.');
    }

    private function context(Request $request, string $reason): array
    {
        return [
            'reason' => $reason,
            'ip' => $request->ip(),
            'customer_id' => $request->route('id'),
            'campaign_id' => $request->route('cid') ?? $request->query('cid'),
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\Geo\CountryCentroids;
use App\Services\Geo\PhoneCountryResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Mapa interno de ventas ganadas: ¿dónde ha vendido MQE?
 *
 * Página interna de análisis de territorio. No es pública y no expone datos de
 * clientes: todo se presenta agregado por país, nunca por cliente individual.
 *
 * Jerarquía de evidencia para el país, de mayor a menor:
 *   1. `customers.country` cuando ya es un ISO2 válido (dato autoritativo del CRM).
 *   2. Inferencia por indicativo telefónico (`PhoneCountryResolver`).
 * La procedencia se conserva por país y se muestra en la página, porque un país
 * cuya única evidencia es el indicativo telefónico no vale lo mismo que uno
 * registrado por un asesor.
 */
class SalesMapController extends Controller
{
    /** Estado "Ganado Maquinas" en `customer_statuses`. */
    private const STATUS_GANADO_MAQUINAS = 8;

    /**
     * Mercados que el reporte SEO de maquiempanadas.com analiza y prioriza
     * (reporte del 28-sep-2026), en ISO2. Se usa solo para contrastar con la venta real.
     */
    private const MERCADOS_REPORTE_SEO = ['US', 'CA', 'CO', 'AR', 'MX', 'CL', 'ES', 'VE', 'CR', 'PR'];

    public function index(PhoneCountryResolver $resolver, CountryCentroids $centroides): View
    {
        $customers = DB::table('customers')
            ->where('status_id', self::STATUS_GANADO_MAQUINAS)
            ->select('id', 'name', 'business', 'city', 'country', 'phone', 'phone_wp', 'phone2')
            ->get();

        // Nombres oficiales desde la tabla `countries` (no se hardcodean).
        $nombrePorIso2 = DB::table('countries')
            ->whereNotNull('iso2')
            ->pluck('name', 'iso2')
            ->mapWithKeys(fn ($nombre, $iso2) => [strtoupper($iso2) => $nombre]);

        // Índice inverso normalizado, para convertir el nombre que produce el
        // resolver telefónico en su ISO2 sin depender de acentos ni mayúsculas.
        $iso2PorNombre = [];
        foreach ($nombrePorIso2 as $iso2 => $nombre) {
            $iso2PorNombre[$this->normalizar($nombre)] = $iso2;
        }

        $agregado = [];
        $porFuente = ['crm' => 0, 'telefono' => 0, 'sin_dato' => 0];
        $clientes = [];

        foreach ($customers as $customer) {
            [$iso2, $fuente] = $this->resolverPais($customer, $resolver, $centroides, $nombrePorIso2, $iso2PorNombre);

            // Detalle por cliente para el listado. La pagina esta tras `auth` y es
            // interna, asi que aqui si corresponde identificar al cliente y enlazar
            // a su ficha; el agregado por pais es solo la vista de arriba.
            $clientes[] = [
                'id' => $customer->id,
                'nombre' => trim((string) $customer->name) ?: '(sin nombre)',
                'empresa' => trim((string) $customer->business),
                'ciudad' => trim((string) $customer->city),
                'iso2' => $iso2 ?? 'SIN_PAIS',
                'fuente' => $fuente,
            ];

            if ($iso2 === null) {
                $porFuente['sin_dato']++;

                continue;
            }

            $porFuente[$fuente]++;

            $agregado[$iso2] ??= [
                'iso2' => $iso2,
                'name' => $nombrePorIso2[$iso2] ?? $iso2,
                'ventas' => 0,
                'desde_crm' => 0,
                'inferido_telefono' => 0,
            ];
            $agregado[$iso2]['ventas']++;
            $agregado[$iso2][$fuente === 'crm' ? 'desde_crm' : 'inferido_telefono']++;
        }

        $total = $customers->count();
        $identificados = $total - $porFuente['sin_dato'];

        $countries = collect($agregado)
            ->map(function (array $pais) use ($centroides, $identificados) {
                $centroide = $centroides->forIso2($pais['iso2']);
                $pais['lat'] = $centroide[0] ?? null;
                $pais['lng'] = $centroide[1] ?? null;
                $pais['share'] = $identificados > 0 ? round($pais['ventas'] / $identificados * 100, 1) : 0.0;
                $pais['en_reporte_seo'] = in_array($pais['iso2'], self::MERCADOS_REPORTE_SEO, true);
                // Un país sin ningún registro del CRM se sostiene solo en la
                // inferencia telefónica: probable ruido de números mal digitados.
                $pais['solo_inferido'] = $pais['desde_crm'] === 0;

                return $pais;
            })
            ->sortByDesc('ventas')
            ->values();

        $sinCentroide = $countries->whereNull('lat')->pluck('iso2')->values();

        $clientes = collect($clientes)
            ->sortBy([['iso2', 'asc'], ['nombre', 'asc']])
            ->values();

        return view('reports.sales_map', [
            'countries' => $countries,
            'mapeables' => $countries->whereNotNull('lat')->values(),
            'total' => $total,
            'identificados' => $identificados,
            'porFuente' => $porFuente,
            'soloInferidos' => $countries->where('solo_inferido', true)->values(),
            'sinCentroide' => $sinCentroide,
            'puntosCiegos' => $countries->where('en_reporte_seo', false)->values(),
            'sinVenta' => collect(self::MERCADOS_REPORTE_SEO)
                ->reject(fn (string $iso2) => $countries->contains('iso2', $iso2))
                ->map(fn (string $iso2) => $nombrePorIso2[$iso2] ?? $iso2)
                ->values(),
            'maxVentas' => (int) ($countries->max('ventas') ?: 1),
            'mercadosReporteSeo' => count(self::MERCADOS_REPORTE_SEO),
            'clientes' => $clientes,
            'clientesPorPais' => $clientes->countBy('iso2'),
            // Payload compacto para el listado: con 2.500 clientes, renderizar las
            // filas en servidor llevaria el HTML de ~50 KB a ~2,4 MB, y solo se
            // consulta un pais a la vez. i=id n=nombre e=empresa c=ciudad p=iso2 f=fuente
            'clientesPayload' => $clientes->map(fn (array $c): array => [
                'i' => $c['id'],
                'n' => $c['nombre'],
                'e' => $c['empresa'],
                'c' => $c['ciudad'],
                'p' => $c['iso2'],
                'f' => $c['fuente'],
            ])->values(),
        ]);
    }

    /**
     * @return array{0: string|null, 1: string} [iso2, fuente]
     */
    private function resolverPais(
        object $customer,
        PhoneCountryResolver $resolver,
        CountryCentroids $centroides,
        $nombrePorIso2,
        array $iso2PorNombre
    ): array {
        // 1. El campo del CRM, si ya es un ISO2 que la tabla `countries` reconoce.
        $declarado = strtoupper(trim((string) $customer->country));
        if (strlen($declarado) === 2 && isset($nombrePorIso2[$declarado])) {
            return [$declarado, 'crm'];
        }

        // 2. Inferencia por indicativo telefónico.
        $inferido = $resolver->resolve([$customer->phone, $customer->phone_wp, $customer->phone2]);
        if ($inferido === null) {
            return [null, 'sin_dato'];
        }

        $iso2 = $centroides->aliasToIso2($inferido['name'])
            ?? ($iso2PorNombre[$this->normalizar($inferido['name'])] ?? null);

        return $iso2 === null ? [null, 'sin_dato'] : [$iso2, 'telefono'];
    }

    /** Quita acentos y mayúsculas para comparar nombres de país. */
    private function normalizar(string $valor): string
    {
        $sinAcentos = strtr(
            mb_strtolower(trim($valor)),
            ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']
        );

        return preg_replace('/[^a-z0-9]+/', '', $sinAcentos) ?? $sinAcentos;
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\Geo\PhoneCountryResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Mapa interno de ventas ganadas: ¿dónde ha vendido MQE?
 *
 * Página interna de análisis de territorio. No es pública y no expone datos de
 * clientes: todo se presenta agregado por país, nunca por cliente individual.
 */
class SalesMapController extends Controller
{
    /** Estado "Ganado Maquinas" en `customer_statuses`. */
    private const STATUS_GANADO_MAQUINAS = 8;

    /**
     * Mercados que el reporte SEO de maquiempanadas.com analiza y prioriza
     * (reporte del 28-sep-2026). Se usa solo para contrastar con la venta real.
     */
    private const MERCADOS_REPORTE_SEO = [
        'EE. UU. / Canadá',
        'Colombia',
        'Argentina',
        'México',
        'Chile',
        'España',
        'Venezuela',
        'Costa Rica',
        'Puerto Rico',
    ];

    public function index(PhoneCountryResolver $resolver): View
    {
        $customers = DB::table('customers')
            ->where('status_id', self::STATUS_GANADO_MAQUINAS)
            ->select('id', 'phone', 'phone_wp', 'phone2')
            ->get();

        $total = $customers->count();
        $countries = [];
        $sinIdentificar = 0;

        foreach ($customers as $customer) {
            $country = $resolver->resolve([
                $customer->phone,
                $customer->phone_wp,
                $customer->phone2,
            ]);

            if ($country === null) {
                $sinIdentificar++;

                continue;
            }

            $name = $country['name'];
            $countries[$name] ??= [
                'name' => $name,
                'lat' => $country['lat'],
                'lng' => $country['lng'],
                'ventas' => 0,
            ];
            $countries[$name]['ventas']++;
        }

        $identificados = $total - $sinIdentificar;

        $countries = collect($countries)
            ->sortByDesc('ventas')
            ->values()
            ->map(function (array $country) use ($identificados) {
                $country['share'] = $identificados > 0
                    ? round($country['ventas'] / $identificados * 100, 1)
                    : 0.0;
                $country['en_reporte_seo'] = in_array($country['name'], self::MERCADOS_REPORTE_SEO, true);

                return $country;
            });

        // Mercados que venden y el reporte SEO no mira.
        $puntosCiegos = $countries->where('en_reporte_seo', false)->values();

        // Mercados que el reporte SEO prioriza y donde no hay ninguna venta registrada.
        $conVenta = $countries->pluck('name')->all();
        $sinVenta = collect(self::MERCADOS_REPORTE_SEO)
            ->reject(fn (string $mercado) => in_array($mercado, $conVenta, true))
            ->values();

        return view('reports.sales_map', [
            'countries' => $countries,
            'total' => $total,
            'identificados' => $identificados,
            'sinIdentificar' => $sinIdentificar,
            'puntosCiegos' => $puntosCiegos,
            'sinVenta' => $sinVenta,
            'maxVentas' => (int) ($countries->max('ventas') ?: 1),
            'mercadosReporteSeo' => count(self::MERCADOS_REPORTE_SEO),
        ]);
    }
}

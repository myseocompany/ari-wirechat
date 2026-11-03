<?php

namespace App\Services\Geo;

/**
 * Centroides aproximados por código ISO 3166-1 alpha-2.
 *
 * La tabla `countries` del CRM aporta el nombre y el ISO2 pero no coordenadas,
 * así que este mapa solo existe para poder ubicar un círculo en el mapa.
 * NO representa la ubicación de ningún cliente: es el centro aproximado del país.
 */
class CountryCentroids
{
    /** ISO2 => [latitud, longitud] */
    private const CENTROIDS = [
        'AF' => [33.94, 67.71],   'AR' => [-38.42, -63.62], 'AT' => [47.52, 14.55],
        'AU' => [-25.27, 133.78], 'AW' => [12.52, -69.97],  'BE' => [50.50, 4.47],
        'BG' => [42.73, 25.49],   'BO' => [-16.29, -63.59], 'BR' => [-14.24, -51.93],
        'BS' => [25.03, -77.40],  'BZ' => [17.19, -88.50],  'CA' => [56.13, -106.35],
        'CH' => [46.82, 8.23],    'CL' => [-35.68, -71.54], 'CN' => [35.86, 104.20],
        'CO' => [4.57, -74.30],   'CR' => [9.75, -83.75],   'CU' => [21.52, -77.78],
        'CW' => [12.17, -68.99],  'DE' => [51.17, 10.45],   'DK' => [56.26, 9.50],
        'DM' => [15.41, -61.37],  'DO' => [18.74, -70.16],  'DZ' => [28.03, 1.66],
        'EC' => [-1.83, -78.18],  'ES' => [40.46, -3.75],   'FR' => [46.23, 2.21],
        'GB' => [55.38, -3.44],   'GF' => [3.93, -53.13],   'GT' => [15.78, -90.23],
        'HN' => [15.20, -86.24],  'HT' => [18.97, -72.29],  'IL' => [31.05, 34.85],
        'IT' => [41.87, 12.57],   'JM' => [18.11, -77.30],  'LU' => [49.82, 6.13],
        'MM' => [21.91, 95.96],   'MX' => [23.63, -102.55], 'MY' => [4.21, 101.98],
        'NI' => [12.87, -85.21],  'NL' => [52.13, 5.29],    'NO' => [60.47, 8.47],
        'NZ' => [-40.90, 174.89], 'PA' => [8.54, -80.78],   'PE' => [-9.19, -75.02],
        'PR' => [18.22, -66.59],  'PT' => [39.40, -8.22],   'PY' => [-23.44, -58.44],
        'SR' => [3.92, -56.03],   'SV' => [13.79, -88.90],  'TT' => [10.69, -61.22],
        'US' => [39.83, -98.58],  'UY' => [-32.52, -55.77], 'VE' => [6.42, -66.59],
    ];

    /**
     * Alias para nombres que el resolver telefónico produce y que no son un país
     * con ISO2 propio. El indicativo +1 cubre EE. UU. y Canadá: sin el código de
     * área no se pueden separar, así que se atribuye a US y queda marcado como
     * inferido para que la imprecisión sea visible en la página.
     */
    private const ALIASES = [
        'EE. UU. / CANADÁ' => 'US',
    ];

    /** @return array{0: float, 1: float}|null */
    public function forIso2(string $iso2): ?array
    {
        return self::CENTROIDS[strtoupper(trim($iso2))] ?? null;
    }

    public function aliasToIso2(string $nombre): ?string
    {
        return self::ALIASES[mb_strtoupper(trim($nombre))] ?? null;
    }

    public function has(string $iso2): bool
    {
        return isset(self::CENTROIDS[strtoupper(trim($iso2))]);
    }
}

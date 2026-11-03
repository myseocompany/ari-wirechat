<?php

namespace App\Services\Geo;

/**
 * Deriva país a partir del indicativo telefónico internacional.
 *
 * Contexto: en la tabla `customers` los campos geográficos (`city`, `country`,
 * `address`) están prácticamente vacíos, pero `phone` está diligenciado en la
 * gran mayoría de los registros y conserva el indicativo del país. Este resolver
 * es la única fuente de geografía disponible hoy sin enriquecer datos.
 *
 * Limitación conocida y deliberada: el indicativo identifica el país, NO la
 * ciudad. En Colombia la mayoría de los números son móviles (3xx), que no
 * tienen indicativo regional, así que el detalle por ciudad no es derivable.
 */
class PhoneCountryResolver
{
    /**
     * Indicativo => [nombre, latitud, longitud].
     * Las coordenadas son el centroide aproximado del país, usado solo para
     * ubicar el círculo en el mapa. No representan la ubicación del cliente.
     */
    private const COUNTRIES = [
        '1242' => ['Bahamas', 25.03, -77.40],
        '1246' => ['Barbados', 13.19, -59.54],
        '1284' => ['Islas Vírgenes Británicas', 18.42, -64.64],
        '1721' => ['Sint Maarten', 18.04, -63.06],
        '1758' => ['Santa Lucía', 13.91, -60.98],
        '1767' => ['Dominica', 15.41, -61.37],
        '1784' => ['San Vicente y las Granadinas', 13.25, -61.19],
        '1787' => ['Puerto Rico', 18.22, -66.59],
        '1809' => ['República Dominicana', 18.74, -70.16],
        '1829' => ['República Dominicana', 18.74, -70.16],
        '1849' => ['República Dominicana', 18.74, -70.16],
        '1868' => ['Trinidad y Tobago', 10.69, -61.22],
        '1869' => ['San Cristóbal y Nieves', 17.36, -62.78],
        '1876' => ['Jamaica', 18.11, -77.30],
        '1939' => ['Puerto Rico', 18.22, -66.59],
        '501' => ['Belice', 17.19, -88.50],
        '502' => ['Guatemala', 15.78, -90.23],
        '503' => ['El Salvador', 13.79, -88.90],
        '504' => ['Honduras', 15.20, -86.24],
        '505' => ['Nicaragua', 12.87, -85.21],
        '506' => ['Costa Rica', 9.75, -83.75],
        '507' => ['Panamá', 8.54, -80.78],
        '509' => ['Haití', 18.97, -72.29],
        '591' => ['Bolivia', -16.29, -63.59],
        '593' => ['Ecuador', -1.83, -78.18],
        '594' => ['Guayana Francesa', 3.93, -53.13],
        '595' => ['Paraguay', -23.44, -58.44],
        '597' => ['Surinam', 3.92, -56.03],
        '598' => ['Uruguay', -32.52, -55.77],
        '971' => ['Emiratos Árabes Unidos', 23.42, 53.85],
        '972' => ['Israel', 31.05, 34.85],
        '34' => ['España', 40.46, -3.75],
        '39' => ['Italia', 41.87, 12.57],
        '44' => ['Reino Unido', 55.38, -3.44],
        '51' => ['Perú', -9.19, -75.02],
        '52' => ['México', 23.63, -102.55],
        '53' => ['Cuba', 21.52, -77.78],
        '54' => ['Argentina', -38.42, -63.62],
        '55' => ['Brasil', -14.24, -51.93],
        '56' => ['Chile', -35.68, -71.54],
        '57' => ['Colombia', 4.57, -74.30],
        '58' => ['Venezuela', 6.42, -66.59],
        '61' => ['Australia', -25.27, 133.78],
        '1' => ['EE. UU. / Canadá', 39.83, -98.58],
    ];

    /** Indicativos ordenados de más largo a más corto, para no confundir 1868 con 1. */
    private static ?array $sorted = null;

    /**
     * Resuelve el país a partir del primer teléfono utilizable de la lista.
     *
     * @param  array<int, string|null>  $phones
     * @return array{name: string, lat: float, lng: float}|null
     */
    public function resolve(array $phones): ?array
    {
        foreach ($phones as $phone) {
            $digits = preg_replace('/\D/', '', (string) $phone);

            if ($digits === '') {
                continue;
            }

            foreach (self::sortedCodes() as $code => $data) {
                // Exige al menos 6 dígitos de número local después del indicativo
                // para no clasificar fragmentos o números mal digitados.
                if (str_starts_with($digits, (string) $code) && strlen($digits) >= strlen((string) $code) + 6) {
                    return ['name' => $data[0], 'lat' => $data[1], 'lng' => $data[2]];
                }
            }
        }

        return null;
    }

    /** @return array<string, array{0: string, 1: float, 2: float}> */
    private static function sortedCodes(): array
    {
        if (self::$sorted === null) {
            $codes = self::COUNTRIES;
            uksort($codes, fn ($a, $b) => strlen((string) $b) <=> strlen((string) $a));
            self::$sorted = $codes;
        }

        return self::$sorted;
    }
}

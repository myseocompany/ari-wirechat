<?php

namespace App\Services\LeadClassifier;

class LeadSignalsHeuristicExtractor
{
    /**
     * @param  array{full_customer_text: string}  $snapshot
     * @return array<string, bool|int|null>
     */
    public function extract(array $snapshot): array
    {
        $text = mb_strtolower($snapshot['full_customer_text'] ?? '');

        $volumenEstimado = $this->extractVolumeEstimate($text);

        $signals = [
            'pide_cita_fabrica' => $this->containsAny($text, [
                'ir a la fabrica',
                'visitar la fabrica',
                'visitar la planta',
                'ir a la planta',
                'cita en la fabrica',
                'cita en la planta',
                'agendar cita en la fabrica',
                'agendar cita en la planta',
                'visita a la fabrica',
            ]),
            'pide_llamada' => $this->containsAny($text, [
                'llamada',
                'llamar',
                'me llamas',
                'me puedes llamar',
                'podemos hablar',
                'agendamos una llamada',
                'agendar una llamada',
                'agendar llamada',
                'reunion',
                'reunión',
                'podemos tener una reunion',
                'podemos tener una reunión',
            ]),
            'tiene_productos' => $this->containsAny($text, [
                'yo produzco',
                'produzco',
                'producimos',
                'estoy produciendo',
                'vendo empanadas',
                'vendemos empanadas',
                'hago empanadas',
                'hacemos empanadas',
                'vendo arepas',
                'tenemos negocio',
                'tengo negocio',
                'vendo todos los dias',
                'vendo todos los días',
                'ya vendo',
                'ya produzco',
            ]),
            'solo_proyecto' => $this->containsAny($text, [
                'tengo un proyecto',
                'es un proyecto',
                'quiero empezar',
                'quiero iniciar',
                'voy a empezar',
                'apenas estoy empezando',
                'aun no produzco',
                'aún no produzco',
                'todavia no produzco',
                'todavía no produzco',
                'no produzco',
            ]),
            'volumen_mayor_500' => is_int($volumenEstimado) && $volumenEstimado >= 500,
            'volumen_estimado' => $volumenEstimado,
            'dolor_operarios' => $this->containsAny($text, [
                'no consigo operarios',
                'no tenemos operarios',
                'los operarios fallan',
                'dependemos del personal',
                'dependemos mucho del personal',
                'no me alcanza el personal',
                'me falta personal',
            ]),
            'dolor_tiempo' => $this->containsAny($text, [
                'me quita mucho tiempo',
                'toma mucho tiempo',
                'muy lento',
                'es muy lento',
                'tardo demasiado',
                'demasiado tiempo',
                'no doy abasto',
                'no me alcanza el tiempo',
            ]),
            'dolor_merma_calidad' => $this->containsAny($text, [
                'mucha merma',
                'hay merma',
                'se dañan',
                'mala calidad',
                'no salen uniformes',
                'no salen iguales',
            ]),
            'apertura_nuevo_punto' => $this->containsAny($text, [
                'abrir otro punto',
                'abrir un punto',
                'nuevo punto',
                'nuevo local',
                'nueva sede',
                'abrir otra sede',
                'expandir',
                'expansion',
                'expansión',
            ]),
            'demanda_supera_capacidad' => $this->containsAny($text, [
                'tengo mucha demanda',
                'me piden mas',
                'me piden más',
                'no alcanzo a cumplir',
                'no alcanzo',
                'no doy abasto',
                'se me queda corto',
            ]),
            'habla_escalar' => $this->containsAny($text, [
                'quiero crecer',
                'crecer',
                'escalar',
                'automatizar',
                'aumentar produccion',
                'aumentar producción',
                'duplicar',
                'triplicar',
            ]),
            'urgencia_alta' => $this->containsAny($text, [
                'lo necesito ya',
                'lo necesito urgente',
                'urgente',
                'este mes',
                'esta semana',
                'lo mas pronto posible',
                'lo más pronto posible',
                'para ya',
                'cuanto antes',
                'cuánto antes',
            ]),
            'tiene_presupuesto' => $this->containsAny($text, [
                'tengo presupuesto',
                'hay presupuesto',
                'tengo el dinero',
                'ya tengo el dinero',
                'voy a invertir',
                'quiero invertir',
                'tengo la plata',
            ]),
            'pide_cotizacion_o_ficha' => $this->containsAny($text, [
                'cotizacion',
                'cotización',
                'ficha tecnica',
                'ficha técnica',
                'mandame la ficha',
                'mándame la ficha',
                'enviame la ficha',
                'envíame la ficha',
                'mandame la cotizacion',
                'mándame la cotización',
            ]),
            'pregunta_pago_logistica' => $this->containsAny($text, [
                'como pago',
                'cómo pago',
                'medios de pago',
                'financiacion',
                'financiación',
                'tiempo de entrega',
                'tiempos de entrega',
                'envio',
                'envío',
                'instalacion',
                'instalación',
                'repuestos',
                'mantenimiento',
            ]),
            'negocio_activo_explicitado' => $this->containsAny($text, [
                'tengo un negocio',
                'tenemos un negocio',
                'tengo restaurante',
                'tengo panaderia',
                'tengo panadería',
                'tengo local',
                'tenemos local',
                'vendo todos los dias',
                'vendo todos los días',
            ]),
        ];

        if ($signals['tiene_productos'] === true) {
            $signals['solo_proyecto'] = false;
        }

        return $signals;
    }

    private function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function extractVolumeEstimate(string $text): ?int
    {
        if ($text === '') {
            return null;
        }

        preg_match_all('/\b(\d{2,6})\b/u', $text, $matches);

        if (! isset($matches[1]) || $matches[1] === []) {
            return null;
        }

        $numbers = collect($matches[1])
            ->map(fn ($value) => (int) $value)
            ->filter(fn (int $value) => $value >= 20 && $value <= 200000)
            ->values();

        if ($numbers->isEmpty()) {
            return null;
        }

        return (int) $numbers->max();
    }
}

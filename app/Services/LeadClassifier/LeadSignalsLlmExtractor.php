<?php

namespace App\Services\LeadClassifier;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class LeadSignalsLlmExtractor
{
    /**
     * @param  array{full_customer_text: string}  $snapshot
     * @return array{
     *     signals: array<string, bool|int|null>,
     *     reasons: array<int, string>,
     *     llm_used: bool,
     *     llm_error: string|null,
     *     llm_duration_ms: int|null,
     *     model: string|null
     * }
     */
    public function extract(array $snapshot): array
    {
        $heuristicSignals = app(LeadSignalsHeuristicExtractor::class)->extract($snapshot);

        $apiKey = (string) config('openai.api_key');
        $model = (string) config('openai.model');
        $baseUrl = rtrim((string) config('openai.base_url', 'https://api.openai.com/v1'), '/');
        $timeout = (int) config('openai.timeout', 30);

        if ($apiKey === '' || $model === '') {
            return [
                'signals' => $heuristicSignals,
                'reasons' => $this->buildFallbackReasons($heuristicSignals),
                'llm_used' => false,
                'llm_error' => 'missing_openai_configuration',
                'llm_duration_ms' => null,
                'model' => null,
            ];
        }

        $prompt = $this->buildPrompt($snapshot['full_customer_text'] ?? '');

        try {
            $startTime = microtime(true);
            $response = Http::timeout($timeout)
                ->withToken($apiKey)
                ->acceptJson()
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'temperature' => 0,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un clasificador de leads B2B. Respondes estrictamente en JSON válido.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            if ($response->failed()) {
                return [
                    'signals' => $heuristicSignals,
                    'reasons' => $this->buildFallbackReasons($heuristicSignals),
                    'llm_used' => false,
                    'llm_error' => 'http_'.$response->status(),
                    'llm_duration_ms' => $durationMs,
                    'model' => $model,
                ];
            }

            $content = (string) data_get($response->json(), 'choices.0.message.content', '');
            $decoded = json_decode($content, true);

            if (! is_array($decoded)) {
                return [
                    'signals' => $heuristicSignals,
                    'reasons' => $this->buildFallbackReasons($heuristicSignals),
                    'llm_used' => false,
                    'llm_error' => 'invalid_json',
                    'llm_duration_ms' => $durationMs,
                    'model' => $model,
                ];
            }

            $llmSignals = is_array($decoded['signals'] ?? null) ? $decoded['signals'] : [];
            $reasons = is_array($decoded['reasons'] ?? null) ? $decoded['reasons'] : [];

            $mergedSignals = $this->mergeSignals($heuristicSignals, $llmSignals);

            $normalizedReasons = collect($reasons)
                ->filter(fn ($reason) => is_string($reason) && trim($reason) !== '')
                ->map(fn ($reason) => Str::limit(trim($reason), 200))
                ->values()
                ->take(6)
                ->all();

            if ($normalizedReasons === []) {
                $normalizedReasons = $this->buildFallbackReasons($mergedSignals);
            }

            return [
                'signals' => $mergedSignals,
                'reasons' => $normalizedReasons,
                'llm_used' => true,
                'llm_error' => null,
                'llm_duration_ms' => $durationMs,
                'model' => $model,
            ];
        } catch (Throwable $exception) {
            return [
                'signals' => $heuristicSignals,
                'reasons' => $this->buildFallbackReasons($heuristicSignals),
                'llm_used' => false,
                'llm_error' => $exception->getMessage(),
                'llm_duration_ms' => null,
                'model' => $model,
            ];
        }
    }

    private function buildPrompt(string $fullCustomerText): string
    {
        return <<<PROMPT
Analiza SOLO mensajes del cliente (no hay respuestas del bot).

Devuelve un JSON con esta forma exacta:
{
  "signals": {
    "pide_cita_fabrica": boolean,
    "pide_llamada": boolean,
    "tiene_productos": boolean,
    "solo_proyecto": boolean,
    "volumen_mayor_500": boolean,
    "volumen_estimado": number|null,
    "dolor_operarios": boolean,
    "dolor_tiempo": boolean,
    "dolor_merma_calidad": boolean,
    "apertura_nuevo_punto": boolean,
    "demanda_supera_capacidad": boolean,
    "habla_escalar": boolean,
    "urgencia_alta": boolean,
    "tiene_presupuesto": boolean,
    "pide_cotizacion_o_ficha": boolean,
    "pregunta_pago_logistica": boolean,
    "negocio_activo_explicitado": boolean
  },
  "reasons": ["razones cortas y concretas"]
}

Reglas críticas:
- Si el cliente pide visitar la fábrica/planta, marca pide_cita_fabrica=true.
- Si el cliente pide llamada/reunión, marca pide_llamada=true.
- Tiene máxima prioridad detectar cita a fábrica y llamada.
- "tengo un proyecto" suele ser solo_proyecto=true, excepto si también hay señales claras de operación real.
- Si hay números y parecen volumen de producción, estima volumen_estimado.

Mensajes del cliente:
\"\"\"{$fullCustomerText}\"\"\"
PROMPT;
    }

    /**
     * @param  array<string, bool|int|null>  $heuristicSignals
     * @param  array<string, mixed>  $llmSignals
     * @return array<string, bool|int|null>
     */
    private function mergeSignals(array $heuristicSignals, array $llmSignals): array
    {
        $merged = $heuristicSignals;

        foreach ($llmSignals as $key => $value) {
            if (! array_key_exists($key, $merged)) {
                continue;
            }

            if (is_bool($merged[$key])) {
                $merged[$key] = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $merged[$key];

                continue;
            }

            if ($key === 'volumen_estimado') {
                $numericValue = is_numeric($value) ? (int) $value : null;
                $merged[$key] = $numericValue ?? $merged[$key];

                continue;
            }

            $merged[$key] = $merged[$key];
        }

        if (($merged['tiene_productos'] ?? false) === true) {
            $merged['solo_proyecto'] = false;
        }

        $volumenEstimado = $merged['volumen_estimado'] ?? null;
        $merged['volumen_mayor_500'] = is_int($volumenEstimado) && $volumenEstimado >= 500;

        return $merged;
    }

    /**
     * @param  array<string, bool|int|null>  $signals
     * @return array<int, string>
     */
    private function buildFallbackReasons(array $signals): array
    {
        $reasons = [];

        $reasonMap = [
            'pide_cita_fabrica' => 'El cliente pide cita para ir a la fábrica.',
            'pide_llamada' => 'El cliente pide llamada o reunión.',
            'tiene_productos' => 'El cliente ya produce o vende.',
            'volumen_mayor_500' => 'El cliente menciona volumen mayor a 500.',
            'apertura_nuevo_punto' => 'El cliente habla de abrir un nuevo punto.',
            'dolor_operarios' => 'El cliente menciona problemas con operarios.',
            'dolor_tiempo' => 'El cliente menciona dolor por tiempo o lentitud.',
            'urgencia_alta' => 'El cliente expresa urgencia alta.',
            'tiene_presupuesto' => 'El cliente expresa que tiene presupuesto.',
            'pide_cotizacion_o_ficha' => 'El cliente pide cotización o ficha técnica.',
        ];

        foreach ($reasonMap as $key => $label) {
            if (($signals[$key] ?? false) === true) {
                $reasons[] = $label;
            }
        }

        if ($reasons === [] && ($signals['solo_proyecto'] ?? false) === true) {
            $reasons[] = 'El cliente parece estar en etapa de proyecto.';
        }

        return array_slice($reasons, 0, 6);
    }
}

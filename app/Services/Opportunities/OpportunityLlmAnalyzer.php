<?php

namespace App\Services\Opportunities;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class OpportunityLlmAnalyzer
{
    private const MAX_TEXT_LENGTH = 5000;

    /**
     * @return array{
     *     llm_used: bool,
     *     llm_error: string|null,
     *     llm_duration_ms: int|null,
     *     model: string|null,
     *     produce_empanadas: string,
     *     estimated_daily_empanadas: int|null,
     *     intent: string,
     *     confidence: float|null,
     *     evidence: string|null,
     *     next_best_action: string,
     *     recommended_channel: string,
     *     recommended_sla: string,
     *     action_reason: string|null,
     *     suggested_message: string|null,
     *     stop_condition: string|null
     * }
     */
    public function analyze(object $row): array
    {
        $apiKey = (string) config('openai.api_key');
        $model = (string) config('openai.opportunity_model', config('openai.model'));
        $baseUrl = rtrim((string) config('openai.base_url', 'https://api.openai.com/v1'), '/');
        $timeout = (int) config('openai.timeout', 30);
        $prompt = $this->buildPrompt($row);

        if ($apiKey === '' || $model === '') {
            return $this->fallback('missing_openai_configuration');
        }

        if ($prompt === '') {
            return $this->fallback('insufficient_context', $model);
        }

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
                            'content' => 'Eres un analista comercial B2B. Devuelves estrictamente JSON válido.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            if ($response->failed()) {
                return $this->fallback('http_'.$response->status(), $model, $durationMs);
            }

            $content = (string) data_get($response->json(), 'choices.0.message.content', '');
            $decoded = json_decode($content, true);

            if (! is_array($decoded)) {
                return $this->fallback('invalid_json', $model, $durationMs);
            }

            return [
                'llm_used' => true,
                'llm_error' => null,
                'llm_duration_ms' => $durationMs,
                'model' => $model,
                'produce_empanadas' => $this->normalizeChoice($decoded['produce_empanadas'] ?? null, ['yes', 'no', 'unknown', 'other'], 'unknown'),
                'estimated_daily_empanadas' => $this->normalizeAmount($decoded['estimated_daily_empanadas'] ?? null),
                'intent' => $this->normalizeChoice($decoded['intent'] ?? null, ['buy', 'quote', 'info', 'event', 'support', 'unknown'], 'unknown'),
                'confidence' => $this->normalizeConfidence($decoded['confidence'] ?? null),
                'evidence' => $this->normalizeText($decoded['evidence'] ?? null),
                'next_best_action' => $this->normalizeChoice($decoded['next_best_action'] ?? null, ['reply_whatsapp', 'create_call_task', 'send_quote', 'book_demo', 'qualify_project', 'assign_owner', 'wait_for_signal', 'disqualify'], 'wait_for_signal'),
                'recommended_channel' => $this->normalizeChoice($decoded['recommended_channel'] ?? null, ['whatsapp', 'phone', 'email', 'crm', 'none'], 'crm'),
                'recommended_sla' => $this->normalizeChoice($decoded['recommended_sla'] ?? null, ['hoy', '24h', '48h', 'esta_semana', 'esperar'], '24h'),
                'action_reason' => $this->normalizeText($decoded['action_reason'] ?? null),
                'suggested_message' => $this->normalizeText($decoded['suggested_message'] ?? null, 280),
                'stop_condition' => $this->normalizeText($decoded['stop_condition'] ?? null),
            ];
        } catch (Throwable $exception) {
            return $this->fallback($exception->getMessage(), $model);
        }
    }

    private function buildPrompt(object $row): string
    {
        $messages = trim((string) ($row->analysis_messages_body ?? $row->last_messages_body ?? ''));

        if ($messages === '') {
            return '';
        }

        $messages = Str::limit($messages, self::MAX_TEXT_LENGTH, '');

        return <<<PROMPT
Analiza este prospecto para venta de máquinas de empanadas.

Campos CRM:
- maker: {$row->maker}
- count_empanadas: {$row->count_empanadas}
- estado: {$row->status_name}
- asesor: {$row->user_name}
- origen: {$row->source_name}

Mensajes recientes:
"""{$messages}"""

Devuelve SOLO este JSON:
{
  "produce_empanadas": "yes|no|unknown|other",
  "estimated_daily_empanadas": number|null,
  "intent": "buy|quote|info|event|support|unknown",
  "confidence": number,
  "evidence": "frase textual corta o null",
  "next_best_action": "reply_whatsapp|create_call_task|send_quote|book_demo|qualify_project|assign_owner|wait_for_signal|disqualify",
  "recommended_channel": "whatsapp|phone|email|crm|none",
  "recommended_sla": "hoy|24h|48h|esta_semana|esperar",
  "action_reason": "razón breve o null",
  "suggested_message": "mensaje corto para el asesor enviar o null",
  "stop_condition": "condición breve para dejar de insistir o null"
}

Reglas:
- Usa "yes" solo si hay evidencia de que ya hace o vende empanadas.
- Usa "no" si está en proyecto, quiere empezar o explícitamente no produce todavía.
- Usa "other" si habla de otro producto/uso, como desmechadora, sin empanadas.
- estimated_daily_empanadas debe ser producción diaria. No uses teléfonos, fechas, precios ni cuotas.
- intent "quote" si pide precio, cotización, ficha o condiciones.
- intent "buy" si expresa decisión de compra, visita para comprar o cierre.
- evidence debe ser una frase corta tomada del texto, sin inventar.
- No recomiendes auto-envío. El mensaje sugerido es un borrador para revisión humana.
- suggested_message debe estar en español, sonar natural y tener máximo 60 palabras.
PROMPT;
    }

    private function normalizeChoice(mixed $value, array $allowed, string $default): string
    {
        $normalized = is_string($value) ? Str::lower(trim($value)) : '';

        return in_array($normalized, $allowed, true) ? $normalized : $default;
    }

    private function normalizeAmount(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $amount = (int) $value;

        return $amount > 0 && $amount < 100000 ? $amount : null;
    }

    private function normalizeConfidence(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        return max(0.0, min(1.0, (float) $value));
    }

    private function normalizeText(mixed $value, int $limit = 180): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $text = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        return $text === '' ? null : Str::limit($text, $limit);
    }

    /**
     * @return array{
     *     llm_used: bool,
     *     llm_error: string|null,
     *     llm_duration_ms: int|null,
     *     model: string|null,
     *     produce_empanadas: string,
     *     estimated_daily_empanadas: int|null,
     *     intent: string,
     *     confidence: float|null,
     *     evidence: string|null,
     *     next_best_action: string,
     *     recommended_channel: string,
     *     recommended_sla: string,
     *     action_reason: string|null,
     *     suggested_message: string|null,
     *     stop_condition: string|null
     * }
     */
    private function fallback(string $error, ?string $model = null, ?int $durationMs = null): array
    {
        return [
            'llm_used' => false,
            'llm_error' => $error,
            'llm_duration_ms' => $durationMs,
            'model' => $model,
            'produce_empanadas' => 'unknown',
            'estimated_daily_empanadas' => null,
            'intent' => 'unknown',
            'confidence' => null,
            'evidence' => null,
            'next_best_action' => 'wait_for_signal',
            'recommended_channel' => 'crm',
            'recommended_sla' => 'esperar',
            'action_reason' => null,
            'suggested_message' => null,
            'stop_condition' => null,
        ];
    }
}

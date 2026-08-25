<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class CustomerCallBriefingGenerator
{
    /**
     * @param  array{version: string, hash: string, system: string, user: string}  $prompt
     * @return array{summary: string, known_facts: array<int, array{text: string, occurred_at: string|null, source: string}>, conflicting_facts: array<int, array{text: string, sources: array<int, string>}>, avoid_asking: array<int, array{text: string, reason: string, source: string}>, suggested_opening: string, next_question: string, model: string}
     */
    public function generate(array $prompt, string $context): array
    {
        $apiKey = (string) config('openai.api_key');
        $model = (string) config('openai.call_briefing_model', config('openai.model'));
        $baseUrl = rtrim((string) config('openai.base_url', 'https://api.openai.com/v1'), '/');
        $timeout = (int) config('openai.timeout', 30);

        if ($apiKey === '' || $model === '') {
            throw new RuntimeException('missing_openai_configuration');
        }

        $response = Http::timeout($timeout)
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'temperature' => 0,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $prompt['system']],
                    ['role' => 'user', 'content' => app(CustomerCallBriefingPromptLoader::class)->renderUser($prompt, $context)],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('llm_http_'.$response->status());
        }

        $decoded = json_decode((string) data_get($response->json(), 'choices.0.message.content', ''), true);
        if (! is_array($decoded)) {
            throw new RuntimeException('invalid_llm_response');
        }

        $briefing = $this->validateResponse($decoded);
        $briefing['model'] = $model;

        return $briefing;
    }

    /**
     * @param  array<string, mixed>  $value
     * @return array{summary: string, known_facts: array<int, array{text: string, occurred_at: string|null, source: string}>, conflicting_facts: array<int, array{text: string, sources: array<int, string>}>, avoid_asking: array<int, array{text: string, reason: string, source: string}>, suggested_opening: string, next_question: string}
     */
    public function validateResponse(array $value): array
    {
        $keys = ['summary', 'known_facts', 'conflicting_facts', 'avoid_asking', 'suggested_opening', 'next_question'];
        if (array_diff($keys, array_keys($value)) !== [] || array_diff(array_keys($value), $keys) !== []) {
            throw new RuntimeException('invalid_llm_response');
        }

        $summary = $this->text($value['summary'] ?? null, 1000);
        if ($summary === '' || str_word_count($summary) > 80) {
            throw new RuntimeException('invalid_llm_response');
        }

        $opening = $this->text($value['suggested_opening'] ?? null, 500);
        $question = $this->text($value['next_question'] ?? null, 300);
        if ($opening === '' || $question === '' || $this->sentenceCount($opening) > 2 || ! $this->isSingleQuestion($question)) {
            throw new RuntimeException('invalid_llm_response');
        }

        return [
            'summary' => $summary,
            'known_facts' => $this->facts($value['known_facts'] ?? null),
            'conflicting_facts' => $this->conflicts($value['conflicting_facts'] ?? null),
            'avoid_asking' => $this->avoid($value['avoid_asking'] ?? null),
            'suggested_opening' => $opening,
            'next_question' => $question,
        ];
    }

    /** @return array<int, array{text: string, occurred_at: string|null, source: string}> */
    private function facts(mixed $items): array
    {
        if (! is_array($items) || count($items) > 6) {
            throw new RuntimeException('invalid_llm_response');
        }

        return array_map(function ($item): array {
            if (! is_array($item)) {
                throw new RuntimeException('invalid_llm_response');
            }

            $text = $this->text($item['text'] ?? null, 240);
            $source = $this->text($item['source'] ?? null, 120);
            $occurredAt = $item['occurred_at'] ?? null;
            if ($text === '' || $source === '' || ! $this->isUtcDate($occurredAt)) {
                throw new RuntimeException('invalid_llm_response');
            }

            return ['text' => $text, 'occurred_at' => $occurredAt, 'source' => $source];
        }, $items);
    }

    /** @return array<int, array{text: string, sources: array<int, string>}> */
    private function conflicts(mixed $items): array
    {
        if (! is_array($items) || count($items) > 4) {
            throw new RuntimeException('invalid_llm_response');
        }

        return array_map(function ($item): array {
            if (! is_array($item) || ! is_array($item['sources'] ?? null) || count($item['sources']) < 2 || count($item['sources']) > 4) {
                throw new RuntimeException('invalid_llm_response');
            }

            $text = $this->text($item['text'] ?? null, 320);
            $sources = array_map(fn ($source) => $this->text($source, 120), $item['sources']);
            if ($text === '' || in_array('', $sources, true)) {
                throw new RuntimeException('invalid_llm_response');
            }

            return ['text' => $text, 'sources' => $sources];
        }, $items);
    }

    /** @return array<int, array{text: string, reason: string, source: string}> */
    private function avoid(mixed $items): array
    {
        if (! is_array($items) || count($items) > 4) {
            throw new RuntimeException('invalid_llm_response');
        }

        return array_map(function ($item): array {
            if (! is_array($item)) {
                throw new RuntimeException('invalid_llm_response');
            }

            $text = $this->text($item['text'] ?? null, 240);
            $reason = $this->text($item['reason'] ?? null, 240);
            $source = $this->text($item['source'] ?? null, 120);
            if ($text === '' || $reason === '' || $source === '') {
                throw new RuntimeException('invalid_llm_response');
            }

            return compact('text', 'reason', 'source');
        }, $items);
    }

    private function text(mixed $value, int $limit): string
    {
        if (! is_string($value)) {
            return '';
        }

        $value = trim($value);

        return mb_strlen($value) <= $limit ? $value : '';
    }

    private function isUtcDate(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (! is_string($value) || ! preg_match('/^\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2}Z$/', $value)) {
            return false;
        }

        try {
            return \Carbon\CarbonImmutable::createFromFormat('Y-m-d\\TH:i:s\\Z', $value, 'UTC')->format('Y-m-d\\TH:i:s\\Z') === $value;
        } catch (\Throwable) {
            return false;
        }
    }

    private function sentenceCount(string $value): int
    {
        return preg_match_all('/[.!?]+(?:\\s|$)/u', $value) ?: 1;
    }

    private function isSingleQuestion(string $value): bool
    {
        return substr_count($value, '?') === 1 && str_ends_with($value, '?');
    }
}

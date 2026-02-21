<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ChannelsApiService
{
    public function __construct(private readonly ChannelsCallNormalizer $normalizer) {}

    public function isConfigured(): bool
    {
        return $this->apiToken() !== '' && $this->account() !== '';
    }

    public function listCalls(CarbonInterface $fromDate, CarbonInterface $toDate, array $filters = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('CHANNELS_API_TOKEN y CHANNELS_ACCOUNT son obligatorios para consultar llamadas.');
        }

        $calls = [];
        $page = 1;
        $cursor = null;
        $nextUrl = null;

        for ($iteration = 0; $iteration < 50; $iteration++) {
            $response = $nextUrl !== null
                ? $this->requestAbsolute('GET', $nextUrl, [], true)
                : $this->request(
                    'GET',
                    '/api/v1/calls',
                    $this->buildCallsQuery($fromDate, $toDate, $filters, $page, $cursor),
                    [],
                    true
                );

            $json = $response->json();
            if (! is_array($json)) {
                break;
            }

            $batch = $this->normalizer->extractCallItems($json);
            $calls = [...$calls, ...$batch];

            $resolvedNextUrl = $this->resolveNextUrl($json);
            $resolvedNextCursor = $this->resolveNextCursor($json);
            $resolvedNextPage = $this->resolveNextPage($json, $page);
            $hasMore = $this->resolveHasMore($json);

            if ($resolvedNextUrl !== null) {
                $nextUrl = $resolvedNextUrl;
                $cursor = null;

                continue;
            }

            if ($resolvedNextCursor !== null && $resolvedNextCursor !== $cursor) {
                $cursor = $resolvedNextCursor;
                $nextUrl = null;

                continue;
            }

            if ($resolvedNextPage !== null && $resolvedNextPage > $page) {
                $page = $resolvedNextPage;
                $cursor = null;
                $nextUrl = null;

                continue;
            }

            if ($hasMore === true && $batch !== []) {
                $page++;
                $cursor = null;
                $nextUrl = null;

                continue;
            }

            break;
        }

        return $this->normalizer->uniqueCalls($calls);
    }

    public function fetchRecordingForCall(string $callId): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('No hay credenciales de Channels configuradas.');
        }

        $response = $this->request('GET', '/api/v1/call/'.rawurlencode($callId).'/recording', [], [], false);
        $payload = $response->json();
        $payload = is_array($payload) ? $payload : [];

        $recordingUrl = $this->normalizer->extractRecordingUrl($payload);
        $errorCode = $this->extractErrorCode($payload);
        $token = $this->normalizer->extractToken($payload);

        if ($recordingUrl === null && $response->status() === 404 && $errorCode === null) {
            $errorCode = 'RECORDING_NOT_FOUND';
        }

        if ($recordingUrl === null) {
            $generated = $this->createRecordingLink($callId);
            if ($generated !== null) {
                $recordingUrl = $generated['recording_url'] ?? null;
                $errorCode = $generated['error_code'] ?? $errorCode;
                $token = $generated['token'] ?? $token;
                $payload = $generated['raw'] ?? $payload;
            }
        }

        return [
            'recording_url' => $recordingUrl,
            'error_code' => $errorCode,
            'token' => $token,
            'status' => $response->status(),
            'raw' => $payload,
        ];
    }

    public function downloadRecordingContent(string $recordingUrl): array
    {
        $response = Http::timeout(max(20, $this->timeout() * 3))
            ->accept('audio/mpeg, audio/wav, */*')
            ->get($recordingUrl);

        if ($response->failed() || $response->body() === '') {
            $response = Http::timeout(max(20, $this->timeout() * 3))
                ->accept('audio/mpeg, audio/wav, */*')
                ->withHeaders($this->headers())
                ->get($recordingUrl);
        }

        if ($response->failed() || $response->body() === '') {
            throw new RuntimeException(sprintf(
                'No se pudo descargar el audio (%d): %s',
                $response->status(),
                Str::limit((string) $response->body(), 240)
            ));
        }

        return [
            'body' => $response->body(),
            'content_type' => (string) $response->header('Content-Type', ''),
            'content_length' => is_numeric($response->header('Content-Length'))
                ? (int) $response->header('Content-Length')
                : strlen($response->body()),
        ];
    }

    private function createRecordingLink(string $callId): ?array
    {
        $payloads = [
            ['callId' => $callId],
            ['call_id' => $callId],
            ['id' => $callId],
        ];

        foreach ($payloads as $payload) {
            $response = $this->request('POST', '/api/v1/recordings', [], $payload, false);
            if (! $response->successful()) {
                continue;
            }

            $json = $response->json();
            if (! is_array($json)) {
                continue;
            }

            $recordingUrl = $this->normalizer->extractRecordingUrl($json);
            if ($recordingUrl === null) {
                continue;
            }

            return [
                'recording_url' => $recordingUrl,
                'error_code' => $this->extractErrorCode($json),
                'token' => $this->normalizer->extractToken($json),
                'raw' => $json,
            ];
        }

        return null;
    }

    private function request(
        string $method,
        string $endpoint,
        array $query = [],
        array $json = [],
        bool $throwOnFailure = true
    ): Response {
        $url = $this->buildUrl($endpoint);

        return $this->requestAbsolute($method, $url, [
            'query' => $this->cleanParams($query),
            'json' => $json,
        ], $throwOnFailure);
    }

    private function requestAbsolute(
        string $method,
        string $url,
        array $options = [],
        bool $throwOnFailure = true
    ): Response {
        $response = Http::timeout($this->timeout())
            ->acceptJson()
            ->withHeaders($this->headers())
            ->send($method, $url, $options);

        if ($throwOnFailure && $response->failed()) {
            throw new RuntimeException(sprintf(
                'Channels API request failed (%d): %s',
                $response->status(),
                Str::limit((string) $response->body(), 300)
            ));
        }

        return $response;
    }

    private function buildCallsQuery(
        CarbonInterface $fromDate,
        CarbonInterface $toDate,
        array $filters,
        int $page,
        ?string $cursor
    ): array {
        $fromIso = $fromDate->utc()->format('Y-m-d\TH:i:s\Z');
        $toIso = $toDate->utc()->format('Y-m-d\TH:i:s\Z');

        $query = [
            'from' => $fromIso,
            'to' => $toIso,
            'fromDate' => $fromIso,
            'toDate' => $toIso,
            'dateFrom' => $fromIso,
            'dateTo' => $toIso,
            'startDate' => $fromIso,
            'endDate' => $toIso,
            'limit' => 200,
            'perPage' => 200,
            'page' => $page,
        ];

        $callId = trim((string) ($filters['call_id'] ?? ''));
        $agentId = trim((string) ($filters['agent_id'] ?? ''));
        $msisdn = trim((string) ($filters['msisdn'] ?? ''));

        if ($callId !== '') {
            $query['callId'] = $callId;
            $query['call_id'] = $callId;
            $query['id'] = $callId;
        }

        if ($agentId !== '') {
            $query['agentId'] = $agentId;
            $query['agent_id'] = $agentId;
        }

        if ($msisdn !== '') {
            $query['msisdn'] = $msisdn;
            $query['phone'] = $msisdn;
        }

        if ($cursor !== null && trim($cursor) !== '') {
            $query['cursor'] = $cursor;
        }

        return $query;
    }

    private function resolveHasMore(array $payload): ?bool
    {
        $value = data_get($payload, 'hasMore')
            ?? data_get($payload, 'has_more')
            ?? data_get($payload, 'meta.hasMore')
            ?? data_get($payload, 'meta.has_more')
            ?? data_get($payload, 'pagination.hasMore')
            ?? data_get($payload, 'pagination.has_more');

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'yes'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no'], true)) {
                return false;
            }
        }

        return null;
    }

    private function resolveNextPage(array $payload, int $currentPage): ?int
    {
        $value = data_get($payload, 'nextPage')
            ?? data_get($payload, 'next_page')
            ?? data_get($payload, 'meta.nextPage')
            ?? data_get($payload, 'meta.next_page')
            ?? data_get($payload, 'pagination.nextPage')
            ?? data_get($payload, 'pagination.next_page');

        if (! is_numeric($value)) {
            return null;
        }

        $nextPage = (int) $value;

        return $nextPage > $currentPage ? $nextPage : null;
    }

    private function resolveNextCursor(array $payload): ?string
    {
        $value = data_get($payload, 'nextCursor')
            ?? data_get($payload, 'next_cursor')
            ?? data_get($payload, 'meta.nextCursor')
            ?? data_get($payload, 'meta.next_cursor')
            ?? data_get($payload, 'pagination.nextCursor')
            ?? data_get($payload, 'pagination.next_cursor');

        if (! is_scalar($value)) {
            return null;
        }

        $cursor = trim((string) $value);

        return $cursor !== '' ? $cursor : null;
    }

    private function resolveNextUrl(array $payload): ?string
    {
        $value = data_get($payload, 'nextUrl')
            ?? data_get($payload, 'next_url')
            ?? data_get($payload, 'meta.nextUrl')
            ?? data_get($payload, 'meta.next_url')
            ?? data_get($payload, 'links.next');

        if (! is_scalar($value)) {
            return null;
        }

        $url = trim((string) $value);
        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return $this->buildUrl($url);
    }

    private function extractErrorCode(array $payload): ?string
    {
        $value = data_get($payload, 'errorCode')
            ?? data_get($payload, 'error_code')
            ?? data_get($payload, 'error.code')
            ?? data_get($payload, 'code');

        if (! is_scalar($value)) {
            return null;
        }

        $code = trim((string) $value);

        return $code !== '' ? $code : null;
    }

    private function cleanParams(array $params): array
    {
        return array_filter($params, function ($value): bool {
            if ($value === null) {
                return false;
            }

            if (is_string($value)) {
                return trim($value) !== '';
            }

            return true;
        });
    }

    private function headers(): array
    {
        return [
            'x-api-token' => $this->apiToken(),
            'Account' => $this->account(),
        ];
    }

    private function buildUrl(string $endpoint): string
    {
        if (str_starts_with($endpoint, 'http://') || str_starts_with($endpoint, 'https://')) {
            return $endpoint;
        }

        $base = rtrim((string) config('services.channels.base_url', 'https://api.channels.app'), '/');
        $path = '/'.ltrim($endpoint, '/');

        return $base.$path;
    }

    private function apiToken(): string
    {
        return trim((string) config('services.channels.api_token', ''));
    }

    private function account(): string
    {
        return trim((string) config('services.channels.account', ''));
    }

    private function timeout(): int
    {
        $timeout = (int) config('services.channels.timeout', 30);

        return $timeout > 0 ? $timeout : 30;
    }
}

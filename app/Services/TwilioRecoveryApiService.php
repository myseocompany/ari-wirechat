<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class TwilioRecoveryApiService
{
    public function isConfigured(): bool
    {
        return $this->accountSid() !== '' && $this->authToken() !== '';
    }

    public function listCalls(CarbonInterface $fromDate, CarbonInterface $toDate, array $filters = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('TWILIO_ACCOUNT_SID y TWILIO_AUTH_TOKEN son obligatorios para consultar llamadas.');
        }

        $calls = [];
        $nextUrl = null;

        for ($iteration = 0; $iteration < 50; $iteration++) {
            $response = $nextUrl === null
                ? $this->request(
                    'GET',
                    $this->buildAccountPath('/Calls.json'),
                    $this->buildCallsQuery($fromDate, $toDate, $filters),
                    true
                )
                : $this->requestAbsolute('GET', $nextUrl, [], true);

            $payload = $response->json();
            if (! is_array($payload)) {
                break;
            }

            $items = data_get($payload, 'calls');
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $calls[] = $this->normalizeCall($item);
                }
            }

            $nextUrl = $this->resolveNextPageUrl($payload);
            if ($nextUrl === null) {
                break;
            }
        }

        $recordingsByCallSid = $this->listRecordingsByCallSid($fromDate, $toDate);

        $merged = array_map(function (array $call) use ($recordingsByCallSid): array {
            $callSidKey = strtolower(trim((string) ($call['call_sid'] ?? '')));
            if ($callSidKey === '' || ! isset($recordingsByCallSid[$callSidKey])) {
                return $call;
            }

            $recording = $recordingsByCallSid[$callSidKey];
            $call['recording_exists'] = true;
            $call['recording_sid'] = $recording['recording_sid'] ?? null;
            $call['recording_url'] = $recording['recording_url'] ?? null;
            $call['duration_seconds'] = $call['duration_seconds'] ?? ($recording['duration_seconds'] ?? null);
            $call['recording_raw'] = $recording['raw'] ?? null;

            return $call;
        }, $calls);

        return $this->uniqueCalls($merged);
    }

    public function fetchRecordingForCall(string $callSid): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('No hay credenciales de Twilio configuradas.');
        }

        $response = $this->request(
            'GET',
            $this->buildAccountPath('/Recordings.json'),
            [
                'CallSid' => trim($callSid),
                'PageSize' => 20,
            ],
            false
        );

        $payload = $response->json();
        $payload = is_array($payload) ? $payload : [];

        $recordings = data_get($payload, 'recordings');
        if (! is_array($recordings) || $recordings === []) {
            return [
                'recording_url' => null,
                'recording_sid' => null,
                'duration_seconds' => null,
                'error_code' => 'RECORDING_NOT_FOUND',
                'status' => $response->status(),
                'raw' => $payload,
            ];
        }

        foreach ($recordings as $recording) {
            if (! is_array($recording)) {
                continue;
            }

            $normalized = $this->normalizeRecording($recording);
            if (! empty($normalized['recording_url'])) {
                return [
                    'recording_url' => $normalized['recording_url'],
                    'recording_sid' => $normalized['recording_sid'] ?? null,
                    'duration_seconds' => $normalized['duration_seconds'] ?? null,
                    'error_code' => null,
                    'status' => $response->status(),
                    'raw' => $recording,
                ];
            }
        }

        return [
            'recording_url' => null,
            'recording_sid' => null,
            'duration_seconds' => null,
            'error_code' => 'RECORDING_URL_NOT_FOUND',
            'status' => $response->status(),
            'raw' => $payload,
        ];
    }

    public function downloadRecordingContent(string $recordingUrl): array
    {
        $normalizedUrl = $this->ensureRecordingAudioUrl($recordingUrl);
        $response = $this->requestMedia($normalizedUrl);

        if (($response->failed() || $response->body() === '') && $normalizedUrl !== $recordingUrl) {
            $response = $this->requestMedia($recordingUrl);
        }

        if ($response->failed() || $response->body() === '') {
            throw new RuntimeException(sprintf(
                'No se pudo descargar el audio Twilio (%d): %s',
                $response->status(),
                Str::limit((string) $response->body(), 240)
            ));
        }

        return [
            'body' => $response->body(),
            'content_type' => (string) $response->header('Content-Type', ''),
            'content_length' => is_numeric($response->header('Content-Length'))
                ? (int) $response->header('Content-Length')
                : strlen((string) $response->body()),
        ];
    }

    public function normalizeUrlForMatch(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $normalized = trim($url);
        if (str_starts_with($normalized, '//')) {
            $normalized = 'https:'.$normalized;
        }

        if (filter_var($normalized, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $scheme = strtolower((string) parse_url($normalized, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($normalized, PHP_URL_HOST));
        $path = (string) parse_url($normalized, PHP_URL_PATH);

        if ($host === '') {
            return null;
        }

        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        return sprintf('%s://%s%s', $scheme === '' ? 'https' : $scheme, $host, $path);
    }

    private function buildCallsQuery(CarbonInterface $fromDate, CarbonInterface $toDate, array $filters): array
    {
        $query = [
            'PageSize' => 200,
            'StartTime>=' => $fromDate->format('Y-m-d'),
            'StartTime<=' => $toDate->format('Y-m-d'),
        ];

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $query['Status'] = $status;
        }

        return $query;
    }

    private function listRecordingsByCallSid(CarbonInterface $fromDate, CarbonInterface $toDate): array
    {
        $map = [];
        $nextUrl = null;

        for ($iteration = 0; $iteration < 50; $iteration++) {
            $response = $nextUrl === null
                ? $this->request(
                    'GET',
                    $this->buildAccountPath('/Recordings.json'),
                    [
                        'PageSize' => 200,
                        'DateCreated>=' => $fromDate->format('Y-m-d'),
                        'DateCreated<=' => $toDate->format('Y-m-d'),
                    ],
                    false
                )
                : $this->requestAbsolute('GET', $nextUrl, [], false);

            if ($response->failed()) {
                break;
            }

            $payload = $response->json();
            if (! is_array($payload)) {
                break;
            }

            $recordings = data_get($payload, 'recordings');
            if (is_array($recordings)) {
                foreach ($recordings as $recording) {
                    if (! is_array($recording)) {
                        continue;
                    }

                    $normalized = $this->normalizeRecording($recording);
                    $callSid = strtolower(trim((string) ($normalized['call_sid'] ?? '')));
                    if ($callSid === '' || empty($normalized['recording_url'])) {
                        continue;
                    }

                    if (! isset($map[$callSid])) {
                        $map[$callSid] = $normalized;

                        continue;
                    }

                    $existingDate = $map[$callSid]['recording_created_at'] ?? null;
                    $candidateDate = $normalized['recording_created_at'] ?? null;

                    if ($existingDate instanceof CarbonImmutable && $candidateDate instanceof CarbonImmutable) {
                        if ($candidateDate->greaterThan($existingDate)) {
                            $map[$callSid] = $normalized;
                        }

                        continue;
                    }

                    if (! ($existingDate instanceof CarbonImmutable) && $candidateDate instanceof CarbonImmutable) {
                        $map[$callSid] = $normalized;
                    }
                }
            }

            $nextUrl = $this->resolveNextPageUrl($payload);
            if ($nextUrl === null) {
                break;
            }
        }

        return $map;
    }

    private function normalizeCall(array $call): array
    {
        $callSid = trim((string) ($call['sid'] ?? ''));
        if ($callSid === '') {
            $callSid = 'unknown-'.substr(sha1((string) json_encode($call)), 0, 16);
        }

        $fromNumber = $this->normalizePhone((string) ($call['from'] ?? ''));
        $toNumber = $this->normalizePhone((string) ($call['to'] ?? ''));
        $direction = strtolower(trim((string) ($call['direction'] ?? '')));

        return [
            'call_sid' => $callSid,
            'call_created_at' => $this->parseDate(
                (string) ($call['start_time'] ?? $call['date_created'] ?? $call['timestamp'] ?? '')
            ),
            'from_number' => $fromNumber,
            'to_number' => $toNumber,
            'contact_msisdn' => $this->resolveContactMsisdn($fromNumber, $toNumber, $direction),
            'direction' => $direction !== '' ? $direction : null,
            'status_text' => trim((string) ($call['status'] ?? '')) ?: null,
            'duration_seconds' => $this->toNonNegativeInt($call['duration'] ?? null),
            'recording_exists' => false,
            'recording_sid' => null,
            'recording_url' => null,
            'raw' => $call,
        ];
    }

    private function normalizeRecording(array $recording): array
    {
        $uri = trim((string) ($recording['uri'] ?? ''));
        $mediaUrl = trim((string) ($recording['media_url'] ?? ''));

        $recordingUrl = $mediaUrl;
        if ($recordingUrl === '' && $uri !== '') {
            $recordingUrl = 'https://api.twilio.com'.$uri;
        }

        if ($recordingUrl !== '' && str_ends_with($recordingUrl, '.json')) {
            $recordingUrl = substr($recordingUrl, 0, -5);
        }

        if ($recordingUrl !== '') {
            $recordingUrl = $this->ensureRecordingAudioUrl($recordingUrl);
        }

        return [
            'call_sid' => trim((string) ($recording['call_sid'] ?? '')),
            'recording_sid' => trim((string) ($recording['sid'] ?? '')) ?: null,
            'duration_seconds' => $this->toNonNegativeInt($recording['duration'] ?? null),
            'recording_url' => $recordingUrl !== '' ? $recordingUrl : null,
            'recording_created_at' => $this->parseDate((string) ($recording['date_created'] ?? '')),
            'raw' => $recording,
        ];
    }

    private function resolveContactMsisdn(?string $fromNumber, ?string $toNumber, string $direction): ?string
    {
        $callerIdDigits = $this->normalizePhone((string) config('services.twilio.caller_id', ''));

        if ($callerIdDigits !== null) {
            if ($toNumber === $callerIdDigits && $fromNumber !== null) {
                return $fromNumber;
            }

            if ($fromNumber === $callerIdDigits && $toNumber !== null) {
                return $toNumber;
            }
        }

        if (str_contains($direction, 'outbound') && $toNumber !== null) {
            return $toNumber;
        }

        if (str_contains($direction, 'inbound') && $fromNumber !== null) {
            return $fromNumber;
        }

        return $toNumber ?? $fromNumber;
    }

    private function uniqueCalls(array $calls): array
    {
        $unique = [];

        foreach ($calls as $call) {
            if (! is_array($call)) {
                continue;
            }

            $key = strtolower(trim((string) ($call['call_sid'] ?? '')));
            if ($key === '') {
                $key = 'unknown-'.substr(sha1((string) json_encode($call)), 0, 16);
            }

            $unique[$key] = $call;
        }

        $rows = array_values($unique);
        usort($rows, function (array $left, array $right): int {
            $leftDate = $left['call_created_at'] ?? null;
            $rightDate = $right['call_created_at'] ?? null;

            if ($leftDate instanceof CarbonImmutable && $rightDate instanceof CarbonImmutable) {
                return $rightDate->getTimestamp() <=> $leftDate->getTimestamp();
            }

            if ($leftDate instanceof CarbonImmutable) {
                return -1;
            }

            if ($rightDate instanceof CarbonImmutable) {
                return 1;
            }

            return strcmp((string) ($right['call_sid'] ?? ''), (string) ($left['call_sid'] ?? ''));
        });

        return $rows;
    }

    private function parseDate(string $value): ?CarbonImmutable
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($trimmed)->utc();
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (! is_string($digits) || $digits === '') {
            return null;
        }

        return $digits;
    }

    private function toNonNegativeInt(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (int) floor((float) $value);

        return $number >= 0 ? $number : null;
    }

    private function request(string $method, string $path, array $query = [], bool $throwOnFailure = true): Response
    {
        return $this->requestAbsolute($method, $this->buildUrl($path), $query, $throwOnFailure);
    }

    private function requestAbsolute(string $method, string $url, array $query = [], bool $throwOnFailure = true): Response
    {
        $response = Http::timeout($this->timeout())
            ->acceptJson()
            ->withBasicAuth($this->accountSid(), $this->authToken())
            ->send($method, $url, [
                'query' => $this->cleanParams($query),
            ]);

        if ($throwOnFailure && $response->failed()) {
            throw new RuntimeException(sprintf(
                'Twilio API request failed (%d): %s',
                $response->status(),
                Str::limit((string) $response->body(), 300)
            ));
        }

        return $response;
    }

    private function requestMedia(string $url): Response
    {
        $request = Http::timeout(max(20, $this->timeout() * 3))
            ->accept('audio/mpeg, audio/wav, */*');

        if ($this->isTwilioUrl($url)) {
            $request = $request->withBasicAuth($this->accountSid(), $this->authToken());
        }

        return $request->get($url);
    }

    private function resolveNextPageUrl(array $payload): ?string
    {
        $uri = trim((string) ($payload['next_page_uri'] ?? ''));
        if ($uri === '') {
            return null;
        }

        if (str_starts_with($uri, 'http://') || str_starts_with($uri, 'https://')) {
            return $uri;
        }

        return rtrim($this->baseApiUrl(), '/').'/'.ltrim($uri, '/');
    }

    private function ensureRecordingAudioUrl(string $recordingUrl): string
    {
        if (preg_match('/\.(mp3|wav|m4a|ogg|oga|webm)(\?.*)?$/i', $recordingUrl) === 1) {
            return $recordingUrl;
        }

        return $recordingUrl.'.mp3';
    }

    private function isTwilioUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return $host === 'api.twilio.com';
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

    private function buildUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return rtrim($this->baseApiUrl(), '/').'/'.ltrim($path, '/');
    }

    private function buildAccountPath(string $path): string
    {
        return sprintf(
            '/2010-04-01/Accounts/%s/%s',
            rawurlencode($this->accountSid()),
            ltrim($path, '/')
        );
    }

    private function baseApiUrl(): string
    {
        return 'https://api.twilio.com';
    }

    private function accountSid(): string
    {
        return trim((string) config('services.twilio.account_sid', ''));
    }

    private function authToken(): string
    {
        return trim((string) config('services.twilio.auth_token', ''));
    }

    private function timeout(): int
    {
        return 30;
    }
}

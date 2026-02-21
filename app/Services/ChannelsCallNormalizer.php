<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class ChannelsCallNormalizer
{
    public function extractCallItems(array $response): array
    {
        $items = $this->extractList($response);
        $calls = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $calls[] = $this->normalizeCall($item);
        }

        return $this->uniqueCalls($calls);
    }

    public function normalizeCall(array $call): array
    {
        $callId = $this->extractCallId($call) ?? 'unknown-'.substr(sha1((string) json_encode($call)), 0, 16);
        $recordingUrl = $this->extractRecordingUrl($call);
        $recordingExists = $this->firstBooleanValue($call, [
            'recordingExists',
            'recording.exists',
            'hasRecording',
            'has_recording',
        ]);

        if ($recordingExists === null) {
            $recordingExists = $recordingUrl !== null;
        }

        return [
            'call_id' => $callId,
            'call_created_at' => $this->extractCallCreatedAt($call),
            'msisdn' => $this->normalizePhone($this->firstStringValue($call, [
                'msisdn',
                'phoneNumber',
                'phone',
                'to',
                'toNumber',
                'contact.msisdns.0',
            ])),
            'agent_id' => $this->extractAgentId($call),
            'agent_username' => $this->extractAgentUsername($call),
            'agent_name' => $this->extractAgentName($call),
            'agent_surname' => $this->extractAgentSurname($call),
            'agent_msisdn' => $this->normalizePhone($this->extractAgentMsisdn($call)),
            'call_duration_seconds' => $this->extractDurationSeconds($call),
            'recording_exists' => $recordingExists,
            'recording_url' => $recordingUrl,
            'status' => $this->firstStringValue($call, [
                'status',
                'callStatus',
                'call_status',
                'lastEventType',
            ]),
            'raw' => $call,
        ];
    }

    public function uniqueCalls(array $calls): array
    {
        $unique = [];

        foreach ($calls as $call) {
            if (! is_array($call)) {
                continue;
            }

            $key = strtolower((string) ($call['call_id'] ?? ''));
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

            return strcmp((string) ($right['call_id'] ?? ''), (string) ($left['call_id'] ?? ''));
        });

        return $rows;
    }

    public function extractCallId(array $payload): ?string
    {
        $callId = $this->firstStringValue($payload, [
            'callId',
            'call_id',
            'id',
            'call.id',
            'conversationId',
            'conversation_id',
        ]);

        if ($callId !== null) {
            return $callId;
        }

        return $this->searchStringByKeyPattern($payload, 'call', true);
    }

    public function extractRecordingUrl(array $payload): ?string
    {
        $url = $this->firstStringValue($payload, [
            'recordingLink',
            'recordingUrl',
            'recording_url',
            'recording.url',
            'recording.link',
            'audio.url',
            'audioUrl',
            'link',
            'url',
            'data.recordingUrl',
            'data.recordingLink',
            'data.link',
            'data.url',
        ]);

        $normalized = $this->normalizePotentialUrl($url);
        if ($normalized !== null) {
            return $normalized;
        }

        return $this->searchStringByKeyPattern($payload, 'recording', false);
    }

    public function extractToken(array $payload): ?string
    {
        return $this->firstStringValue($payload, [
            'token',
            'recordingToken',
            'archiveToken',
            'data.token',
            'data.recordingToken',
            'data.archiveToken',
        ]);
    }

    public function extractAgentId(array $payload): ?string
    {
        return $this->firstStringValue($payload, [
            'agentId',
            'agent_id',
            'agent.id',
            'userId',
            'user_id',
            'user.id',
            'ownerId',
            'owner_id',
            'assignedUserId',
            'assigned_user_id',
            'assignedAgentId',
            'sellerId',
            'seller_id',
            'agent',
        ]);
    }

    public function extractAgentUsername(array $payload): ?string
    {
        return $this->firstStringValue($payload, [
            'agentUsername',
            'agent_username',
            'agent.username',
            'agent.email',
            'user.email',
            'owner.email',
            'seller.email',
        ]);
    }

    public function extractAgentName(array $payload): ?string
    {
        return $this->firstStringValue($payload, [
            'agentName',
            'agent_name',
            'agent.name',
            'user.name',
            'owner.name',
            'seller.name',
        ]);
    }

    public function extractAgentSurname(array $payload): ?string
    {
        return $this->firstStringValue($payload, [
            'agentSurname',
            'agent_surname',
            'agent.surname',
            'user.surname',
            'owner.surname',
            'seller.surname',
            'agent.lastName',
            'agent.last_name',
        ]);
    }

    public function extractAgentMsisdn(array $payload): ?string
    {
        return $this->firstStringValue($payload, [
            'agentMsisdn',
            'agent_msisdn',
            'agent.msisdn',
            'agent.phone',
            'agent.phoneNumber',
        ]);
    }

    public function parseDate(?string $value): ?CarbonImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $trimmed = trim($value);

        if (is_numeric($trimmed)) {
            $numeric = (float) $trimmed;
            if ($numeric <= 0) {
                return null;
            }

            // Channels puede enviar unix epoch en segundos o milisegundos.
            $seconds = $numeric > 9999999999 ? (int) floor($numeric / 1000) : (int) floor($numeric);
            if ($seconds < 946684800) { // 2000-01-01 UTC
                return null;
            }

            try {
                return CarbonImmutable::createFromTimestampUTC($seconds);
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return CarbonImmutable::parse($trimmed)->utc();
        } catch (\Throwable) {
            return null;
        }
    }

    public function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if (! is_string($digits) || $digits === '') {
            return null;
        }

        return $digits;
    }

    public function normalizeUrlForMatch(?string $url): ?string
    {
        $normalized = $this->normalizePotentialUrl($url);
        if ($normalized === null) {
            return null;
        }

        $scheme = strtolower((string) parse_url($normalized, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($normalized, PHP_URL_HOST));
        $path = (string) parse_url($normalized, PHP_URL_PATH);

        if ($host === '') {
            $fallback = preg_replace('/[?#].*$/', '', trim($normalized));
            if (! is_string($fallback) || $fallback === '') {
                return null;
            }

            return strtolower(rtrim($fallback, '/'));
        }

        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        return sprintf('%s://%s%s', $scheme === '' ? 'https' : $scheme, $host, $path);
    }

    public function unwrapWebhookPayload(mixed $payload): array
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            $payload = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($payload)) {
            return [];
        }

        $data = $payload;
        if (array_is_list($data) && isset($data[0]) && is_array($data[0])) {
            $data = $data[0];
        }

        if (isset($data['body']) && is_array($data['body'])) {
            $data = $data['body'];
        }

        return $data;
    }

    public function extractCallIdFromWebhook(mixed $payload, ?string $payloadRaw = null): ?string
    {
        $data = $this->unwrapWebhookPayload($payload);
        $callId = $this->extractCallId($data);
        if ($callId !== null) {
            return $callId;
        }

        if ($payloadRaw === null || $payloadRaw === '') {
            return null;
        }

        if (preg_match('/"(?:callId|call_id|conversationId|conversation_id)"\s*:\s*"([^"]+)"/i', $payloadRaw, $matches) === 1) {
            return trim((string) ($matches[1] ?? '')) ?: null;
        }

        return null;
    }

    public function extractRecordingUrlFromWebhook(mixed $payload, ?string $payloadRaw = null): ?string
    {
        $data = $this->unwrapWebhookPayload($payload);
        $url = $this->extractRecordingUrl($data);
        if ($url !== null) {
            return $url;
        }

        if ($payloadRaw === null || $payloadRaw === '') {
            return null;
        }

        if (preg_match('/"(?:recordingLink|recordingUrl|recording_url|audioUrl|audio_url)"\s*:\s*"([^"]+)"/i', $payloadRaw, $matches) === 1) {
            return $this->normalizePotentialUrl((string) ($matches[1] ?? ''));
        }

        return null;
    }

    public function extractAgentIdFromWebhook(mixed $payload, ?string $payloadRaw = null): ?string
    {
        $data = $this->unwrapWebhookPayload($payload);
        $agentId = $this->extractAgentId($data);
        if ($agentId !== null) {
            return $agentId;
        }

        if ($payloadRaw === null || $payloadRaw === '') {
            return null;
        }

        if (preg_match('/"(?:agentId|agent_id|userId|user_id|ownerId|owner_id|sellerId|seller_id)"\s*:\s*"?(.*?)"?(,|\})/i', $payloadRaw, $matches) === 1) {
            $value = trim((string) ($matches[1] ?? ''));

            return $value !== '' ? trim($value, '"') : null;
        }

        return null;
    }

    private function extractList(array $response): array
    {
        $candidates = [
            data_get($response, 'calls'),
            data_get($response, 'data.calls'),
            data_get($response, 'data.items'),
            data_get($response, 'items'),
            data_get($response, 'results'),
            data_get($response, 'data'),
            $response,
        ];

        foreach ($candidates as $candidate) {
            if (! is_array($candidate) || ! array_is_list($candidate)) {
                continue;
            }

            if ($candidate === [] || is_array($candidate[0] ?? null)) {
                return $candidate;
            }
        }

        return [];
    }

    private function firstStringValue(array $payload, array $paths): ?string
    {
        foreach ($paths as $path) {
            $value = data_get($payload, $path);

            if (is_string($value)) {
                $trimmed = trim($value);
                if ($trimmed !== '') {
                    return $trimmed;
                }
            }

            if (is_numeric($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    private function firstBooleanValue(array $payload, array $paths): ?bool
    {
        foreach ($paths as $path) {
            $value = data_get($payload, $path);

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
        }

        return null;
    }

    private function normalizePotentialUrl(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        if (str_starts_with($trimmed, '//')) {
            $trimmed = 'https:'.$trimmed;
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return $trimmed;
    }

    private function searchStringByKeyPattern(array $payload, string $needle, bool $allowAnyValue): ?string
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $nested = $this->searchStringByKeyPattern($value, $needle, $allowAnyValue);
                if ($nested !== null) {
                    return $nested;
                }

                continue;
            }

            if (! is_string($key) || ! is_string($value)) {
                continue;
            }

            if (! str_contains(strtolower($key), strtolower($needle))) {
                continue;
            }

            if ($allowAnyValue) {
                $trimmed = trim($value);
                if ($trimmed !== '') {
                    return $trimmed;
                }

                continue;
            }

            $url = $this->normalizePotentialUrl($value);
            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }

    private function extractCallCreatedAt(array $payload): ?CarbonImmutable
    {
        $candidate = $this->firstStringValue($payload, [
            'createdAt',
            'created_at',
            'startedAt',
            'started_at',
            'startAt',
            'start_at',
            'date',
            'createdOn',
            'creationDate',
            'callDate',
            'call_date',
            'timestamp',
            'eventTime',
            'time',
            'call.createdAt',
            'call.created_at',
            'call.startedAt',
            'call.started_at',
            'call.startAt',
            'call.start_at',
            'call.date',
            'data.createdAt',
            'data.created_at',
            'data.startedAt',
            'data.started_at',
            'data.startAt',
            'data.start_at',
            'data.date',
        ]);

        $parsed = $this->parseDate($candidate);
        if ($parsed !== null) {
            return $parsed;
        }

        return $this->searchDateByKeyPattern($payload);
    }

    private function searchDateByKeyPattern(array $payload): ?CarbonImmutable
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $nested = $this->searchDateByKeyPattern($value);
                if ($nested !== null) {
                    return $nested;
                }

                continue;
            }

            if (! is_string($key) || (! is_scalar($value) && $value !== null)) {
                continue;
            }

            if (preg_match('/(date|time|created|start|timestamp)/i', $key) !== 1) {
                continue;
            }

            $parsed = $this->parseDate((string) $value);
            if ($parsed !== null) {
                return $parsed;
            }
        }

        return null;
    }

    private function extractDurationSeconds(array $payload): ?int
    {
        $raw = $this->firstStringValue($payload, [
            'duration',
            'durationSeconds',
            'duration_seconds',
            'callDuration',
            'call_duration',
            'talkDuration',
            'talk_duration',
            'conversationDuration',
            'conversation_duration',
            'call.duration',
            'call.durationSeconds',
            'call.duration_seconds',
            'data.duration',
            'data.durationSeconds',
            'data.duration_seconds',
        ]);

        if ($raw === null) {
            return null;
        }

        return $this->parseDurationToSeconds($raw);
    }

    private function parseDurationToSeconds(string $raw): ?int
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return null;
        }

        if (is_numeric($trimmed)) {
            $seconds = (int) floor((float) $trimmed);

            return $seconds >= 0 ? $seconds : null;
        }

        if (preg_match('/^(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?$/', $trimmed, $matches) === 1) {
            if (isset($matches[3]) && $matches[3] !== '') {
                $hours = (int) $matches[1];
                $minutes = (int) $matches[2];
                $seconds = (int) $matches[3];

                return ($hours * 3600) + ($minutes * 60) + $seconds;
            }

            $minutes = (int) $matches[1];
            $seconds = (int) $matches[2];

            return ($minutes * 60) + $seconds;
        }

        return null;
    }
}

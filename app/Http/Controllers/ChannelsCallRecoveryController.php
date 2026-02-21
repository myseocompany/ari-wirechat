<?php

namespace App\Http\Controllers;

use App\Http\Requests\QueueChannelsRecoveriesRequest;
use App\Http\Requests\SearchChannelsCallsRequest;
use App\Jobs\RecoverChannelsCallRecording;
use App\Models\Action;
use App\Models\ChannelsCallRecovery;
use App\Models\ChannelsWebhookLog;
use App\Models\User;
use App\Services\ChannelsApiService;
use App\Services\ChannelsCallNormalizer;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ChannelsCallRecoveryController extends Controller
{
    public function index(
        SearchChannelsCallsRequest $request,
        ChannelsApiService $channelsApiService,
        ChannelsCallNormalizer $normalizer
    ): View {
        [$fromDate, $toDate] = $this->resolveDateRange($request->validated());

        $filters = [
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate->format('Y-m-d'),
            'call_id' => trim((string) $request->validated('call_id', '')),
            'agent_id' => trim((string) $request->validated('agent_id', '')),
            'msisdn' => trim((string) $request->validated('msisdn', '')),
            'only_missing' => $request->boolean('only_missing'),
        ];

        $calls = [];
        $summary = [
            'remote_total' => 0,
            'existing_local' => 0,
            'missing_local' => 0,
            'displayed' => 0,
        ];
        $searchError = null;
        $searched = true;

        if ($channelsApiService->isConfigured()) {
            try {
                $remoteCalls = $channelsApiService->listCalls($fromDate, $toDate, $filters);
                $remoteCalls = $this->applyLocalFilters($remoteCalls, $filters);

                [$calls, $summary] = $this->compareWithLocalData($remoteCalls, $fromDate, $toDate, $normalizer);

                if ($filters['only_missing']) {
                    $calls = array_values(array_filter($calls, function (array $call): bool {
                        return (bool) ($call['is_missing'] ?? false);
                    }));
                }

                $summary['displayed'] = count($calls);
            } catch (\Throwable $exception) {
                $searchError = $exception->getMessage();
                Log::warning('Channels calls recovery search failed.', [
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $recoveries = ChannelsCallRecovery::query()
            ->latest('updated_at')
            ->limit(80)
            ->get();

        return view('reports.views.channels_calls_recovery', [
            'searched' => $searched,
            'channelsConfigured' => $channelsApiService->isConfigured(),
            'filters' => $filters,
            'calls' => $calls,
            'summary' => $summary,
            'searchError' => $searchError,
            'recoveries' => $recoveries,
        ]);
    }

    public function queue(QueueChannelsRecoveriesRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $selectedIndexes = collect($validated['selected_indexes'] ?? [])
            ->map(fn ($value): int => (int) $value)
            ->unique()
            ->values();

        $calls = collect($validated['calls'] ?? []);
        $queued = 0;
        $alreadyProcessing = 0;

        foreach ($selectedIndexes as $index) {
            $call = $calls->get($index);
            if (! is_array($call)) {
                continue;
            }

            $callId = trim((string) ($call['call_id'] ?? ''));
            if ($callId === '') {
                continue;
            }

            $recovery = ChannelsCallRecovery::query()->firstOrNew(['call_id' => $callId]);
            if (in_array($recovery->status, [ChannelsCallRecovery::STATUS_QUEUED, ChannelsCallRecovery::STATUS_PROCESSING], true)) {
                $alreadyProcessing++;

                continue;
            }

            $recovery->fill([
                'call_created_at' => $this->parseDate((string) ($call['call_created_at'] ?? '')),
                'msisdn' => $this->normalizePhone((string) ($call['msisdn'] ?? '')),
                'agent_id' => trim((string) ($call['agent_id'] ?? '')) ?: null,
                'recording_exists' => filter_var($call['recording_exists'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'recording_url' => trim((string) ($call['recording_url'] ?? '')) ?: null,
                'status' => ChannelsCallRecovery::STATUS_QUEUED,
                'queued_at' => now(),
                'processed_at' => null,
                'recovered_at' => null,
                'error' => null,
                'payload' => $call,
            ]);
            $recovery->save();

            RecoverChannelsCallRecording::dispatch($recovery->id);
            $queued++;
        }

        $message = "Se encolaron {$queued} llamada(s) para recuperación.";
        if ($alreadyProcessing > 0) {
            $message .= " {$alreadyProcessing} ya estaban en cola o en proceso.";
        }

        return redirect()
            ->route('reports.channels_calls_recovery', $this->buildRedirectQuery($validated))
            ->with('status', $message);
    }

    private function resolveDateRange(array $validated): array
    {
        $todayUtc = CarbonImmutable::now('UTC');

        $fromDate = isset($validated['from_date']) && $validated['from_date'] !== null
            ? CarbonImmutable::parse((string) $validated['from_date'], 'UTC')->startOfDay()
            : $todayUtc->subDays(30)->startOfDay();

        $toDate = isset($validated['to_date']) && $validated['to_date'] !== null
            ? CarbonImmutable::parse((string) $validated['to_date'], 'UTC')->endOfDay()
            : $todayUtc->endOfDay();

        if ($toDate->lt($fromDate)) {
            $toDate = $fromDate->endOfDay();
        }

        return [$fromDate, $toDate];
    }

    private function applyLocalFilters(array $calls, array $filters): array
    {
        $callFilter = strtolower(trim((string) ($filters['call_id'] ?? '')));
        $agentFilter = trim((string) ($filters['agent_id'] ?? ''));
        $phoneFilter = preg_replace('/\D+/', '', (string) ($filters['msisdn'] ?? ''));
        $phoneFilter = is_string($phoneFilter) ? $phoneFilter : '';

        return array_values(array_filter($calls, function (array $call) use ($callFilter, $agentFilter, $phoneFilter): bool {
            if ($callFilter !== '' && ! str_contains(strtolower((string) ($call['call_id'] ?? '')), $callFilter)) {
                return false;
            }

            if ($agentFilter !== '' && (string) ($call['agent_id'] ?? '') !== $agentFilter) {
                return false;
            }

            if ($phoneFilter !== '') {
                $msisdn = preg_replace('/\D+/', '', (string) ($call['msisdn'] ?? ''));
                $msisdn = is_string($msisdn) ? $msisdn : '';
                if (! str_contains($msisdn, $phoneFilter)) {
                    return false;
                }
            }

            return true;
        }));
    }

    private function compareWithLocalData(
        array $calls,
        CarbonImmutable $fromDate,
        CarbonImmutable $toDate,
        ChannelsCallNormalizer $normalizer
    ): array {
        $webhookLogs = ChannelsWebhookLog::query()
            ->whereBetween('created_at', [$fromDate->subDays(2), $toDate->addDays(2)])
            ->get(['payload', 'payload_raw']);

        $knownCallIds = [];
        $knownRecordingUrls = [];
        $agentIdByCallId = [];

        foreach ($webhookLogs as $log) {
            $callId = $normalizer->extractCallIdFromWebhook($log->payload, (string) $log->payload_raw);
            if ($callId !== null) {
                $knownCallIds[strtolower($callId)] = true;

                $agentId = $normalizer->extractAgentIdFromWebhook($log->payload, (string) $log->payload_raw);
                if ($agentId !== null) {
                    $agentIdByCallId[strtolower($callId)] = $agentId;
                }
            }

            $recordingUrl = $normalizer->extractRecordingUrlFromWebhook($log->payload, (string) $log->payload_raw);
            $normalizedUrl = $normalizer->normalizeUrlForMatch($recordingUrl);
            if ($normalizedUrl !== null) {
                $knownRecordingUrls[$normalizedUrl] = true;
            }
        }

        $actionUrls = Action::query()
            ->whereNotNull('url')
            ->whereBetween('created_at', [$fromDate->subDays(2), $toDate->addDays(2)])
            ->where(function ($query): void {
                $query->where('note', 'like', '%Channels%')
                    ->orWhere('url', 'like', '%channels%');
            })
            ->pluck('url');

        $knownActionUrls = $actionUrls
            ->map(fn ($url): ?string => $normalizer->normalizeUrlForMatch((string) $url))
            ->filter()
            ->values()
            ->flip()
            ->all();

        $recoveredCalls = ChannelsCallRecovery::query()
            ->select(['call_id', 'recording_url'])
            ->where('status', ChannelsCallRecovery::STATUS_RECOVERED)
            ->whereNotNull('call_id')
            ->get();

        $recoveredCallIds = [];
        $recoveredRecordingUrls = [];

        foreach ($recoveredCalls as $recoveredCall) {
            $normalizedCallId = strtolower(trim((string) $recoveredCall->call_id));
            if ($normalizedCallId !== '') {
                $recoveredCallIds[$normalizedCallId] = true;
            }

            $normalizedRecordingUrl = $normalizer->normalizeUrlForMatch((string) $recoveredCall->recording_url);
            if ($normalizedRecordingUrl !== null) {
                $recoveredRecordingUrls[$normalizedRecordingUrl] = true;
            }
        }

        $rows = [];
        $existing = 0;
        $missing = 0;
        $agentUserMapByUsername = $this->resolveUsersByChannelsUsername($calls);

        foreach ($calls as $call) {
            $callId = strtolower((string) ($call['call_id'] ?? ''));
            $recordingUrl = $normalizer->normalizeUrlForMatch((string) ($call['recording_url'] ?? ''));

            $existsByCallId = $callId !== '' && isset($knownCallIds[$callId]);
            $existsByWebhookUrl = $recordingUrl !== null && isset($knownRecordingUrls[$recordingUrl]);
            $existsByActionUrl = $recordingUrl !== null && isset($knownActionUrls[$recordingUrl]);
            $existsByRecoveredCallId = $callId !== '' && isset($recoveredCallIds[$callId]);
            $existsByRecoveredRecordingUrl = $recordingUrl !== null && isset($recoveredRecordingUrls[$recordingUrl]);

            $localExists = $existsByCallId
                || $existsByWebhookUrl
                || $existsByActionUrl
                || $existsByRecoveredCallId
                || $existsByRecoveredRecordingUrl;
            $isMissing = (bool) ($call['recording_exists'] ?? false) && ! $localExists;

            if ($localExists) {
                $existing++;
            }

            if ($isMissing) {
                $missing++;
            }

            $sources = [];
            if ($existsByCallId) {
                $sources[] = 'webhook_call_id';
            }
            if ($existsByWebhookUrl) {
                $sources[] = 'webhook_recording_url';
            }
            if ($existsByActionUrl) {
                $sources[] = 'action_url';
            }
            if ($existsByRecoveredCallId) {
                $sources[] = 'recovery_call_id';
            }
            if ($existsByRecoveredRecordingUrl) {
                $sources[] = 'recovery_recording_url';
            }

            $rows[] = [
                'call_id' => (string) ($call['call_id'] ?? ''),
                'call_created_at' => $call['call_created_at'] instanceof CarbonImmutable
                    ? $call['call_created_at']->format('Y-m-d H:i:s')
                    : null,
                'msisdn' => $call['msisdn'] ?? null,
                'agent_id' => $this->resolveAgentIdentifier(
                    $call,
                    $agentIdByCallId[$callId] ?? null,
                    $agentUserMapByUsername
                ),
                'agent_username' => $call['agent_username'] ?? null,
                'agent_name' => $call['agent_name'] ?? null,
                'agent_surname' => $call['agent_surname'] ?? null,
                'agent_msisdn' => $call['agent_msisdn'] ?? null,
                'recording_exists' => (bool) ($call['recording_exists'] ?? false),
                'recording_url' => $call['recording_url'] ?? null,
                'status' => $call['status'] ?? null,
                'local_exists' => $localExists,
                'local_sources' => implode(', ', $sources),
                'is_missing' => $isMissing,
                'agent_debug_fields' => $this->collectAgentDebugFields($call['raw'] ?? []),
                'raw_json' => $this->encodePrettyJson($call['raw'] ?? []),
            ];
        }

        return [
            $rows,
            [
                'remote_total' => count($calls),
                'existing_local' => $existing,
                'missing_local' => $missing,
                'displayed' => count($rows),
            ],
        ];
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

    private function buildRedirectQuery(array $validated): array
    {
        return [
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
            'call_id' => $validated['call_id'] ?? null,
            'agent_id' => $validated['agent_id'] ?? null,
            'msisdn' => $validated['msisdn'] ?? null,
            'only_missing' => $validated['only_missing'] ?? false,
            'search' => 1,
        ];
    }

    private function resolveAgentIdentifier(
        array $call,
        ?string $fallbackWebhookAgentId,
        array $agentUserMapByUsername
    ): ?string {
        $agentId = trim((string) ($call['agent_id'] ?? ''));
        if ($agentId !== '') {
            return $agentId;
        }

        $fallback = trim((string) $fallbackWebhookAgentId);
        if ($fallback !== '') {
            return $fallback;
        }

        $normalizedUsername = $this->normalizeChannelsUsername((string) ($call['agent_username'] ?? ''));
        if ($normalizedUsername !== '' && isset($agentUserMapByUsername[$normalizedUsername])) {
            $matchedChannelsId = $agentUserMapByUsername[$normalizedUsername]['channels_id'] ?? null;
            if ($matchedChannelsId !== null) {
                return (string) $matchedChannelsId;
            }
        }

        return null;
    }

    private function resolveUsersByChannelsUsername(array $calls): array
    {
        $usernames = collect($calls)
            ->map(fn (array $call): string => $this->normalizeChannelsUsername((string) ($call['agent_username'] ?? '')))
            ->filter(fn (string $value): bool => $value !== '')
            ->unique()
            ->values();

        if ($usernames->isEmpty()) {
            return [];
        }

        return User::query()
            ->whereNotNull('channels_email')
            ->whereIn('channels_email', $usernames->all())
            ->select(['id', 'channels_id', 'channels_email'])
            ->get()
            ->mapWithKeys(function (User $user): array {
                $normalized = $this->normalizeChannelsUsername((string) $user->channels_email);
                if ($normalized === '') {
                    return [];
                }

                return [
                    $normalized => [
                        'id' => (int) $user->id,
                        'channels_id' => $user->channels_id !== null ? (int) $user->channels_id : null,
                    ],
                ];
            })
            ->all();
    }

    private function normalizeChannelsUsername(string $username): string
    {
        return strtolower(trim($username));
    }

    private function collectAgentDebugFields(mixed $payload): array
    {
        $results = [];
        $this->collectAgentDebugFieldsRecursive($payload, '', $results);

        ksort($results);

        return $results;
    }

    private function collectAgentDebugFieldsRecursive(mixed $payload, string $prefix, array &$results): void
    {
        if (! is_array($payload)) {
            return;
        }

        foreach ($payload as $key => $value) {
            $segment = is_string($key) ? $key : (string) $key;
            $path = $prefix === '' ? $segment : $prefix.'.'.$segment;

            if (is_array($value)) {
                $this->collectAgentDebugFieldsRecursive($value, $path, $results);

                continue;
            }

            if (! is_scalar($value) || ! is_string($segment)) {
                continue;
            }

            if (preg_match('/(agent|user|owner|seller|advisor|assignee)/i', $segment) !== 1) {
                continue;
            }

            $results[$path] = (string) $value;
        }
    }

    private function encodePrettyJson(mixed $value): string
    {
        if (! is_array($value)) {
            return '{}';
        }

        $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return is_string($encoded) ? $encoded : '{}';
    }
}

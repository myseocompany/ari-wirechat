<?php

namespace App\Http\Controllers;

use App\Http\Requests\QueueChannelsRecoveriesRequest;
use App\Http\Requests\SearchChannelsCallsRequest;
use App\Jobs\RecoverChannelsCallRecording;
use App\Models\Action;
use App\Models\ChannelsCallRecovery;
use App\Models\ChannelsWebhookLog;
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

        $rows = [];
        $existing = 0;
        $missing = 0;

        foreach ($calls as $call) {
            $callId = strtolower((string) ($call['call_id'] ?? ''));
            $recordingUrl = $normalizer->normalizeUrlForMatch((string) ($call['recording_url'] ?? ''));

            $existsByCallId = $callId !== '' && isset($knownCallIds[$callId]);
            $existsByWebhookUrl = $recordingUrl !== null && isset($knownRecordingUrls[$recordingUrl]);
            $existsByActionUrl = $recordingUrl !== null && isset($knownActionUrls[$recordingUrl]);

            $localExists = $existsByCallId || $existsByWebhookUrl || $existsByActionUrl;
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

            $rows[] = [
                'call_id' => (string) ($call['call_id'] ?? ''),
                'call_created_at' => $call['call_created_at'] instanceof CarbonImmutable
                    ? $call['call_created_at']->format('Y-m-d H:i:s')
                    : null,
                'msisdn' => $call['msisdn'] ?? null,
                'agent_id' => $call['agent_id'] ?? ($agentIdByCallId[$callId] ?? null),
                'recording_exists' => (bool) ($call['recording_exists'] ?? false),
                'recording_url' => $call['recording_url'] ?? null,
                'status' => $call['status'] ?? null,
                'local_exists' => $localExists,
                'local_sources' => implode(', ', $sources),
                'is_missing' => $isMissing,
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
}

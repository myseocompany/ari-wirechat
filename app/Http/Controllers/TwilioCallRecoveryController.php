<?php

namespace App\Http\Controllers;

use App\Http\Requests\QueueTwilioRecoveriesRequest;
use App\Http\Requests\SearchTwilioCallsRequest;
use App\Jobs\RecoverTwilioCallRecording;
use App\Models\Action;
use App\Models\TwilioCallRecovery;
use App\Services\TwilioRecoveryApiService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class TwilioCallRecoveryController extends Controller
{
    public function index(
        SearchTwilioCallsRequest $request,
        TwilioRecoveryApiService $twilioRecoveryApiService
    ): View {
        [$fromDate, $toDate] = $this->resolveDateRange($request->validated());

        $filters = [
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate->format('Y-m-d'),
            'call_sid' => trim((string) $request->validated('call_sid', '')),
            'msisdn' => trim((string) $request->validated('msisdn', '')),
            'status' => trim((string) $request->validated('status', '')),
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

        if ($twilioRecoveryApiService->isConfigured()) {
            try {
                $remoteCalls = $twilioRecoveryApiService->listCalls($fromDate, $toDate, $filters);
                $remoteCalls = $this->applyLocalFilters($remoteCalls, $filters);

                [$calls, $summary] = $this->compareWithLocalData($remoteCalls, $fromDate, $toDate, $twilioRecoveryApiService);

                if ($filters['only_missing']) {
                    $calls = array_values(array_filter($calls, function (array $call): bool {
                        return (bool) ($call['is_missing'] ?? false);
                    }));
                }

                $summary['displayed'] = count($calls);
            } catch (\Throwable $exception) {
                $searchError = $exception->getMessage();
                Log::warning('Twilio calls recovery search failed.', [
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $recoveries = TwilioCallRecovery::query()
            ->latest('updated_at')
            ->limit(80)
            ->get();

        return view('reports.views.twilio_calls_recovery', [
            'searched' => $searched,
            'twilioConfigured' => $twilioRecoveryApiService->isConfigured(),
            'filters' => $filters,
            'calls' => $calls,
            'summary' => $summary,
            'searchError' => $searchError,
            'recoveries' => $recoveries,
        ]);
    }

    public function queue(QueueTwilioRecoveriesRequest $request): RedirectResponse
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

            $callSid = trim((string) ($call['call_sid'] ?? ''));
            if ($callSid === '') {
                continue;
            }

            $recovery = TwilioCallRecovery::query()->firstOrNew(['call_sid' => $callSid]);
            if (in_array($recovery->status, [TwilioCallRecovery::STATUS_QUEUED, TwilioCallRecovery::STATUS_PROCESSING], true)) {
                $alreadyProcessing++;

                continue;
            }

            $recovery->fill([
                'call_created_at' => $this->parseDate((string) ($call['call_created_at'] ?? '')),
                'from_number' => $this->normalizePhone((string) ($call['from_number'] ?? '')),
                'to_number' => $this->normalizePhone((string) ($call['to_number'] ?? '')),
                'contact_msisdn' => $this->normalizePhone((string) ($call['contact_msisdn'] ?? '')),
                'direction' => trim((string) ($call['direction'] ?? '')) ?: null,
                'status_text' => trim((string) ($call['status_text'] ?? '')) ?: null,
                'duration_seconds' => is_numeric($call['duration_seconds'] ?? null) ? (int) $call['duration_seconds'] : null,
                'recording_exists' => filter_var($call['recording_exists'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'recording_sid' => trim((string) ($call['recording_sid'] ?? '')) ?: null,
                'recording_url' => trim((string) ($call['recording_url'] ?? '')) ?: null,
                'status' => TwilioCallRecovery::STATUS_QUEUED,
                'queued_at' => now(),
                'processed_at' => null,
                'recovered_at' => null,
                'error' => null,
                'payload' => $call,
            ]);
            $recovery->save();

            RecoverTwilioCallRecording::dispatch($recovery->id);
            $queued++;
        }

        $message = "Se encolaron {$queued} llamada(s) para recuperación.";
        if ($alreadyProcessing > 0) {
            $message .= " {$alreadyProcessing} ya estaban en cola o en proceso.";
        }

        return redirect()
            ->route('reports.twilio_calls_recovery', $this->buildRedirectQuery($validated))
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
        $callSidFilter = strtolower(trim((string) ($filters['call_sid'] ?? '')));
        $statusFilter = strtolower(trim((string) ($filters['status'] ?? '')));
        $phoneFilter = preg_replace('/\D+/', '', (string) ($filters['msisdn'] ?? ''));
        $phoneFilter = is_string($phoneFilter) ? $phoneFilter : '';

        return array_values(array_filter($calls, function (array $call) use ($callSidFilter, $statusFilter, $phoneFilter): bool {
            if ($callSidFilter !== '' && ! str_contains(strtolower((string) ($call['call_sid'] ?? '')), $callSidFilter)) {
                return false;
            }

            if ($statusFilter !== '' && ! str_contains(strtolower((string) ($call['status_text'] ?? '')), $statusFilter)) {
                return false;
            }

            if ($phoneFilter !== '') {
                $phones = [
                    $this->normalizePhone((string) ($call['from_number'] ?? '')),
                    $this->normalizePhone((string) ($call['to_number'] ?? '')),
                    $this->normalizePhone((string) ($call['contact_msisdn'] ?? '')),
                ];

                $matched = false;
                foreach ($phones as $phone) {
                    if ($phone !== null && str_contains($phone, $phoneFilter)) {
                        $matched = true;
                        break;
                    }
                }

                if (! $matched) {
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
        TwilioRecoveryApiService $twilioRecoveryApiService
    ): array {
        $actions = Action::query()
            ->where('type_id', 21)
            ->whereBetween('created_at', [$fromDate->subDays(3), now()->addDay()])
            ->where(function ($query): void {
                $query->where('note', 'like', '%twilio_call_sid:%')
                    ->orWhere('note', 'like', '%Twilio%')
                    ->orWhere('url', 'like', '%twilio.com%/Recordings/%');
            })
            ->get(['note', 'url']);

        $knownCallSids = [];
        $knownActionUrls = [];

        foreach ($actions as $action) {
            foreach ($this->extractTwilioCallSidsFromNote((string) $action->note) as $sid) {
                $knownCallSids[strtolower($sid)] = true;
            }

            $normalizedUrl = $twilioRecoveryApiService->normalizeUrlForMatch((string) $action->url);
            if ($normalizedUrl !== null) {
                $knownActionUrls[$normalizedUrl] = true;
            }
        }

        $recoveredCalls = TwilioCallRecovery::query()
            ->select(['call_sid', 'recording_url'])
            ->where('status', TwilioCallRecovery::STATUS_RECOVERED)
            ->whereNotNull('call_sid')
            ->get();

        $recoveredCallSids = [];
        $recoveredRecordingUrls = [];

        foreach ($recoveredCalls as $recoveredCall) {
            $normalizedCallSid = strtolower(trim((string) $recoveredCall->call_sid));
            if ($normalizedCallSid !== '') {
                $recoveredCallSids[$normalizedCallSid] = true;
            }

            $normalizedRecordingUrl = $twilioRecoveryApiService->normalizeUrlForMatch((string) $recoveredCall->recording_url);
            if ($normalizedRecordingUrl !== null) {
                $recoveredRecordingUrls[$normalizedRecordingUrl] = true;
            }
        }

        $rows = [];
        $existing = 0;
        $missing = 0;

        foreach ($calls as $call) {
            $callSid = strtolower((string) ($call['call_sid'] ?? ''));
            $recordingUrl = $twilioRecoveryApiService->normalizeUrlForMatch((string) ($call['recording_url'] ?? ''));

            $existsByCallSid = $callSid !== '' && isset($knownCallSids[$callSid]);
            $existsByActionUrl = $recordingUrl !== null && isset($knownActionUrls[$recordingUrl]);
            $existsByRecoveredCallSid = $callSid !== '' && isset($recoveredCallSids[$callSid]);
            $existsByRecoveredRecordingUrl = $recordingUrl !== null && isset($recoveredRecordingUrls[$recordingUrl]);

            $localExists = $existsByCallSid
                || $existsByActionUrl
                || $existsByRecoveredCallSid
                || $existsByRecoveredRecordingUrl;
            $isMissing = (bool) ($call['recording_exists'] ?? false) && ! $localExists;

            if ($localExists) {
                $existing++;
            }

            if ($isMissing) {
                $missing++;
            }

            $sources = [];
            if ($existsByCallSid) {
                $sources[] = 'action_call_sid';
            }
            if ($existsByActionUrl) {
                $sources[] = 'action_url';
            }
            if ($existsByRecoveredCallSid) {
                $sources[] = 'recovery_call_sid';
            }
            if ($existsByRecoveredRecordingUrl) {
                $sources[] = 'recovery_recording_url';
            }

            $rows[] = [
                'call_sid' => (string) ($call['call_sid'] ?? ''),
                'call_created_at' => $call['call_created_at'] instanceof CarbonImmutable
                    ? $call['call_created_at']->format('Y-m-d H:i:s')
                    : null,
                'from_number' => $call['from_number'] ?? null,
                'to_number' => $call['to_number'] ?? null,
                'contact_msisdn' => $call['contact_msisdn'] ?? null,
                'direction' => $call['direction'] ?? null,
                'status_text' => $call['status_text'] ?? null,
                'duration_seconds' => isset($call['duration_seconds']) ? (int) $call['duration_seconds'] : null,
                'recording_exists' => (bool) ($call['recording_exists'] ?? false),
                'recording_sid' => $call['recording_sid'] ?? null,
                'recording_url' => $call['recording_url'] ?? null,
                'local_exists' => $localExists,
                'local_sources' => implode(', ', $sources),
                'is_missing' => $isMissing,
                'raw_json' => $this->encodePrettyJson([
                    'call' => $call['raw'] ?? [],
                    'recording' => $call['recording_raw'] ?? null,
                ]),
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

    private function extractTwilioCallSidsFromNote(string $note): array
    {
        $sids = [];

        if (preg_match_all('/twilio_call_sid:([A-Za-z0-9]+)/i', $note, $matches) > 0) {
            foreach ($matches[1] as $sid) {
                $trimmed = trim((string) $sid);
                if ($trimmed !== '') {
                    $sids[] = $trimmed;
                }
            }
        }

        if (preg_match_all('/call[_\s]?sid[:=]\s*([A-Za-z0-9]+)/i', $note, $matches) > 0) {
            foreach ($matches[1] as $sid) {
                $trimmed = trim((string) $sid);
                if ($trimmed !== '') {
                    $sids[] = $trimmed;
                }
            }
        }

        return array_values(array_unique($sids));
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
            'call_sid' => $validated['call_sid'] ?? null,
            'msisdn' => $validated['msisdn'] ?? null,
            'status' => $validated['status'] ?? null,
            'only_missing' => $validated['only_missing'] ?? false,
            'search' => 1,
        ];
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

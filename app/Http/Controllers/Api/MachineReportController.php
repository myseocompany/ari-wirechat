<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MachineReportRequest;
use App\Models\Machine;
use App\Models\MachineFaultEvent;
use App\Models\MachineProductionMinute;
use App\Models\MachineReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class MachineReportController extends Controller
{
    private const MAX_FUTURE_SECONDS = 120;

    private const MAX_UNITS_PER_MINUTE = 100000;

    private const MAX_TACOMETER_JUMP = 1000000;

    public function store(MachineReportRequest $request): JsonResponse
    {
        /** @var Machine $machine */
        $machine = $request->attributes->get('machine');
        $payload = $request->validated();
        $receivedAt = now();

        if (! empty($payload['serial']) && $payload['serial'] !== $machine->serial) {
            return response()->json(['message' => 'Serial does not match token.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $reportLog = MachineReport::create([
            'machine_id' => $machine->id,
            'batch_id' => $payload['batch_id'] ?? null,
            'reported_at' => isset($payload['reported_at']) ? Carbon::parse($payload['reported_at']) : null,
            'received_at' => $receivedAt,
            'payload_json' => $payload,
            'raw_body' => $request->getContent(),
            'signature' => $request->header('X-Signature'),
        ]);

        $summary = [
            'ingested' => 0,
            'deduped' => 0,
            'rejected' => 0,
            'faults_ingested' => 0,
            'anomalies' => [],
        ];

        $connection = $machine->newQuery()->getConnection();
        $connection->transaction(function () use ($payload, $machine, $receivedAt, &$summary): void {
            foreach ($payload['reports'] as $index => $report) {
                $minuteAt = Carbon::parse($report['minute_at'])->startOfMinute();

                if ($minuteAt->greaterThan($receivedAt->copy()->addSeconds(self::MAX_FUTURE_SECONDS))) {
                    $summary['rejected']++;
                    $summary['anomalies'][] = [
                        'index' => $index,
                        'minute_at' => $report['minute_at'],
                        'reason' => 'timestamp_in_future',
                    ];

                    continue;
                }

                if ($report['units_in_minute'] < 0) {
                    $summary['rejected']++;
                    $summary['anomalies'][] = [
                        'index' => $index,
                        'minute_at' => $report['minute_at'],
                        'reason' => 'negative_units_in_minute',
                    ];

                    continue;
                }

                if ($report['units_in_minute'] > self::MAX_UNITS_PER_MINUTE) {
                    $summary['rejected']++;
                    $summary['anomalies'][] = [
                        'index' => $index,
                        'minute_at' => $report['minute_at'],
                        'reason' => 'units_in_minute_too_large',
                    ];

                    continue;
                }

                $existing = MachineProductionMinute::query()
                    ->where('machine_id', $machine->id)
                    ->where('minute_at', $minuteAt)
                    ->first();

                if ($existing) {
                    $summary['deduped']++;

                    continue;
                }

                $previous = MachineProductionMinute::query()
                    ->where('machine_id', $machine->id)
                    ->where('minute_at', '<', $minuteAt)
                    ->orderByDesc('minute_at')
                    ->first();

                if ($previous) {
                    $delta = $report['tacometer_total'] - $previous->tacometer_total;
                    if ($delta > self::MAX_TACOMETER_JUMP) {
                        $summary['rejected']++;
                        $summary['anomalies'][] = [
                            'index' => $index,
                            'minute_at' => $report['minute_at'],
                            'reason' => 'tacometer_jump_too_large',
                        ];

                        continue;
                    }
                }

                $isBackfill = $report['is_backfill']
                    ?? $minuteAt->lessThan($receivedAt->copy()->subMinutes(2));

                MachineProductionMinute::create([
                    'machine_id' => $machine->id,
                    'minute_at' => $minuteAt,
                    'tacometer_total' => $report['tacometer_total'],
                    'units_in_minute' => $report['units_in_minute'],
                    'is_backfill' => (bool) $isBackfill,
                    'received_at' => $receivedAt,
                ]);

                $summary['ingested']++;

                if ($previous && $report['tacometer_total'] < $previous->tacometer_total) {
                    MachineFaultEvent::create([
                        'machine_id' => $machine->id,
                        'fault_code' => 'TACOMETER_RESET',
                        'severity' => 'info',
                        'reported_at' => $minuteAt,
                        'metadata' => [
                            'previous_tacometer_total' => $previous->tacometer_total,
                            'current_tacometer_total' => $report['tacometer_total'],
                            'minute_at' => $minuteAt->toIso8601String(),
                        ],
                    ]);
                    $summary['faults_ingested']++;
                }

                if (! empty($report['faults'])) {
                    foreach ($report['faults'] as $fault) {
                        MachineFaultEvent::create([
                            'machine_id' => $machine->id,
                            'fault_code' => $fault['code'],
                            'severity' => $fault['severity'],
                            'reported_at' => isset($fault['reported_at'])
                                ? Carbon::parse($fault['reported_at'])
                                : $minuteAt,
                            'metadata' => $fault['metadata'] ?? null,
                        ]);
                        $summary['faults_ingested']++;
                    }
                }
            }

            if (! empty($payload['faults'])) {
                foreach ($payload['faults'] as $fault) {
                    MachineFaultEvent::create([
                        'machine_id' => $machine->id,
                        'fault_code' => $fault['code'],
                        'severity' => $fault['severity'],
                        'reported_at' => isset($fault['reported_at'])
                            ? Carbon::parse($fault['reported_at'])
                            : $receivedAt,
                        'metadata' => $fault['metadata'] ?? null,
                    ]);
                    $summary['faults_ingested']++;
                }
            }
        });

        $machine->forceFill(['last_seen_at' => $receivedAt])->save();

        return response()->json([
            'report_id' => $reportLog->id,
            'summary' => $summary,
        ]);
    }
}

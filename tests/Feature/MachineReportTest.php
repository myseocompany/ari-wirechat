<?php

use App\Models\Machine;
use App\Models\MachineFaultEvent;
use App\Models\MachineProductionMinute;
use App\Models\MachineToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeMachineToken(Machine $machine, string $plainToken): MachineToken
{
    return MachineToken::query()->create([
        'machine_id' => $machine->id,
        'token_hash' => hash('sha256', $plainToken),
    ]);
}

function makeHeaders(string $plainToken, array $payload): array
{
    $rawBody = json_encode($payload, JSON_THROW_ON_ERROR);

    return [
        'Authorization' => 'Bearer '.$plainToken,
        'X-Signature' => hash_hmac('sha256', $rawBody, $plainToken),
    ];
}

it('rejects missing auth', function () {
    $payload = [
        'reports' => [
            [
                'minute_at' => now()->startOfMinute()->toIso8601String(),
                'tacometer_total' => 100,
                'units_in_minute' => 1,
            ],
        ],
    ];

    $this->postJson('/api/v1/machines/report', $payload)->assertUnauthorized();
});

it('dedupes by machine and minute', function () {
    $machine = Machine::factory()->create();
    $plainToken = 'token-123';
    makeMachineToken($machine, $plainToken);

    $minute = now()->startOfMinute()->toIso8601String();

    $payload = [
        'reports' => [
            [
                'minute_at' => $minute,
                'tacometer_total' => 100,
                'units_in_minute' => 2,
            ],
            [
                'minute_at' => $minute,
                'tacometer_total' => 100,
                'units_in_minute' => 2,
            ],
        ],
    ];

    $response = $this->withHeaders(makeHeaders($plainToken, $payload))
        ->postJson('/api/v1/machines/report', $payload);

    $response->assertSuccessful()
        ->assertJsonPath('summary.ingested', 1)
        ->assertJsonPath('summary.deduped', 1);

    expect(MachineProductionMinute::query()->count())->toBe(1);
});

it('ingests a batch of minutes', function () {
    $machine = Machine::factory()->create();
    $plainToken = 'token-456';
    makeMachineToken($machine, $plainToken);

    $payload = [
        'batch_id' => 'batch-1',
        'reports' => [
            [
                'minute_at' => now()->subMinutes(2)->startOfMinute()->toIso8601String(),
                'tacometer_total' => 150,
                'units_in_minute' => 3,
                'is_backfill' => true,
            ],
            [
                'minute_at' => now()->subMinute()->startOfMinute()->toIso8601String(),
                'tacometer_total' => 153,
                'units_in_minute' => 3,
            ],
        ],
    ];

    $response = $this->withHeaders(makeHeaders($plainToken, $payload))
        ->postJson('/api/v1/machines/report', $payload);

    $response->assertSuccessful()
        ->assertJsonPath('summary.ingested', 2);

    expect(MachineProductionMinute::query()->count())->toBe(2);
});

it('detects tacometer reset and records event', function () {
    $machine = Machine::factory()->create();
    $plainToken = 'token-789';
    makeMachineToken($machine, $plainToken);

    MachineProductionMinute::query()->create([
        'machine_id' => $machine->id,
        'minute_at' => now()->subMinutes(2)->startOfMinute(),
        'tacometer_total' => 500,
        'units_in_minute' => 5,
        'is_backfill' => false,
        'received_at' => now()->subMinutes(2),
    ]);

    $payload = [
        'reports' => [
            [
                'minute_at' => now()->subMinute()->startOfMinute()->toIso8601String(),
                'tacometer_total' => 10,
                'units_in_minute' => 1,
            ],
        ],
    ];

    $this->withHeaders(makeHeaders($plainToken, $payload))
        ->postJson('/api/v1/machines/report', $payload)
        ->assertSuccessful();

    expect(MachineFaultEvent::query()->where('fault_code', 'TACOMETER_RESET')->count())->toBe(1);
});

it('ingests fault events', function () {
    $machine = Machine::factory()->create();
    $plainToken = 'token-abc';
    makeMachineToken($machine, $plainToken);

    $payload = [
        'reports' => [
            [
                'minute_at' => now()->startOfMinute()->toIso8601String(),
                'tacometer_total' => 100,
                'units_in_minute' => 1,
                'faults' => [
                    [
                        'code' => 'F-101',
                        'severity' => 'high',
                        'metadata' => ['source' => 'plc'],
                    ],
                ],
            ],
        ],
    ];

    $this->withHeaders(makeHeaders($plainToken, $payload))
        ->postJson('/api/v1/machines/report', $payload)
        ->assertSuccessful();

    expect(MachineFaultEvent::query()->where('fault_code', 'F-101')->count())->toBe(1);
});

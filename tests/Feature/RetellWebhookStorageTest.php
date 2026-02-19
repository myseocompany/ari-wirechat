<?php

use App\Jobs\RetellProcessCall;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

test('stores retell payload in inbox and dispatches processing job', function () {
    Queue::fake();

    $payload = [
        'event' => 'call_analyzed',
        'call' => [
            'call_id' => 'retell-call-123',
            'call_status' => 'ended',
            'from_number' => '+573001234567',
            'to_number' => '+573009876543',
            'duration_ms' => 54000,
        ],
    ];

    $response = $this->postJson('/api/retell-action', $payload);

    $response->assertSuccessful();

    $this->assertDatabaseHas('retell_inbox', [
        'call_id' => 'retell-call-123',
        'status' => 'ended',
    ]);

    $stored = DB::table('retell_inbox')->where('call_id', 'retell-call-123')->first();
    expect($stored)->not->toBeNull();
    expect(json_decode((string) $stored->payload, true))->toBe($payload);

    Queue::assertPushedOn('webhooks', RetellProcessCall::class);
    Queue::assertPushed(RetellProcessCall::class, function (RetellProcessCall $job) use ($payload): bool {
        return $job->data === $payload;
    });
});

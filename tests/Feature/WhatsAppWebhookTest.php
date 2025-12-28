<?php

use App\Models\Customer;
use App\Models\LeadAssignmentLog;
use App\Models\User;
use App\Models\WhatsAppMessageMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Namu\WireChat\Models\Message;

uses(RefreshDatabase::class);

test('verifies the whatsapp webhook challenge', function () {
    config(['whatsapp.verify_token' => 'test-token']);

    $response = $this->get('/api/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=test-token&hub.challenge=12345');

    $response->assertStatus(200);
    $response->assertSeeText('12345');
});

test('stores inbound messages and dedupes by external id', function () {
    config(['whatsapp.verify_token' => 'test-token']);
    config(['whatsapp.sellerchat_webhook_url' => 'https://panel.sellerchat.ai/api/whatsapp/webhook/test']);
    Http::fake();

    $systemUser = User::factory()->create();
    config(['whatsapp.system_user_id' => $systemUser->id]);

    $customer = Customer::create([
        'name' => 'Lead WhatsApp',
        'phone' => '+57 300 1234567',
    ]);

    $payload = [
        'object' => 'whatsapp_business_account',
        'entry' => [
            [
                'id' => 'entry-id',
                'changes' => [
                    [
                        'field' => 'messages',
                        'value' => [
                            'messages' => [
                                [
                                    'from' => '573001234567',
                                    'id' => 'wamid.HBgMTIzNDU2Nzg5',
                                    'timestamp' => '1734567890',
                                    'type' => 'text',
                                    'text' => [
                                        'body' => 'Hola desde WhatsApp',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $this->postJson('/api/webhooks/whatsapp', $payload)->assertStatus(200);
    $this->postJson('/api/webhooks/whatsapp', $payload)->assertStatus(200);

    expect(WhatsAppMessageMap::count())->toBe(1);
    expect(Message::count())->toBe(1);

    $storedMessage = Message::first();
    expect($storedMessage)->not->toBeNull();
    expect($storedMessage->sendable_id)->toBe($customer->id);

    Http::assertSentCount(2);
});

test('creates a new customer with default status, source and assignment when phone is unknown', function () {
    config(['whatsapp.verify_token' => 'test-token']);
    config(['whatsapp.sellerchat_webhook_url' => 'https://panel.sellerchat.ai/api/whatsapp/webhook/test']);
    Http::fake();

    $systemUser = User::factory()->create();
    config(['whatsapp.system_user_id' => $systemUser->id]);

    $assignee = User::factory()->create();
    $assignee->forceFill([
        'assignable' => 1,
        'status_id' => 1,
        'last_assigned' => 0,
    ])->save();

    $payload = [
        'object' => 'whatsapp_business_account',
        'entry' => [
            [
                'id' => 'entry-id',
                'changes' => [
                    [
                        'field' => 'messages',
                        'value' => [
                            'messages' => [
                                [
                                    'from' => '573009999999',
                                    'id' => 'wamid.HBgMTIzOTk5OTk5',
                                    'timestamp' => '1734567890',
                                    'type' => 'text',
                                    'text' => [
                                        'body' => 'Nuevo lead',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $this->postJson('/api/webhooks/whatsapp', $payload)->assertStatus(200);

    $customer = Customer::where('phone', '573009999999')->first();
    expect($customer)->not->toBeNull();
    expect($customer->status_id)->toBe(1);
    expect($customer->source_id)->toBe(79);
    expect($customer->user_id)->toBe($assignee->id);

    expect(LeadAssignmentLog::count())->toBe(1);
    $assignmentLog = LeadAssignmentLog::first();
    expect($assignmentLog->context)->toBe('whatsapp_webhook');
    expect($assignmentLog->meta)->toBe([
        'strategy' => 'auto',
        'source_id' => 79,
    ]);
});

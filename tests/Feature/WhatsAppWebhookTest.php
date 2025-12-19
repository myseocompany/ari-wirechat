<?php

use App\Models\Customer;
use App\Models\User;
use App\Models\WhatsAppMessageMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Namu\WireChat\Models\Message;

uses(RefreshDatabase::class);

test('verifies the whatsapp webhook challenge', function () {
    config(['whatsapp.verify_token' => 'test-token']);

    $response = $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=test-token&hub.challenge=12345');

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

    $this->postJson('/webhooks/whatsapp', $payload)->assertStatus(200);
    $this->postJson('/webhooks/whatsapp', $payload)->assertStatus(200);

    expect(WhatsAppMessageMap::count())->toBe(1);
    expect(Message::count())->toBe(1);

    $storedMessage = Message::first();
    expect($storedMessage)->not->toBeNull();
    expect($storedMessage->sendable_id)->toBe($customer->id);

    Http::assertSentCount(2);
});

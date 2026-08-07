<?php

use App\Jobs\ProcessAriCrmMirrorDelivery;
use App\Models\AriCrmMirrorDelivery;
use App\Models\AriCrmMirrorIntegration;
use App\Models\MessageSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function mirrorIntegration(string $tenantId, string $secret = 'mirror-test-secret'): AriCrmMirrorIntegration
{
    $source = MessageSource::query()->create([
        'name' => 'AriCRM mirror test',
        'type' => 'whatsapp',
        'APIKEY' => 'unused-test-key',
        'settings' => [],
    ]);

    return AriCrmMirrorIntegration::query()->create([
        'aricrm_tenant_id' => $tenantId,
        'message_source_id' => $source->id,
        'secret_current' => $secret,
        'is_active' => true,
    ]);
}

function mirrorPayload(string $tenantId, string $messageId): array
{
    return [
        'event' => 'message.inbound',
        'tenant_id' => $tenantId,
        'contact_id' => '11111111-1111-4111-8111-111111111111',
        'conversation_id' => '22222222-2222-4222-8222-222222222222',
        'message_id' => $messageId,
        'phone' => '573001112233',
        'name' => 'Contacto AriCRM',
        'direction' => 'inbound',
        'body' => 'Hola',
        'message_type' => 'text',
        'media' => null,
        'author' => ['type' => 'contact', 'id' => '11111111-1111-4111-8111-111111111111', 'name' => 'Contacto AriCRM'],
        'sent_at' => '2026-08-07T10:00:00-05:00',
        'occurred_at' => '2026-08-07T10:00:01-05:00',
        'metadata' => [],
    ];
}

function mirrorHeaders(array $payload, string $deliveryId, string $secret, ?string $body = null): array
{
    $body ??= json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $timestamp = (string) now()->timestamp;

    return [
        'X-AriCRM-Webhook-Version' => '1',
        'X-AriCRM-Event' => $payload['event'],
        'X-AriCRM-Delivery' => $deliveryId,
        'X-AriCRM-Timestamp' => $timestamp,
        'X-AriCRM-Signature' => 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, $secret),
    ];
}

test('accepts a signed delivery and queues it once', function () {
    Queue::fake();
    $tenantId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    mirrorIntegration($tenantId);
    $payload = mirrorPayload($tenantId, '33333333-3333-4333-8333-333333333333');
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $headers = mirrorHeaders($payload, '44444444-4444-4444-8444-444444444444', 'mirror-test-secret', $body);

    $this->call('POST', '/api/v1/integrations/aricrm/messages', [], [], [], array_merge(['CONTENT_TYPE' => 'application/json'], collect($headers)->mapWithKeys(fn ($value, $key) => ['HTTP_'.str_replace('-', '_', $key) => $value])->all()), $body)
        ->assertStatus(202)
        ->assertJson(['accepted' => true]);

    expect(AriCrmMirrorDelivery::count())->toBe(1);
    Queue::assertPushed(ProcessAriCrmMirrorDelivery::class);
});

test('rejects an altered body without persisting a delivery', function () {
    Queue::fake();
    $tenantId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    mirrorIntegration($tenantId);
    $payload = mirrorPayload($tenantId, '33333333-3333-4333-8333-333333333333');
    $signedBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $payload['body'] = 'Alterado';
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $headers = mirrorHeaders($payload, '44444444-4444-4444-8444-444444444444', 'mirror-test-secret', $signedBody);

    $this->call('POST', '/api/v1/integrations/aricrm/messages', [], [], [], array_merge(['CONTENT_TYPE' => 'application/json'], collect($headers)->mapWithKeys(fn ($value, $key) => ['HTTP_'.str_replace('-', '_', $key) => $value])->all()), $body)->assertUnauthorized();

    expect(AriCrmMirrorDelivery::count())->toBe(0);
    Queue::assertNothingPushed();
});

test('returns an idempotent response for a repeated delivery', function () {
    Queue::fake();
    $tenantId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    mirrorIntegration($tenantId);
    $payload = mirrorPayload($tenantId, '33333333-3333-4333-8333-333333333333');
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $headers = mirrorHeaders($payload, '44444444-4444-4444-8444-444444444444', 'mirror-test-secret', $body);
    $server = array_merge(['CONTENT_TYPE' => 'application/json'], collect($headers)->mapWithKeys(fn ($value, $key) => ['HTTP_'.str_replace('-', '_', $key) => $value])->all());

    $this->call('POST', '/api/v1/integrations/aricrm/messages', [], [], [], $server, $body)->assertStatus(202);
    $this->call('POST', '/api/v1/integrations/aricrm/messages', [], [], [], $server, $body)->assertOk()->assertJson(['accepted' => true, 'duplicate' => true]);

    expect(AriCrmMirrorDelivery::count())->toBe(1);
    Queue::assertPushed(ProcessAriCrmMirrorDelivery::class, 1);
});

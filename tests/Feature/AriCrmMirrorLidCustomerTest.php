<?php

use App\Jobs\ProcessAriCrmMirrorDelivery;
use App\Models\AriCrmMirrorContact;
use App\Models\AriCrmMirrorDelivery;
use App\Models\AriCrmMirrorIntegration;
use App\Models\Customer;
use App\Models\CustomerStatus;
use App\Models\MessageSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function lidIntegration(): AriCrmMirrorIntegration
{
    $source = MessageSource::query()->create([
        'name' => 'AriCRM mirror lid test',
        'type' => 'whatsapp',
        'APIKEY' => 'unused-test-key',
        'settings' => [],
    ]);

    CustomerStatus::query()->create(['name' => 'Nuevo', 'stage_id' => 1]);

    return AriCrmMirrorIntegration::query()->create([
        'aricrm_tenant_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'message_source_id' => $source->id,
        'secret_current' => 'mirror-test-secret',
        'is_active' => true,
    ]);
}

function lidDelivery(AriCrmMirrorIntegration $integration, string $contactId, array $overrides = []): AriCrmMirrorDelivery
{
    $payload = array_merge([
        'event' => 'message.inbound',
        'tenant_id' => $integration->aricrm_tenant_id,
        'contact_id' => $contactId,
        'conversation_id' => (string) Str::uuid(),
        'message_id' => (string) Str::uuid(),
        'phone' => null,
        'wa_id' => null,
        'name' => 'Contacto LID',
        'direction' => 'inbound',
        'body' => 'Hola',
        'message_type' => 'text',
        'media' => null,
        'author' => ['type' => 'contact', 'id' => $contactId, 'name' => 'Contacto LID'],
        'sent_at' => '2026-10-25T10:00:00-05:00',
        'occurred_at' => '2026-10-25T10:00:01-05:00',
        'metadata' => [],
    ], $overrides);

    return AriCrmMirrorDelivery::query()->create([
        'integration_id' => $integration->id,
        'delivery_id' => (string) Str::uuid(),
        'event' => 'message.inbound',
        'aricrm_message_id' => $payload['message_id'],
        'payload' => $payload,
        'status' => 'accepted',
    ]);
}

it('stores the LID in wa_id and leaves phone empty', function () {
    $integration = lidIntegration();

    $delivery = lidDelivery($integration, '11111111-1111-4111-8111-111111111111', [
        'wa_id' => 'CO.1766162541355187',
    ]);
    (new ProcessAriCrmMirrorDelivery($delivery->id))->handle(app(App\Services\MessageSourceConversationService::class));

    $customer = Customer::query()->sole();
    expect($customer->phone)->toBeNull()
        ->and($customer->wa_id)->toBe('CO.1766162541355187')
        ->and($customer->name)->toBe('Contacto LID');

    $mapping = AriCrmMirrorContact::query()->sole();
    expect($mapping->customer_id)->toBe($customer->id)
        ->and($mapping->normalized_phone)->toBeNull();
});

it('routes legacy LID digits received in phone to wa_id', function () {
    $integration = lidIntegration();

    $delivery = lidDelivery($integration, '11111111-1111-4111-8111-111111111111', [
        'phone' => '1766162541355187',
    ]);
    (new ProcessAriCrmMirrorDelivery($delivery->id))->handle(app(App\Services\MessageSourceConversationService::class));

    $customer = Customer::query()->sole();
    expect($customer->phone)->toBeNull()
        ->and($customer->wa_id)->toBe('1766162541355187');
});

it('keeps phone and wa_id together when both are known', function () {
    $integration = lidIntegration();

    $delivery = lidDelivery($integration, '11111111-1111-4111-8111-111111111111', [
        'phone' => '573001112233',
        'wa_id' => 'CO.1766162541355187',
    ]);
    (new ProcessAriCrmMirrorDelivery($delivery->id))->handle(app(App\Services\MessageSourceConversationService::class));

    $customer = Customer::query()->sole();
    expect($customer->phone)->toBe('573001112233')
        ->and($customer->wa_id)->toBe('CO.1766162541355187');
});

it('backfills the phone on an existing LID-only customer once AriCRM resolves it', function () {
    $integration = lidIntegration();

    $first = lidDelivery($integration, '11111111-1111-4111-8111-111111111111', [
        'wa_id' => 'CO.1766162541355187',
    ]);
    (new ProcessAriCrmMirrorDelivery($first->id))->handle(app(App\Services\MessageSourceConversationService::class));

    $second = lidDelivery($integration, '11111111-1111-4111-8111-111111111111', [
        'phone' => '573001112233',
        'wa_id' => 'CO.1766162541355187',
    ]);
    (new ProcessAriCrmMirrorDelivery($second->id))->handle(app(App\Services\MessageSourceConversationService::class));

    expect(Customer::count())->toBe(1);
    $customer = Customer::query()->sole();
    expect($customer->phone)->toBe('573001112233')
        ->and($customer->wa_id)->toBe('CO.1766162541355187');
});

it('reuses a customer matched by wa_id for a new AriCRM contact mapping', function () {
    $integration = lidIntegration();

    $first = lidDelivery($integration, '11111111-1111-4111-8111-111111111111', [
        'wa_id' => 'CO.1766162541355187',
    ]);
    (new ProcessAriCrmMirrorDelivery($first->id))->handle(app(App\Services\MessageSourceConversationService::class));

    // Otro contact_id de AriCRM que resuelve a la misma identidad de WhatsApp.
    $second = lidDelivery($integration, '55555555-5555-4555-8555-555555555555', [
        'wa_id' => 'CO.1766162541355187',
    ]);
    (new ProcessAriCrmMirrorDelivery($second->id))->handle(app(App\Services\MessageSourceConversationService::class));

    expect(Customer::count())->toBe(1)
        ->and(AriCrmMirrorContact::count())->toBe(2);
});

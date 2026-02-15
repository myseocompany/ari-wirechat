<?php

use App\Models\Action;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('voip page requires authentication', function () {
    $this->get('/voip')->assertRedirect(route('login'));
});

test('authenticated users can request a voip token', function () {
    config([
        'services.twilio.account_sid' => 'test-account-sid',
        'services.twilio.api_key_sid' => 'test-api-key-sid',
        'services.twilio.api_key_secret' => 'secret-token-key',
        'services.twilio.twiml_app_sid' => 'test-twiml-app-sid',
    ]);

    $user = User::factory()->create([
        'name' => 'Agente Prueba',
    ]);

    $response = $this->actingAs($user)->postJson('/voip/token');

    $response
        ->assertOk()
        ->assertJsonStructure(['token', 'identity', 'twiml_url']);

    $token = $response->json('token');
    $payload = json_decode(base64UrlDecode(explode('.', $token)[1]), true);

    expect($payload['iss'])->toBe('test-api-key-sid');
    expect($payload['sub'])->toBe('test-account-sid');
    expect($payload['grants']['identity'])->toBe(Str::limit('agente-prueba-'.$user->id, 80, ''));
    expect($payload['grants']['voice']['outgoing']['application_sid'])->toBe('test-twiml-app-sid');
});

test('twiml endpoint returns dial response', function () {
    config([
        'services.twilio.caller_id' => '+14155550100',
    ]);

    $response = $this->post('/api/voip/twiml', [
        'to' => '+573001234567',
    ]);

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    $response->assertSee('<Dial callerId="+14155550100">', false);
    $response->assertSee('<Number>+573001234567</Number>', false);
});

test('twiml endpoint prioritizes explicit destination over Twilio To param', function () {
    config([
        'services.twilio.caller_id' => '+14155550100',
    ]);

    $response = $this->post('/api/voip/twiml', [
        'To' => '+14155550000',
        'to' => '+573001234567',
    ]);

    $response->assertOk();
    $response->assertSee('<Number>+573001234567</Number>', false);
});

test('twiml endpoint renders direct twiml when destination is not explicit', function () {
    config([
        'services.twilio.caller_id' => '+14155550100',
    ]);

    $response = $this->post('/api/voip/twiml', [
        'To' => '+573001234567',
    ]);

    $response->assertOk();
    $response->assertSee('<Response>', false);
    $response->assertDontSee('<Dial', false);
});

test('authenticated users can trigger an outbound call from server', function () {
    config([
        'services.twilio.account_sid' => 'test-account-sid',
        'services.twilio.auth_token' => 'test-auth-token',
        'services.twilio.caller_id' => '+14155550100',
    ]);

    Http::fake([
        'https://api.twilio.com/*' => Http::response([
            'sid' => 'call-sid-001',
            'status' => 'queued',
        ], 201),
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/voip/call', [
        'to' => '+573001234567',
    ]);

    $response
        ->assertOk()
        ->assertJson([
            'call_sid' => 'call-sid-001',
            'status' => 'queued',
            'to' => '+573001234567',
            'from' => '+14155550100',
        ]);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.twilio.com/2010-04-01/Accounts/test-account-sid/Calls.json'
            && $request['To'] === '+573001234567'
            && $request['From'] === '+14155550100'
            && $request['Method'] === 'POST'
            && str_ends_with((string) $request['Url'], '/api/voip/twiml');
    });
});

test('advisor can start customer call and create call action from crm', function () {
    config([
        'services.twilio.account_sid' => 'test-account-sid',
        'services.twilio.auth_token' => 'test-auth-token',
        'services.twilio.caller_id' => '+14155550100',
        'services.twilio.webhook_secret' => 'secret-webhook',
    ]);

    Http::fake([
        'https://api.twilio.com/*' => Http::response([
            'sid' => 'CA11111111111111111111111111111111',
            'status' => 'queued',
        ], 201),
    ]);

    $advisor = User::factory()->create();
    $customer = Customer::create([
        'name' => 'Cliente Prueba',
        'phone' => '3001234567',
        'user_id' => $advisor->id,
    ]);

    $response = $this->actingAs($advisor)->postJson("/customers/{$customer->id}/voip/call");

    $response->assertOk();
    $response->assertJson([
        'call_sid' => 'CA11111111111111111111111111111111',
        'to' => '+573001234567',
        'from' => '+14155550100',
    ]);

    $actionId = $response->json('action_id');
    expect($actionId)->toBeInt();

    $action = Action::query()->find($actionId);
    expect($action)->not->toBeNull();
    expect($action?->type_id)->toBe(21);
    expect($action?->customer_id)->toBe($customer->id);
    expect($action?->note)->toContain('[twilio_call_sid:CA11111111111111111111111111111111]');

    Http::assertSent(function ($request) use ($actionId) {
        return $request->url() === 'https://api.twilio.com/2010-04-01/Accounts/test-account-sid/Calls.json'
            && $request['To'] === '+573001234567'
            && $request['From'] === '+14155550100'
            && $request['Method'] === 'POST'
            && str_ends_with((string) $request['Url'], '/api/voip/twiml')
            && $request['Record'] === 'true'
            && str_contains((string) $request['StatusCallback'], '/api/voip/callbacks/status?')
            && str_contains((string) $request['StatusCallback'], 'action_id='.$actionId)
            && str_contains((string) $request['StatusCallback'], 'token=secret-webhook')
            && str_contains((string) $request['RecordingStatusCallback'], '/api/voip/callbacks/recording?')
            && str_contains((string) $request['RecordingStatusCallback'], 'action_id='.$actionId)
            && str_contains((string) $request['RecordingStatusCallback'], 'token=secret-webhook');
    });
});

test('advisor can prepare customer call action for web softphone', function () {
    config([
        'services.twilio.account_sid' => 'test-account-sid',
        'services.twilio.auth_token' => 'test-auth-token',
        'services.twilio.caller_id' => '+14155550100',
    ]);

    Http::fake();

    $advisor = User::factory()->create();
    $customer = Customer::create([
        'name' => 'Cliente softphone',
        'phone' => '3001234567',
        'user_id' => $advisor->id,
    ]);

    $response = $this->actingAs($advisor)->postJson("/customers/{$customer->id}/voip/call", [
        'to' => '+573001234567',
        'client' => true,
    ]);

    $response->assertOk();
    $response->assertJson([
        'message' => 'Acción de llamada preparada.',
        'to' => '+573001234567',
        'from' => '+14155550100',
    ]);

    $actionId = $response->json('action_id');
    expect($actionId)->toBeInt();

    $action = Action::query()->find($actionId);
    expect($action)->not->toBeNull();
    expect($action?->type_id)->toBe(21);
    expect($action?->customer_id)->toBe($customer->id);
    expect($action?->note)->toContain('Destino: +573001234567');

    Http::assertNothingSent();
});

test('recording callback updates action audio url', function () {
    config([
        'services.twilio.account_sid' => 'test-account-sid',
        'services.twilio.webhook_secret' => 'secret-webhook',
    ]);

    $customer = Customer::create([
        'name' => 'Cliente callback',
    ]);
    $creator = User::factory()->create();

    $action = Action::create([
        'customer_id' => $customer->id,
        'type_id' => 21,
        'note' => 'Llamada [twilio_call_sid:CA22222222222222222222222222222222]',
        'creator_user_id' => $creator->id,
    ]);

    $response = $this->post("/api/voip/callbacks/recording?action_id={$action->id}&token=secret-webhook", [
        'AccountSid' => 'test-account-sid',
        'CallSid' => 'CA22222222222222222222222222222222',
        'RecordingSid' => 'RE22222222222222222222222222222222',
        'RecordingStatus' => 'completed',
        'RecordingUrl' => 'https://api.twilio.com/2010-04-01/Accounts/test-account-sid/Recordings/RE22222222222222222222222222222222',
    ]);

    $response->assertOk();

    $action->refresh();
    expect($action->url)->toBe(
        'https://api.twilio.com/2010-04-01/Accounts/test-account-sid/Recordings/RE22222222222222222222222222222222.mp3'
    );
    expect($action->note)->toContain('Grabación Twilio: completed');
});

test('status callback can persist recording url using dial call sid fallback', function () {
    config([
        'services.twilio.account_sid' => 'test-account-sid',
        'services.twilio.auth_token' => 'test-auth-token',
        'services.twilio.webhook_secret' => 'secret-webhook',
    ]);

    Http::fake([
        'https://api.twilio.com/2010-04-01/Accounts/test-account-sid/Recordings.json*' => Http::response([
            'recordings' => [
                [
                    'uri' => '/2010-04-01/Accounts/test-account-sid/Recordings/REaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.json',
                ],
            ],
        ], 200),
    ]);

    $customer = Customer::create([
        'name' => 'Cliente status callback',
    ]);
    $creator = User::factory()->create();

    $action = Action::create([
        'customer_id' => $customer->id,
        'type_id' => 21,
        'note' => 'Llamada inicial sin callback de grabación',
        'creator_user_id' => $creator->id,
    ]);

    $response = $this->post("/api/voip/callbacks/status?action_id={$action->id}&token=secret-webhook", [
        'AccountSid' => 'test-account-sid',
        'DialCallSid' => 'CAbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
        'DialCallStatus' => 'completed',
        'DialCallDuration' => 19,
    ]);

    $response->assertOk();

    $action->refresh();

    expect($action->creation_seconds)->toBe(19);
    expect($action->note)->toContain('[twilio_call_sid:CAbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb]');
    expect($action->note)->toContain('Estado Twilio: completed');
    expect($action->note)->toContain('Grabación Twilio: completed');
    expect($action->url)->toBe('https://api.twilio.com/2010-04-01/Accounts/test-account-sid/Recordings/REaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.mp3');
});

test('status callback accepts webhook token even when account sid differs', function () {
    config([
        'services.twilio.account_sid' => 'test-account-sid',
        'services.twilio.webhook_secret' => 'secret-webhook',
    ]);

    $customer = Customer::create([
        'name' => 'Cliente status token',
    ]);
    $creator = User::factory()->create();

    $action = Action::create([
        'customer_id' => $customer->id,
        'type_id' => 21,
        'note' => 'Llamada iniciada',
        'creator_user_id' => $creator->id,
    ]);

    $response = $this->post("/api/voip/callbacks/status?action_id={$action->id}&token=secret-webhook", [
        'AccountSid' => 'ACsubaccount0000000000000000000000',
        'CallSid' => 'CA33333333333333333333333333333333',
        'CallStatus' => 'completed',
        'CallDuration' => 11,
    ]);

    $response->assertOk();

    $action->refresh();
    expect($action->note)->toContain('Estado Twilio: completed');
    expect($action->creation_seconds)->toBe(11);
    expect($action->delivery_date)->not->toBeNull();
});

test('recording callback accepts webhook token even when account sid differs', function () {
    config([
        'services.twilio.account_sid' => 'test-account-sid',
        'services.twilio.webhook_secret' => 'secret-webhook',
    ]);

    $customer = Customer::create([
        'name' => 'Cliente recording token',
    ]);
    $creator = User::factory()->create();

    $action = Action::create([
        'customer_id' => $customer->id,
        'type_id' => 21,
        'note' => 'Llamada iniciada',
        'creator_user_id' => $creator->id,
    ]);

    $response = $this->post("/api/voip/callbacks/recording?action_id={$action->id}&token=secret-webhook", [
        'AccountSid' => 'ACsubaccount0000000000000000000000',
        'CallSid' => 'CA44444444444444444444444444444444',
        'RecordingSid' => 'RE44444444444444444444444444444444',
        'RecordingStatus' => 'completed',
        'RecordingUrl' => 'https://api.twilio.com/2010-04-01/Accounts/test-account-sid/Recordings/RE44444444444444444444444444444444',
    ]);

    $response->assertOk();

    $action->refresh();
    expect($action->note)->toContain('Grabación Twilio: completed');
    expect($action->url)->toBe('https://api.twilio.com/2010-04-01/Accounts/test-account-sid/Recordings/RE44444444444444444444444444444444.mp3');
    expect($action->delivery_date)->not->toBeNull();
});

test('status callback resolves action by destination when action id is missing', function () {
    config([
        'services.twilio.account_sid' => 'test-account-sid',
        'services.twilio.webhook_secret' => 'secret-webhook',
    ]);

    $customer = Customer::create([
        'name' => 'Cliente destino fallback',
    ]);
    $creator = User::factory()->create();

    $action = Action::create([
        'customer_id' => $customer->id,
        'type_id' => 21,
        'note' => 'Llamada Twilio iniciada por Asesor. Línea: +14155550100. Destino: +573001234567.',
        'creator_user_id' => $creator->id,
        'created_at' => now()->subMinutes(5),
    ]);

    $response = $this->post('/api/voip/callbacks/status?token=secret-webhook', [
        'AccountSid' => 'test-account-sid',
        'CallSid' => 'CA55555555555555555555555555555555',
        'CallStatus' => 'completed',
        'CallDuration' => 7,
        'To' => '+573001234567',
    ]);

    $response->assertOk();

    $action->refresh();
    expect($action->note)->toContain('[twilio_call_sid:CA55555555555555555555555555555555]');
    expect($action->note)->toContain('Estado Twilio: completed');
    expect($action->creation_seconds)->toBe(7);
    expect($action->delivery_date)->not->toBeNull();
});

function base64UrlDecode(string $value): string
{
    $padding = strlen($value) % 4;
    if ($padding > 0) {
        $value .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode(strtr($value, '-_', '+/'), true);

    return $decoded === false ? '' : $decoded;
}

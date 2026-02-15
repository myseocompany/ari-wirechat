<?php

use App\Models\Action;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Http;

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

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/voip/token', [
        'identity' => 'agent_ventas_01',
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure(['token', 'identity', 'twiml_url']);

    $token = $response->json('token');
    $payload = json_decode(base64UrlDecode(explode('.', $token)[1]), true);

    expect($payload['iss'])->toBe('test-api-key-sid');
    expect($payload['sub'])->toBe('test-account-sid');
    expect($payload['grants']['identity'])->toBe('agent_ventas_01');
    expect($payload['grants']['voice']['outgoing']['application_sid'])->toBe('test-twiml-app-sid');
});

test('twiml endpoint returns dial response', function () {
    config([
        'services.twilio.caller_id' => '+14155550100',
    ]);

    $response = $this->post('/api/voip/twiml', [
        'To' => '+573001234567',
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

test('twiml endpoint returns message when destination is missing', function () {
    config([
        'services.twilio.caller_id' => '+14155550100',
    ]);

    $response = $this->post('/api/voip/twiml');

    $response->assertOk();
    $response->assertSee('No destination number was provided.');
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

    $response = $this->actingAs($advisor)->postJson("/customers/{$customer->id}/voip/call", [
        'agent_phone' => '+573009999999',
    ]);

    $response->assertOk();
    $response->assertJson([
        'call_sid' => 'CA11111111111111111111111111111111',
        'to' => '+573001234567',
        'agent_phone' => '+573009999999',
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
            && $request['To'] === '+573009999999'
            && $request['From'] === '+14155550100'
            && $request['Method'] === 'POST'
            && str_contains((string) $request['Url'], '/api/voip/twiml?')
            && str_contains((string) $request['Url'], 'to=%2B573001234567')
            && str_contains((string) $request['Url'], 'action_id='.$actionId)
            && str_contains((string) $request['StatusCallback'], '/api/voip/callbacks/status?')
            && str_contains((string) $request['StatusCallback'], 'action_id='.$actionId)
            && str_contains((string) $request['StatusCallback'], 'token=secret-webhook');
    });
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

function base64UrlDecode(string $value): string
{
    $padding = strlen($value) % 4;
    if ($padding > 0) {
        $value .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode(strtr($value, '-_', '+/'), true);

    return $decoded === false ? '' : $decoded;
}

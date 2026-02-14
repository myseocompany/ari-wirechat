<?php

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

function base64UrlDecode(string $value): string
{
    $padding = strlen($value) % 4;
    if ($padding > 0) {
        $value .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode(strtr($value, '-_', '+/'), true);

    return $decoded === false ? '' : $decoded;
}

<?php

use App\Models\Action;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('audio proxy route requires authentication', function () {
    $action = Action::create([
        'customer_id' => 1,
        'type_id' => 21,
        'note' => 'Llamada',
        'url' => 'https://api.twilio.com/2010-04-01/Accounts/AC123/Recordings/RE123',
    ]);

    $this->get(route('actions.audio', $action))->assertRedirect(route('login'));
});

test('owner can stream twilio recording through proxy', function () {
    config([
        'services.twilio.account_sid' => 'ACtest123',
        'services.twilio.auth_token' => 'authtoken123',
    ]);

    Http::fake([
        'https://api.twilio.com/*' => Http::response('FAKEAUDIO', 200, [
            'Content-Type' => 'audio/mpeg',
        ]),
    ]);

    $user = User::factory()->create();
    $customer = Customer::create([
        'name' => 'Cliente audio',
        'phone' => '3001234567',
        'user_id' => $user->id,
    ]);

    $action = Action::create([
        'customer_id' => $customer->id,
        'type_id' => 21,
        'note' => 'Llamada grabada',
        'url' => 'https://api.twilio.com/2010-04-01/Accounts/ACtest123/Recordings/REtest123',
        'creator_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('actions.audio', $action));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'audio/mpeg');
    expect($response->getContent())->toBe('FAKEAUDIO');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.twilio.com/2010-04-01/Accounts/ACtest123/Recordings/REtest123.mp3'
            && $request->hasHeader('Authorization')
            && str_starts_with((string) ($request->header('Authorization')[0] ?? ''), 'Basic ');
    });
});

test('non owner cannot stream twilio recording through proxy', function () {
    config([
        'services.twilio.account_sid' => 'ACtest123',
        'services.twilio.auth_token' => 'authtoken123',
    ]);

    Http::fake();

    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $customer = Customer::create([
        'name' => 'Cliente privado',
        'phone' => '3001234567',
        'user_id' => $owner->id,
    ]);

    $action = Action::create([
        'customer_id' => $customer->id,
        'type_id' => 21,
        'note' => 'Llamada grabada',
        'url' => 'https://api.twilio.com/2010-04-01/Accounts/ACtest123/Recordings/REtest999',
        'creator_user_id' => $owner->id,
    ]);

    $this->actingAs($otherUser)
        ->get(route('actions.audio', $action))
        ->assertForbidden();

    Http::assertNothingSent();
});

test('audio proxy returns gateway timeout when twilio connection times out', function () {
    config([
        'services.twilio.account_sid' => 'ACtest123',
        'services.twilio.auth_token' => 'authtoken123',
    ]);

    Http::fake([
        'https://api.twilio.com/*' => Http::failedConnection(),
    ]);

    $user = User::factory()->create();
    $customer = Customer::create([
        'name' => 'Cliente timeout',
        'phone' => '3001234567',
        'user_id' => $user->id,
    ]);

    $action = Action::create([
        'customer_id' => $customer->id,
        'type_id' => 21,
        'note' => 'Llamada grabada',
        'url' => 'https://api.twilio.com/2010-04-01/Accounts/ACtest123/Recordings/REtimeout123',
        'creator_user_id' => $user->id,
    ]);

    $this->withoutExceptionHandling()
        ->actingAs($user)
        ->get(route('actions.audio', $action))
        ->assertStatus(504);
});

test('audio proxy returns service unavailable when twilio credentials are rejected', function () {
    config([
        'services.twilio.account_sid' => 'ACtest123',
        'services.twilio.auth_token' => 'badtoken',
    ]);

    Http::fake([
        'https://api.twilio.com/*' => Http::response('Unauthorized', 401),
    ]);

    $user = User::factory()->create();
    $customer = Customer::create([
        'name' => 'Cliente auth',
        'phone' => '3001234567',
        'user_id' => $user->id,
    ]);

    $action = Action::create([
        'customer_id' => $customer->id,
        'type_id' => 21,
        'note' => 'Llamada grabada',
        'url' => 'https://api.twilio.com/2010-04-01/Accounts/ACtest123/Recordings/REunauthorized123',
        'creator_user_id' => $user->id,
    ]);

    $this->withoutExceptionHandling()
        ->actingAs($user)
        ->get(route('actions.audio', $action))
        ->assertStatus(503);
});

test('audio proxy forwards range header to twilio', function () {
    config([
        'services.twilio.account_sid' => 'ACtest123',
        'services.twilio.auth_token' => 'authtoken123',
    ]);

    Http::fake([
        'https://api.twilio.com/*' => Http::response('PARTIAL', 206, [
            'Content-Type' => 'audio/mpeg',
            'Content-Range' => 'bytes 0-6/9999',
            'Accept-Ranges' => 'bytes',
            'Content-Length' => '7',
        ]),
    ]);

    $user = User::factory()->create();
    $customer = Customer::create([
        'name' => 'Cliente range',
        'phone' => '3001234567',
        'user_id' => $user->id,
    ]);

    $action = Action::create([
        'customer_id' => $customer->id,
        'type_id' => 21,
        'note' => 'Llamada grabada',
        'url' => 'https://api.twilio.com/2010-04-01/Accounts/ACtest123/Recordings/RErange123',
        'creator_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('actions.audio', $action), [
        'Range' => 'bytes=0-6',
    ]);

    $response->assertStatus(206);
    $response->assertHeader('Content-Range', 'bytes 0-6/9999');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.twilio.com/2010-04-01/Accounts/ACtest123/Recordings/RErange123.mp3'
            && ($request->header('Range')[0] ?? null) === 'bytes=0-6';
    });
});

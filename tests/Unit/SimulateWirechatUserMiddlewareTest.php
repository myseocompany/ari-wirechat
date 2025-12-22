<?php

use App\Http\Middleware\SimulateWirechatUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimulatedUserProvider
{
    public static ?User $user = null;

    public static function query(): object
    {
        return new class
        {
            public function find(int $id): ?User
            {
                return SimulatedUserProvider::$user;
            }
        };
    }
}

it('simulates the configured wirechat user', function () {
    $simulatedUser = User::factory()->make(['id' => 1001]);
    $actingUser = User::factory()->make(['id' => 2002]);

    config()->set('wirechat.simulated_user_id', $simulatedUser->id);
    config()->set('wirechat.user_model', SimulatedUserProvider::class);
    SimulatedUserProvider::$user = $simulatedUser;

    Auth::setUser($actingUser);

    $middleware = new SimulateWirechatUser;
    $request = Request::create('/chats', 'GET');
    $request->setUserResolver(static fn () => Auth::user());

    $response = $middleware->handle($request, function (Request $request) use ($simulatedUser) {
        expect(auth()->id())->toBe($simulatedUser->id);
        expect($request->user()?->id)->toBe($simulatedUser->id);

        return response('ok');
    });

    expect($response->getStatusCode())->toBe(200);
});

it('keeps the authenticated user when wirechat simulation is disabled', function () {
    $actingUser = User::factory()->make(['id' => 3003]);

    config()->set('wirechat.simulated_user_id', null);

    Auth::setUser($actingUser);

    $middleware = new SimulateWirechatUser;
    $request = Request::create('/chats', 'GET');
    $request->setUserResolver(static fn () => Auth::user());

    $response = $middleware->handle($request, function (Request $request) use ($actingUser) {
        expect(auth()->id())->toBe($actingUser->id);
        expect($request->user()?->id)->toBe($actingUser->id);

        return response('ok');
    });

    expect($response->getStatusCode())->toBe(200);
});

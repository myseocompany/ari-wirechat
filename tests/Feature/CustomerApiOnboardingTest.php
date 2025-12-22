<?php

use App\Models\Customer;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\mock;

it('onboards a new customer created via the api', function () {
    Mail::fake();

    $user = User::factory()->create([
        'status_id' => 1,
        'assignable' => 1,
        'email' => 'asesor@example.com',
    ]);

    mock(WhatsAppService::class)
        ->shouldReceive('sendTemplateToCustomer')
        ->once();

    $payload = [
        'name' => 'Lead de Prueba',
        'phone' => '3001234567',
        'email' => 'lead@example.com',
        'user_id' => $user->id,
    ];

    $response = $this->get('/api/customers/saveCustomer?'.http_build_query($payload));

    $response->assertSuccessful();
    $response->assertJson([
        'created' => true,
    ]);

    $customer = Customer::where('email', 'lead@example.com')->first();

    expect($customer)->not->toBeNull();
    expect($customer->status_id)->toBe(20);
});

<?php

use App\Models\Customer;
use App\Models\User;

it('expands to all dates when default date range has no results', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $customer = new Customer([
        'name' => 'Cliente Uno',
        'email' => 'cliente@example.com',
        'phone' => '3001234567',
        'country' => 'CO',
        'created_at' => now()->subDays(10),
        'updated_at' => now()->subDays(10),
    ]);
    $customer->user_id = $user->id;
    $customer->save();

    $response = $this->actingAs($user)->get(route('customers.index'));

    $response->assertOk();
    $response->assertSee('Cliente Uno');
    $response->assertDontSee('No se encontraron prospectos', false);
});

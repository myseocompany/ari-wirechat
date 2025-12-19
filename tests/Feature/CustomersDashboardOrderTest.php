<?php

use App\Models\Customer;
use App\Models\CustomerStatus;
use App\Models\User;

it('renders the dashboard above the customers list', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $status = new CustomerStatus;
    $status->name = 'Nuevo';
    $status->color = '#111111';
    $status->weight = 1;
    $status->status_id = 1;
    $status->save();

    $customer = new Customer([
        'name' => 'Cliente Uno',
        'email' => 'cliente@example.com',
        'phone' => '3001234567',
        'country' => 'CO',
        'created_at' => now()->subHour(),
        'updated_at' => now()->subHour(),
    ]);
    $customer->status_id = $status->id;
    $customer->user_id = $user->id;
    $customer->save();

    $response = $this->actingAs($user)->get(route('customers.index'));

    $response->assertOk();
    $response->assertSeeInOrder(['class="groupbar', 'Registro'], false);
    $response->assertDontSee('id="side_content"', false);
    $response->assertSee('data-filter-open', false);
    $response->assertSee('class="layout-full"', false);
});

<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Namu\WireChat\Models\Message;

uses(RefreshDatabase::class);

it('shows wirechat messages tied to the customer conversations', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $customer = Customer::create([
        'name' => 'Cliente WireChat',
        'phone' => '+57 300 0000000',
        'user_id' => $user->id,
    ]);

    $conversation = $user->createConversationWith($customer);

    Message::create([
        'conversation_id' => $conversation->id,
        'sendable_type' => $customer->getMorphClass(),
        'sendable_id' => $customer->id,
        'body' => 'Mensaje entrante',
    ]);

    $this->actingAs($user)
        ->get(route('customers.show', $customer))
        ->assertSuccessful()
        ->assertSee('Mensaje entrante');
});

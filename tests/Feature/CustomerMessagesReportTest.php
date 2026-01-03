<?php

use App\Models\Customer;
use App\Models\CustomerStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Namu\WireChat\Models\Message;

uses(RefreshDatabase::class);

it('orders customers by message count', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $status = CustomerStatus::create([
        'name' => 'Nuevo',
        'stage_id' => 1,
    ]);

    $lowVolumeCustomer = Customer::create([
        'name' => 'Cliente Poco Mensajes',
        'phone' => '+57 300 0000001',
        'user_id' => $user->id,
        'status_id' => $status->id,
    ]);

    $highVolumeCustomer = Customer::create([
        'name' => 'Cliente Muchos Mensajes',
        'phone' => '+57 300 0000002',
        'user_id' => $user->id,
        'status_id' => $status->id,
    ]);

    $lowConversation = $user->createConversationWith($lowVolumeCustomer);
    $highConversation = $user->createConversationWith($highVolumeCustomer);

    Message::create([
        'conversation_id' => $lowConversation->id,
        'sendable_type' => $lowVolumeCustomer->getMorphClass(),
        'sendable_id' => $lowVolumeCustomer->id,
        'body' => 'Primer mensaje',
    ]);

    Message::create([
        'conversation_id' => $highConversation->id,
        'sendable_type' => $highVolumeCustomer->getMorphClass(),
        'sendable_id' => $highVolumeCustomer->id,
        'body' => 'Mensaje 1',
    ]);

    Message::create([
        'conversation_id' => $highConversation->id,
        'sendable_type' => $highVolumeCustomer->getMorphClass(),
        'sendable_id' => $highVolumeCustomer->id,
        'body' => 'Mensaje 2',
    ]);

    Message::create([
        'conversation_id' => $highConversation->id,
        'sendable_type' => $highVolumeCustomer->getMorphClass(),
        'sendable_id' => $highVolumeCustomer->id,
        'body' => 'Mensaje 3',
    ]);

    Message::create([
        'conversation_id' => $highConversation->id,
        'sendable_type' => $highVolumeCustomer->getMorphClass(),
        'sendable_id' => $highVolumeCustomer->id,
        'body' => 'Mensaje 4',
    ]);

    Message::create([
        'conversation_id' => $highConversation->id,
        'sendable_type' => $highVolumeCustomer->getMorphClass(),
        'sendable_id' => $highVolumeCustomer->id,
        'body' => 'Mensaje 5',
    ]);

    Message::create([
        'conversation_id' => $highConversation->id,
        'sendable_type' => $highVolumeCustomer->getMorphClass(),
        'sendable_id' => $highVolumeCustomer->id,
        'body' => 'Mensaje 6',
    ]);

    Message::create([
        'conversation_id' => $highConversation->id,
        'sendable_type' => $user->getMorphClass(),
        'sendable_id' => $user->id,
        'body' => 'Mensaje asesor',
    ]);

    $this->actingAs($user)
        ->get('/reports/views/customers_messages_count')
        ->assertSuccessful()
        ->assertSeeInOrder([
            'Cliente Muchos Mensajes',
            'Cliente Poco Mensajes',
        ]);

    $this->actingAs($user)
        ->get('/reports/views/customers_messages_count')
        ->assertSee('Nuevo')
        ->assertSee($user->name)
        ->assertSee('Mensaje 6');
});

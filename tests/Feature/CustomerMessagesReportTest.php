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
    $statusTwo = CustomerStatus::create([
        'name' => 'Calificado',
        'stage_id' => 1,
    ]);

    $lowVolumeCustomer = Customer::create([
        'name' => 'Cliente Poco Mensajes',
        'phone' => '+57 300 0000001',
        'user_id' => $user->id,
        'status_id' => $statusTwo->id,
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

    Message::create([
        'conversation_id' => $highConversation->id,
        'sendable_type' => $user->getMorphClass(),
        'sendable_id' => $user->id,
        'body' => null,
        'type' => 'image',
    ]);

    $filteredCustomer = Customer::create([
        'name' => 'Cliente Filtrado',
        'phone' => '+57 300 0000003',
        'user_id' => $user->id,
        'status_id' => $status->id,
    ]);

    $filteredConversation = $user->createConversationWith($filteredCustomer);

    Message::create([
        'conversation_id' => $filteredConversation->id,
        'sendable_type' => $filteredCustomer->getMorphClass(),
        'sendable_id' => $filteredCustomer->id,
        'body' => 'Mensaje filtrado 1',
    ]);

    Message::create([
        'conversation_id' => $filteredConversation->id,
        'sendable_type' => $filteredCustomer->getMorphClass(),
        'sendable_id' => $filteredCustomer->id,
        'body' => 'Mensaje filtrado 2',
    ]);

    Message::create([
        'conversation_id' => $filteredConversation->id,
        'sendable_type' => $filteredCustomer->getMorphClass(),
        'sendable_id' => $filteredCustomer->id,
        'body' => 'Palabra clave',
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
        ->assertSee('[image]')
        ->assertSee('Mensaje asesor')
        ->assertSee('Mensaje 6');

    $this->actingAs($user)
        ->get('/reports/views/customers_messages_count?messages_min=3')
        ->assertSee('Cliente Muchos Mensajes')
        ->assertDontSee('Cliente Filtrado');

    $this->actingAs($user)
        ->get('/reports/views/customers_messages_count?message_search=Palabra')
        ->assertSee('Cliente Filtrado')
        ->assertDontSee('Cliente Poco Mensajes');

    $this->actingAs($user)
        ->get('/reports/views/customers_messages_count?status_ids[]='.$status->id)
        ->assertSee('Cliente Muchos Mensajes')
        ->assertDontSee('Cliente Poco Mensajes');
});

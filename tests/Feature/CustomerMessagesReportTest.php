<?php

use App\Models\Action;
use App\Models\ActionType;
use App\Models\Customer;
use App\Models\CustomerStatus;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Namu\WireChat\Models\Message;

uses(RefreshDatabase::class);

it('orders customers by message count', function () {
    Carbon::setTestNow(Carbon::parse('2026-02-16 10:00:00'));

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

    $tag = Tag::create([
        'name' => 'VIP',
    ]);

    $highVolumeCustomer->tags()->attach($tag->id);

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

    $unassignedCustomer = Customer::create([
        'name' => 'Cliente Sin Asesor',
        'phone' => '+57 300 0000007',
        'user_id' => null,
        'status_id' => $status->id,
    ]);

    $unassignedConversation = $user->createConversationWith($unassignedCustomer);

    Message::create([
        'conversation_id' => $unassignedConversation->id,
        'sendable_type' => $unassignedCustomer->getMorphClass(),
        'sendable_id' => $unassignedCustomer->id,
        'body' => 'Mensaje sin asesor',
    ]);

    $actionType = ActionType::query()->create([
        'name' => 'Seguimiento',
    ]);

    Action::query()->create([
        'customer_id' => $highVolumeCustomer->id,
        'creator_user_id' => $user->id,
        'type_id' => $actionType->id,
        'note' => 'Accion reciente',
        'created_at' => now()->subDays(5),
        'updated_at' => now()->subDays(5),
    ]);

    Action::query()->create([
        'customer_id' => $lowVolumeCustomer->id,
        'creator_user_id' => $user->id,
        'type_id' => $actionType->id,
        'note' => 'Accion antigua',
        'created_at' => now()->subDays(70),
        'updated_at' => now()->subDays(70),
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

    $this->actingAs($user)
        ->get('/reports/views/customers_messages_count?tag_ids[]='.$tag->id)
        ->assertSee('Cliente Muchos Mensajes')
        ->assertDontSee('Cliente Poco Mensajes');

    $this->actingAs($user)
        ->get('/reports/views/customers_messages_count?tag_none=1')
        ->assertSee('Cliente Poco Mensajes')
        ->assertDontSee('Cliente Muchos Mensajes');

    $this->actingAs($user)
        ->get('/reports/views/customers_messages_count?without_actions_last_60_days=1')
        ->assertSee('Cliente Poco Mensajes')
        ->assertSee('Cliente Filtrado')
        ->assertDontSee('Cliente Muchos Mensajes')
        ->assertDontSee('Cliente Sin Asesor');

    Carbon::setTestNow();
});

it('renders multiline action note as a single action item', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $status = CustomerStatus::create([
        'name' => 'Nuevo',
        'stage_id' => 1,
    ]);

    $customer = Customer::create([
        'name' => 'Cliente Con Nota Larga',
        'phone' => '+57 300 0000004',
        'user_id' => $user->id,
        'status_id' => $status->id,
    ]);

    $conversation = $user->createConversationWith($customer);

    Message::create([
        'conversation_id' => $conversation->id,
        'sendable_type' => $customer->getMorphClass(),
        'sendable_id' => $customer->id,
        'body' => 'Mensaje inicial',
    ]);

    $actionType = ActionType::query()->create([
        'name' => 'Whatsapp de salida',
    ]);

    Action::query()->create([
        'customer_id' => $customer->id,
        'creator_user_id' => $user->id,
        'type_id' => $actionType->id,
        'note' => "Primera linea\nSegunda linea",
    ]);

    $response = $this->actingAs($user)->get('/reports/views/customers_messages_count');

    $response->assertSuccessful()
        ->assertSee('Primera linea')
        ->assertSee('Segunda linea');

    expect(substr_count($response->getContent(), 'rounded-full bg-[linear-gradient(135deg,#1c2640,#0f172a)]'))->toBe(1);
});

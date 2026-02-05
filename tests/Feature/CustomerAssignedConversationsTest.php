<?php

use App\Models\Customer;
use App\Models\User;
use Namu\WireChat\Enums\MessageType;
use Namu\WireChat\Models\Message;

test('shows only conversations for customers assigned to the logged user', function () {
    $agent = User::factory()->create(['role_id' => 2]);
    $otherAgent = User::factory()->create();
    $systemUser = User::factory()->create();

    $assignedCustomer = Customer::create([
        'name' => 'Cliente Asignado',
        'phone' => '3001112233',
    ]);
    $assignedCustomer->forceFill(['user_id' => $agent->id])->save();

    $otherCustomer = Customer::create([
        'name' => 'Cliente Otro',
        'phone' => '3004445566',
    ]);
    $otherCustomer->forceFill(['user_id' => $otherAgent->id])->save();

    $assignedConversation = $systemUser->createConversationWith($assignedCustomer);
    $otherConversation = $systemUser->createConversationWith($otherCustomer);

    Message::create([
        'conversation_id' => $assignedConversation->id,
        'sendable_type' => $assignedCustomer->getMorphClass(),
        'sendable_id' => $assignedCustomer->id,
        'body' => 'Mensaje del cliente asignado',
        'type' => MessageType::TEXT->value,
    ]);

    Message::create([
        'conversation_id' => $otherConversation->id,
        'sendable_type' => $otherCustomer->getMorphClass(),
        'sendable_id' => $otherCustomer->id,
        'body' => 'Mensaje de otro cliente',
        'type' => MessageType::TEXT->value,
    ]);

    $assignedConversation->touch();
    $otherConversation->touch();

    $response = $this->actingAs($agent)->get(route('customer-chats'));

    $response->assertSuccessful();
    $response->assertSee('Cliente Asignado');
    $response->assertDontSee('Cliente Otro');
});

test('shows all customer conversations for role id 1', function () {
    $admin = User::factory()->create(['role_id' => 1]);
    $agent = User::factory()->create(['role_id' => 2]);
    $otherAgent = User::factory()->create();
    $systemUser = User::factory()->create();

    $assignedCustomer = Customer::create([
        'name' => 'Cliente Agente',
        'phone' => '3001110000',
    ]);
    $assignedCustomer->forceFill(['user_id' => $agent->id])->save();

    $otherCustomer = Customer::create([
        'name' => 'Cliente Otro Agente',
        'phone' => '3002220000',
    ]);
    $otherCustomer->forceFill(['user_id' => $otherAgent->id])->save();

    $assignedConversation = $systemUser->createConversationWith($assignedCustomer);
    $otherConversation = $systemUser->createConversationWith($otherCustomer);

    Message::create([
        'conversation_id' => $assignedConversation->id,
        'sendable_type' => $assignedCustomer->getMorphClass(),
        'sendable_id' => $assignedCustomer->id,
        'body' => 'Mensaje cliente agente',
        'type' => MessageType::TEXT->value,
    ]);

    Message::create([
        'conversation_id' => $otherConversation->id,
        'sendable_type' => $otherCustomer->getMorphClass(),
        'sendable_id' => $otherCustomer->id,
        'body' => 'Mensaje cliente otro agente',
        'type' => MessageType::TEXT->value,
    ]);

    $assignedConversation->touch();
    $otherConversation->touch();

    $response = $this->actingAs($admin)->get(route('customer-chats'));

    $response->assertSuccessful();
    $response->assertSee('Cliente Agente');
    $response->assertSee('Cliente Otro Agente');
});

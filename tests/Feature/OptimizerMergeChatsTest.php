<?php

use App\Models\Customer;
use App\Models\User;
use Namu\WireChat\Models\Message;
use Namu\WireChat\Models\Participant;

it('consolidates wirechat participants and messages when merging duplicates', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $winner = new Customer([
        'name' => 'Cliente Winner',
        'email' => 'winner@example.com',
    ]);
    $winner->save();

    $duplicate = new Customer([
        'name' => 'Cliente Duplicate',
        'email' => 'duplicate@example.com',
    ]);
    $duplicate->save();

    $conversation = $user->createConversationWith($duplicate);

    $message = Message::create([
        'sendable_id' => $duplicate->id,
        'sendable_type' => $duplicate->getMorphClass(),
        'conversation_id' => $conversation->id,
        'body' => 'Hola desde duplicado',
    ]);

    $response = $this->actingAs($user)->post(route('optimizer.merge'), [
        'customer_id' => $winner->id,
        'customer_id_all' => [$winner->id, $duplicate->id],
        'query' => 'cliente',
    ]);

    $response->assertRedirect();

    $message->refresh();
    expect($message->sendable_id)->toBe($winner->id);

    $participantIds = Participant::withoutGlobalScopes()
        ->where('conversation_id', $conversation->id)
        ->where('participantable_type', $winner->getMorphClass())
        ->pluck('participantable_id');

    expect($participantIds)->toContain($winner->id)
        ->not->toContain($duplicate->id);

    expect(Customer::find($duplicate->id))->toBeNull();
});

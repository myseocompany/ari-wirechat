<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StartChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_chat_endpoint_creates_conversation()
    {
        // Crear un usuario logueado
        $user = User::find(8);
        $this->actingAs($user); // Simula login

        // Crear un cliente
        $customer = Customer::find(654374);

        $response = $this->postJson('/customers/start-chat', [
            'customer_id' => $customer->id,
            'mensaje' => 'Hola desde el test!'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'chat_url',
                     'conversation_id',
                 ]);
    }
}

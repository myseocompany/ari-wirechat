<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\MessageSource;

class WAToolBoxWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Asegura que las rutas de API se cargan en el entorno del test
        $this->app['router']->middleware('api')->group(base_path('routes/api.php'));
    }

    public function test_webhook_post_creates_message_successfully()
    {
        // Simular un usuario y un message source real
        $apikey = 'II([:{~Lm}+FXA}$Hmc+90`ZBVca[Wo42}a.(bg1sX!Oo5)X';

       

        

        $payload = [
            'id' => 'false_573125407247@c.us_3AE13517C554529265BA',
            'type' => 'chat',
            'user' => '573125407247@c.us',
            'phone' => '573125407247',
            'content' => 'Holaaaa',
            'name' => 'Juanda',
            'name2' => 'Juanda',
            'image' => 'https://example.com/fake.jpg',
            'APIKEY' => $apikey,
        ];

        $response = $this->postJson('/watoolbox/webhook', $payload);
        
        $response->dump(); // Opcional: mostrar el body recibido
        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Data processed successfully']);
        
    }
}

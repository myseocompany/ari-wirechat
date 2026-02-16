<?php

use App\Models\Customer;
use App\Models\CustomerMeta;
use App\Models\CustomerStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows production, manual rate and dough preference for calculator leads', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $status = CustomerStatus::query()->create([
        'name' => 'Nuevo',
        'stage_id' => 1,
    ]);

    $customer = new Customer;
    $customer->name = 'Cliente Calculadora';
    $customer->phone = '+57 300 0000008';
    $customer->status_id = $status->id;
    $customer->user_id = $user->id;
    $customer->save();

    $createdAt = now()->subHour();

    CustomerMeta::query()->insert([
        [
            'customer_id' => $customer->id,
            'meta_data_id' => 3000,
            'value' => json_encode([
                'final_score' => 85,
                'stage' => 'Recupera inversion en <6 meses',
                'completed_at' => now()->toIso8601String(),
            ]),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ],
        [
            'customer_id' => $customer->id,
            'meta_data_id' => 30010,
            'value' => json_encode([
                'answer_text' => '1200 emp/dia',
            ]),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ],
        [
            'customer_id' => $customer->id,
            'meta_data_id' => 30011,
            'value' => json_encode([
                'answer_text' => '35 emp/h',
            ]),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ],
        [
            'customer_id' => $customer->id,
            'meta_data_id' => 30015,
            'value' => json_encode([
                'answer_text' => 'maiz-trigo',
            ]),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ],
    ]);

    $this->actingAs($user)
        ->get('/reports/views/customers_calculator')
        ->assertSuccessful()
        ->assertSee('Cliente Calculadora')
        ->assertSee('1200 emp/dia')
        ->assertSee('35 emp/h')
        ->assertSee('Maiz + Trigo')
        ->assertSee('Produccion diaria promedio')
        ->assertSee('Masas que quieren producir');
});

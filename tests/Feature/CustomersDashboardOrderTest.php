<?php

use App\Models\Customer;
use App\Models\CustomerStatus;
use App\Models\User;

it('renders the parent dashboard and customers list', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $parentStatus = new CustomerStatus;
    $parentStatus->name = 'Por contactar';
    $parentStatus->color = '#111111';
    $parentStatus->weight = 1;
    $parentStatus->status_id = 1;
    $parentStatus->save();

    $status = new CustomerStatus;
    $status->name = 'Nuevo';
    $status->color = '#111111';
    $status->weight = 1;
    $status->status_id = 1;
    $status->parent_id = $parentStatus->id;
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
    $response->assertSee('data-dashboard="status-summary"', false);
    $response->assertDontSee('id="side_content"', false);
    $response->assertSee('data-filter-open', false);
    $response->assertSee('customer-overlay', false);
});

it('filters customers by parent status', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $parentContact = new CustomerStatus;
    $parentContact->name = 'Por contactar';
    $parentContact->color = '#111111';
    $parentContact->weight = 1;
    $parentContact->status_id = 1;
    $parentContact->save();

    $parentSales = new CustomerStatus;
    $parentSales->name = 'Ventas';
    $parentSales->color = '#111111';
    $parentSales->weight = 3;
    $parentSales->status_id = 1;
    $parentSales->save();

    $statusNuevo = new CustomerStatus;
    $statusNuevo->name = 'Nuevo';
    $statusNuevo->color = '#111111';
    $statusNuevo->weight = 1;
    $statusNuevo->status_id = 1;
    $statusNuevo->parent_id = $parentContact->id;
    $statusNuevo->save();

    $statusGanado = new CustomerStatus;
    $statusGanado->name = 'Ganado Maquinas';
    $statusGanado->color = '#111111';
    $statusGanado->weight = 10;
    $statusGanado->status_id = 1;
    $statusGanado->parent_id = $parentSales->id;
    $statusGanado->save();

    $contactCustomer = new Customer([
        'name' => 'Cliente Contacto',
        'email' => 'contacto@example.com',
        'phone' => '3001111111',
        'country' => 'CO',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $contactCustomer->status_id = $statusNuevo->id;
    $contactCustomer->user_id = $user->id;
    $contactCustomer->save();

    $salesCustomer = new Customer([
        'name' => 'Cliente Venta',
        'email' => 'venta@example.com',
        'phone' => '3002222222',
        'country' => 'CO',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $salesCustomer->status_id = $statusGanado->id;
    $salesCustomer->user_id = $user->id;
    $salesCustomer->save();

    $response = $this->actingAs($user)->get(route('customers.index', [
        'parent_status_id' => $parentContact->id,
    ]));

    $response->assertOk();
    $response->assertSee('Cliente Contacto');
    $response->assertDontSee('Cliente Venta');
});

it('shows the breakdown of child statuses when a parent is selected', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $parentContact = new CustomerStatus;
    $parentContact->name = 'Por contactar';
    $parentContact->color = '#111111';
    $parentContact->weight = 1;
    $parentContact->status_id = 1;
    $parentContact->save();

    $statusNuevo = new CustomerStatus;
    $statusNuevo->name = 'Nuevo';
    $statusNuevo->color = '#222222';
    $statusNuevo->weight = 2;
    $statusNuevo->status_id = 1;
    $statusNuevo->parent_id = $parentContact->id;
    $statusNuevo->save();

    $customerOne = new Customer([
        'name' => 'Cliente Uno',
        'email' => 'uno@example.com',
        'phone' => '3000000001',
        'country' => 'CO',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $customerOne->status_id = $statusNuevo->id;
    $customerOne->user_id = $user->id;
    $customerOne->save();

    $customerTwo = new Customer([
        'name' => 'Cliente Dos',
        'email' => 'dos@example.com',
        'phone' => '3000000002',
        'country' => 'CO',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $customerTwo->status_id = $statusNuevo->id;
    $customerTwo->user_id = $user->id;
    $customerTwo->save();

    $response = $this->actingAs($user)->get(route('customers.index', [
        'parent_status_id' => $parentContact->id,
    ]));

    $response->assertOk();
    $response->assertSee('Subestados de Por contactar');
    $response->assertSee('Nuevo');
});

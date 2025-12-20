<?php

use App\Models\User;

it('renders the filter overlay and trigger button', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $response = $this->actingAs($user)->get(route('customers.index'));

    $response->assertOk();
    $response->assertSee('data-filter-open', false);
    $response->assertSee('id="filter_overlay"', false);
    $response->assertSee('id="filter_form"', false);
});

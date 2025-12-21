<?php

use App\Models\User;

it('redirects guests to login from root', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});

it('redirects authenticated users to customers index from root', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect(route('customers.index'));
});

<?php

use App\Models\User;

test('design system page is displayed for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/design-system');

    $response->assertOk();
});

test('design system page redirects guests to login', function () {
    $response = $this->get('/design-system');

    $response->assertRedirect('/login');
});

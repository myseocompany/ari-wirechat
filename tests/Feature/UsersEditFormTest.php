<?php

use App\Models\User;

it('shows the password and profile photo inputs on the user edit form', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get("/users/{$user->id}/edit");

    $response->assertOk();
    $response->assertSee('name="password"', false);
    $response->assertSee('name="profile_photo"', false);
    $response->assertSee('file:rounded-md', false);
});

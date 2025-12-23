<?php

use App\Models\User;

beforeEach(function () {
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => ':memory:',
        'session.driver' => 'array',
    ]);
});

test('design system page shows updated layout sections', function () {
    $user = User::factory()->make();

    $response = $this
        ->actingAs($user)
        ->get('/design-system');

    $response
        ->assertOk()
        ->assertSeeText('Palette')
        ->assertSeeText('Primary')
        ->assertSeeText('Emerald')
        ->assertSeeText('Orange')
        ->assertSeeText('Red')
        ->assertSeeText('Secondary')
        ->assertSeeText('Buttons')
        ->assertSeeText('Tags')
        ->assertSeeText('Benefits');
});

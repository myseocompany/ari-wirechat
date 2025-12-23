<?php

test('shows the password toggle button on the login screen', function () {
    $response = $this->get('/login');

    $response->assertOk();
    $response->assertSee('id="password_toggle"', false);
    $response->assertSee('id="password"', false);
});

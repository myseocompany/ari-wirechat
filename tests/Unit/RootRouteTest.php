<?php

use Tests\TestCase;

uses(TestCase::class);

it('redirects guests to login and users to customers index at root', function () {
    $routes = file_get_contents(base_path('routes/web.php'));

    expect($routes)->toContain("Route::get('/', function ()");
    expect($routes)->toContain("redirect()->route('customers.index')");
    expect($routes)->toContain("redirect()->route('login')");
});

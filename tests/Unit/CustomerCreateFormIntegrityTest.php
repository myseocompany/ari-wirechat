<?php

it('renders a single user assignment selector in the create customer form', function () {
    $view = file_get_contents(resource_path('views/customers/create.blade.php'));

    expect(substr_count($view, 'id="user_id"'))->toBe(1);
});

it('uses purchase_date as the create customer purchase date field', function () {
    $view = file_get_contents(resource_path('views/customers/create.blade.php'));

    expect($view)->toContain('name="purchase_date"');
    expect($view)->not->toContain('name="date_bought"');
});

it('falls back to the authenticated user for assignment when no user is selected', function () {
    $controller = file_get_contents(app_path('Http/Controllers/CustomerController.php'));

    expect($controller)->toContain('? ($requestedUserId ?? $authUser?->id)');
});

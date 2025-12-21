<?php

use Tests\TestCase;

uses(TestCase::class);

it('orders customer statuses by weight on the index action', function () {
    $controller = file_get_contents(app_path('Http/Controllers/CustomerStatusController.php'));

    expect($controller)->toContain("CustomerStatus::orderBy('weight', 'ASC')");
    expect($controller)->not->toContain("orderBy('stage_id'");
});

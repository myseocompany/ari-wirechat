<?php

use Tests\TestCase;

uses(TestCase::class);

it('shows city and department inside the details block', function () {
    $blade = file_get_contents(resource_path('views/customers/show.blade.php'));

    $detailsPos = strpos($blade, 'card-title card-header">Detalles');
    $addressPos = strpos($blade, 'card-title card-header">Dirección');

    $departmentPos = strpos($blade, 'Departamento:', $detailsPos);
    $cityPos = strpos($blade, 'Ciudad:', $detailsPos);

    expect($detailsPos)->not->toBeFalse();
    expect($addressPos)->not->toBeFalse();
    expect($departmentPos)->not->toBeFalse();
    expect($cityPos)->not->toBeFalse();
    expect($departmentPos)->toBeLessThan($addressPos);
    expect($cityPos)->toBeLessThan($addressPos);
});

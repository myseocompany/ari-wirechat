<?php

use Tests\TestCase;

uses(TestCase::class);

it('includes the full-width header meta row in the design system', function () {
    $blade = file_get_contents(resource_path('views/design-system.blade.php'));

    expect($blade)->toContain('Proyecto');
    expect($blade)->toContain('Servicios');
    expect($blade)->toContain('Enfoque');
    expect($blade)->toContain('Wirechat UI Kit');
});

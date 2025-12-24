<?php

use Tests\TestCase;

uses(TestCase::class);

it('shows the campaign detail table structure', function () {
    $blade = file_get_contents(resource_path('views/campaigns/show.blade.php'));

    expect($blade)->toContain('Detalle de campa');
    expect($blade)->toContain('Clientes que respondieron');
    expect($blade)->toContain('Cliente');
    expect($blade)->toContain('@foreach($questions as $question)');
});

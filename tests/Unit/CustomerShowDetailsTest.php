<?php

use Tests\TestCase;

uses(TestCase::class);

it('shows city and department inside the details block', function () {
    $blade = file_get_contents(resource_path('views/customers/show.blade.php'));

    $detailsPos = strpos($blade, '>Detalles<');
    $addressPos = strpos($blade, '>Dirección<');

    $departmentPos = strpos($blade, 'Departamento', $detailsPos);
    $cityPos = strpos($blade, 'Ciudad', $detailsPos);

    expect($detailsPos)->not->toBeFalse();
    expect($addressPos)->not->toBeFalse();
    expect($departmentPos)->not->toBeFalse();
    expect($cityPos)->not->toBeFalse();
    expect($departmentPos)->toBeLessThan($addressPos);
    expect($cityPos)->toBeLessThan($addressPos);
});

it('uses the tailwind layout on the customer show view', function () {
    $blade = file_get_contents(resource_path('views/customers/show.blade.php'));

    expect($blade)->toContain("@extends('layouts.tailwind')");
});

it('includes the duplicate search action link', function () {
    $blade = file_get_contents(resource_path('views/customers/show.blade.php'));

    expect($blade)->toContain('/optimize/customers/consolidateDuplicates/?query=');
    expect($blade)->toContain('getBestPhoneCandidate');
    expect($blade)->toContain('getBestEmail');
});

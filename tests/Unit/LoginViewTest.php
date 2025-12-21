<?php

use Tests\TestCase;

uses(TestCase::class);

it('uses the guest layout and Spanish login copy', function () {
    $blade = file_get_contents(resource_path('views/auth/login.blade.php'));

    expect($blade)->toContain("@extends('layouts.guest')");
    expect($blade)->toContain('Bienvenido de nuevo');
    expect($blade)->toContain('Correo electrónico');
    expect($blade)->toContain('Iniciar sesión');
});

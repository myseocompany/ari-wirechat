<?php

use App\Services\Geo\PhoneCountryResolver;

it('resuelve el indicativo mas largo antes que el mas corto', function () {
    $resolver = new PhoneCountryResolver;

    // 1868 es Trinidad y Tobago: no debe clasificarse como EE. UU. por el "1".
    expect($resolver->resolve(['18683732411'])['name'])->toBe('Trinidad y Tobago');
    expect($resolver->resolve(['18768136287'])['name'])->toBe('Jamaica');
    expect($resolver->resolve(['13015843280'])['name'])->toBe('EE. UU. / Canadá');
});

it('resuelve los indicativos de los mercados principales', function () {
    $resolver = new PhoneCountryResolver;

    expect($resolver->resolve(['573002984900'])['name'])->toBe('Colombia');
    expect($resolver->resolve(['59171234567'])['name'])->toBe('Bolivia');
    expect($resolver->resolve(['51987826928'])['name'])->toBe('Perú');
    expect($resolver->resolve(['5978525240'])['name'])->toBe('Surinam');
});

it('devuelve coordenadas numericas junto al nombre', function () {
    $resolver = new PhoneCountryResolver;
    $resultado = $resolver->resolve(['573002984900']);

    expect($resultado)->toHaveKeys(['name', 'lat', 'lng']);
    expect($resultado['lat'])->toBeFloat();
    expect($resultado['lng'])->toBeFloat();
});

it('ignora caracteres no numericos', function () {
    $resolver = new PhoneCountryResolver;

    expect($resolver->resolve(['+57 300 298 4900'])['name'])->toBe('Colombia');
});

it('cae al siguiente telefono cuando el primero no sirve', function () {
    $resolver = new PhoneCountryResolver;

    expect($resolver->resolve([null, '', '573002984900'])['name'])->toBe('Colombia');
});

it('devuelve null cuando no hay numero utilizable', function () {
    $resolver = new PhoneCountryResolver;

    expect($resolver->resolve([null, '', '   ']))->toBeNull();
    // Indicativo valido pero sin numero local suficiente: no se clasifica.
    expect($resolver->resolve(['5712']))->toBeNull();
});

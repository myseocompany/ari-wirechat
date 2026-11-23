<?php

use App\Http\Controllers\Api\APIController;
use App\Models\Customer;
use Tests\TestCase;

uses(TestCase::class);

function mapRdLead(array $lead): Customer
{
    $model = new Customer;
    $method = new ReflectionMethod(APIController::class, 'mapDataToCustomer');
    $method->setAccessible(true);
    $method->invoke(app(APIController::class), $model, $lead);

    return $model;
}

it('toma el teléfono de `phone` cuando RD no envía personal_phone ni mobile_phone', function () {
    $model = mapRdLead([
        'email' => 'lead@example.com',
        'phone' => '+57 300 111 2233',
        'personal_phone' => null,
        'mobile_phone' => null,
    ]);

    expect($model->phone2)->toBe('573001112233');
    expect($model->phone)->toBeNull();
});

it('toma el teléfono de la conversión cuando tampoco viene `phone`', function () {
    $model = mapRdLead([
        'email' => 'lead@example.com',
        'last_conversion' => ['content' => ['Telefone principal' => '+573001112233']],
    ]);

    expect($model->phone2)->toBe('573001112233');
});

it('no cambia nada cuando RD sí envía mobile_phone o personal_phone', function () {
    $mobile = mapRdLead(['mobile_phone' => '573009998877', 'phone' => '+573001112233']);
    expect($mobile->phone2)->toBe('573009998877');

    $personal = mapRdLead(['personal_phone' => '6011234567', 'phone' => '+573001112233']);
    expect($personal->phone)->toBe('6011234567');
    expect($personal->phone2)->toBeNull();
});

it('deja el cliente sin teléfono si no hay ningún número utilizable', function () {
    $model = mapRdLead(['email' => 'lead@example.com', 'phone' => 'n/a']);

    expect($model->phone)->toBeNull();
    expect($model->phone2)->toBeNull();
});

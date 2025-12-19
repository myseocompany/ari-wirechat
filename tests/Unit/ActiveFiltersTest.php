<?php

use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

uses(TestCase::class);

it('shows default date range and user in active filters', function () {
    Carbon::setTestNow(Carbon::parse('2025-12-19 10:00:00'));

    $user = User::factory()->make(['name' => 'Juan Perez']);
    $user->id = 7;

    request()->replace([]);
    request()->merge(['user_id' => (string) $user->id]);

    $html = view('customers.index_partials.active_filters', [
        'country_options' => collect(),
        'statuses' => collect(),
        'users' => collect([$user]),
        'sources' => collect(),
        'tags' => collect(),
    ])->render();

    expect($html)->toContain('Rango: 2025-12-18 17:00 - 2025-12-19 23:59');
    expect($html)->toContain('Usuario: Juan Perez');

    Carbon::setTestNow();
});

it('shows full range when default range is disabled', function () {
    Carbon::setTestNow(Carbon::parse('2025-12-19 10:00:00'));

    request()->replace([]);
    request()->merge(['no_date' => '1']);

    $html = view('customers.index_partials.active_filters', [
        'country_options' => collect(),
        'statuses' => collect(),
        'users' => collect(),
        'sources' => collect(),
        'tags' => collect(),
    ])->render();

    expect($html)->toContain('Rango: Todos');
    expect($html)->not->toContain('2025-12-18 17:00 - 2025-12-19 23:59');

    Carbon::setTestNow();
});

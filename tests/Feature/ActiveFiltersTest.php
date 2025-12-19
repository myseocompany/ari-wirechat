<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

it('shows default date range and user in active filters', function () {
    Carbon::setTestNow(Carbon::parse('2025-12-19 10:00:00'));

    $user = User::factory()->create(['name' => 'Juan Perez']);
    Auth::login($user);

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

<?php

use Tests\TestCase;

uses(TestCase::class);

it('uses the filter overlay for the daily customers followup view', function () {
    $blade = file_get_contents(resource_path('views/customers/daily.blade.php'));

    expect($blade)->toContain('data-filter-open');
    expect($blade)->toContain("@section('filter')");
    expect($blade)->not->toContain('data-toggle="collapse"');
});

it('styles the daily customers filter with tailwind utilities', function () {
    $blade = file_get_contents(resource_path('views/customers/filter_daily.blade.php'));

    expect($blade)->toContain('class="flex flex-col gap-4"');
    expect($blade)->toContain('ds-mono');
});

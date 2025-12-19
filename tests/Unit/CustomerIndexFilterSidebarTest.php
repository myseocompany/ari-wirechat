<?php

use Tests\TestCase;

uses(TestCase::class);

it('renders the customers filter inside the overlay container', function () {
    $blade = file_get_contents(resource_path('views/layouts/agile.blade.php'));

    $filterPos = strpos($blade, "@yield('filter')");
    $overlayPos = strpos($blade, 'id="filter_overlay"');
    $navClosePos = strpos($blade, '</nav>');
    $sideContentPos = strpos($blade, 'id="side_content"');

    expect($filterPos)->not->toBeFalse();
    expect($overlayPos)->not->toBeFalse();
    expect($navClosePos)->not->toBeFalse();
    expect($sideContentPos)->not->toBeFalse();
    expect($filterPos)->toBeGreaterThan($navClosePos);
    expect($filterPos)->toBeGreaterThan($sideContentPos);
    expect($filterPos)->toBeGreaterThan($overlayPos);
});

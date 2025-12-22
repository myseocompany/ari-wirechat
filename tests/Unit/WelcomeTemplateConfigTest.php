<?php

use Tests\TestCase;

uses(TestCase::class);

it('uses the WhatsApp welcome template config', function () {
    $config = file_get_contents(config_path('whatsapp.php'));
    $customerController = file_get_contents(app_path('Http/Controllers/CustomerController.php'));
    $apiController = file_get_contents(app_path('Http/Controllers/Api/APIController.php'));
    $loginView = file_get_contents(resource_path('views/customers/show.blade.php'));

    expect($config)->toContain('WHATSAPP_WELCOME_TEMPLATE');
    expect($config)->toContain('WHATSAPP_WELCOME_TEMPLATE_LANGUAGE');
    expect($customerController)->toContain("config('whatsapp.welcome_template'");
    expect($customerController)->toContain("config('whatsapp.welcome_template_language'");
    expect($apiController)->toContain("config('whatsapp.welcome_template'");
    expect($apiController)->toContain("config('whatsapp.welcome_template_language'");
    expect($loginView)->toContain("config('whatsapp.welcome_template'");
});

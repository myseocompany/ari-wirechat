<?php

use App\Services\GoogleAdsLeadMapper;

it('maps google ads leads into customer fields', function () {
    $payload = [
        'lead_id' => 'TeSter-123-ABCDEFGHIJKLMNOPQRSTUVWXYZ-abcdefghijklmnopqrstuvwxyz-0123456789-AaBb',
        'user_column_data' => [
            [
                'column_name' => 'Full Name',
                'string_value' => 'FirstName LastName',
                'column_id' => 'FULL_NAME',
            ],
            [
                'column_name' => 'User Phone',
                'string_value' => '+16505550123',
                'column_id' => 'PHONE_NUMBER',
            ],
            [
                'column_name' => 'User Email',
                'string_value' => 'test@example.com',
                'column_id' => 'EMAIL',
            ],
            [
                'column_name' => 'City',
                'string_value' => 'Mountain View',
                'column_id' => 'CITY',
            ],
            [
                'column_name' => 'Country',
                'string_value' => 'United States',
                'column_id' => 'COUNTRY',
            ],
            [
                'column_name' => 'Company Size',
                'string_value' => '11-50',
                'column_id' => 'COMPANY_SIZE',
            ],
        ],
        'api_version' => '1.0',
        'form_id' => 74820476159,
        'campaign_id' => 15923377400,
        'google_key' => 'VTQ0ct94UX',
        'is_test' => true,
        'gcl_id' => 'gcl-123',
        'adgroup_id' => 20000000000,
        'creative_id' => 30000000000,
    ];

    $mapper = new GoogleAdsLeadMapper;
    $mapped = $mapper->map($payload);

    expect($mapped['name'])->toBe('FirstName LastName');
    expect($mapped['phone'])->toBe('+16505550123');
    expect($mapped['email'])->toBe('test@example.com');
    expect($mapped['city'])->toBe('Mountain View');
    expect($mapped['country'])->toBe('United States');
    expect($mapped['utm_source'])->toBe('google_ads');
    expect($mapped['campaign_name'])->toBe('15923377400');
    expect($mapped['adset_name'])->toBe('20000000000');
    expect($mapped['ad_name'])->toBe('30000000000');
    expect($mapped['lead_id'])->toBe('TeSter-123-ABCDEFGHIJKLMNOPQRSTUVWXYZ-abcdefghijklmnopqrstuvwxyz-0123456789-AaBb');
    expect($mapped['notes'])->toContain('Google Ads');
    expect($mapped['notes'])->toContain('lead_id: TeSter-123-ABCDEFGHIJKLMNOPQRSTUVWXYZ-abcdefghijklmnopqrstuvwxyz-0123456789-AaBb');
    expect($mapped['notes'])->toContain('Company Size: 11-50');
});

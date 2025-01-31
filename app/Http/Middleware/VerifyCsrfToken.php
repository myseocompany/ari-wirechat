<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'auth/callback',
        'api/opendialog',
        'api/customers/update',
        'api/customers/audience',
        'api/customers/status/*',
        'api/customers/referenceWompi',
        'api/references/sandbox',
        'api/references/production',
        'api/customers/save-calculate',
        'api/customers/rd_station',
        'api/customers/saveCustomer',
        'campaigns',
        'campaigns/message/*/update',
        'campaigns/message/*/delete',
        'customers/ajax/update_user',
        'metadata/*/create/poe/*'
    ];
}
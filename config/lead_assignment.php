<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Lead assignment mode
    |--------------------------------------------------------------------------
    |
    | sequential -> usa getNextUserId() (round-robin real)
    | random     -> respeta el peso "assignable" de cada usuario en cada sorteo
    |
    */
    'mode' => env('LEAD_ASSIGNMENT_MODE', 'sequential'),
];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Default Channel
    |--------------------------------------------------------------------------
    | Define qué proveedor usar por defecto para los envíos salientes.
    | Opciones soportadas: "watoolbox", "graph".
    */
    'default_channel' => env('WHATSAPP_CHANNEL', 'watoolbox'),
];

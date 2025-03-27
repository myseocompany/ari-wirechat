<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WhatsAppAPIController extends Controller
{
    public function test()
    {
        // TOKEN QUE NOS DA FACEBOOK
        $token = 'EAALYCTRcA8sBO3g3qFpF32AIK7nlwKzkGbJdWVzx4jLBH3hrMAdzZC9awutc15BWrYFEHZC447Aw4FJesCR5MV9G9SDXYOBWxTZBgjAXYmbEb1zjGeU9VT2gFZBG8ZAjpiXN7nGdGj1SnlZCJZCZBIfys8RfzY0ftIkOZBdSZCHuEoaqo34wPdlJsOw1ZAZAUpSD6KiYoHMIya8RZBMJwJv0ZD';
        // NUESTRO TELEFONO
        $phone = '573004410097';
        // URL A DONDE SE MANDARA EL message
        $url = "https://graph.facebook.com/v19.0/353578161179031/messages";

        // CONFIGURACION DEL MENSAJE
        $message = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => 'envivo_empanadero',
                'language' => [
                    'code' => 'es'
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => 'Nicolás' // Aquí va el parámetro requerido por tu plantilla
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // CONVERTIMOS EL message A JSON
        $messageJson = json_encode($message);

        // DECLARAMOS LAS CABECERAS
        $headers = [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ];

        // INICIAMOS EL CURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $messageJson);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = json_decode(curl_exec($curl), true);
        print_r($response);

        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        print_r($status_code);
        curl_close($curl);
    }
}

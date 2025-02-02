<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WAToolboxService {
    public $end_point = "";
    
    public function __construct($messageSource)
    {
        // Inicializar con el webhook del Message Source
        $settings =$messageSource->settings;
        logger(['WAToolboxService'=>$settings]);
        $this->end_point = $settings['webhook_url'];

        Log::info('WAToolBox endpoint set to: ' . $this->end_point);
    }
    
    public function sendToWhatsApp($data)
    {
        $response = Http::asJson()->post($this->end_point, [
            'action' => 'send-message',
            'phone' => $data['phone_number'],
            'content' => $data['message'],
            'type' => 'text',
            'is_outgoing' => true,
        ]);

        return $response->json();
    }

    public function sendMedia($dataIn){
        $url = $this->end_point;
        
        Log::info('sendMedia-antes:', ['phone' => $dataIn['phone_number'], 
                'text' => $dataIn['message'],
                'watoolbox' => $url
            ]);
        
        $data = [
            'action' => 'send-message',
            'type' => 'text',
            'content' => $dataIn['message'],
            'phone' => $dataIn['phone_number'],
        ];
        
        $res = $this->sendHttp($url, $data);
        Log::info("sendMedia-despues: content: ".
            $data['content'].", phone: ". 
            $data['phone']);
        return $res;
    }

    public function sendHttp($url, $data){
        try {
            $response = Http::asJson()->post($url, $data);

            if ($response->failed()) {
                throw new \Exception("HTTP status code: " . $response->status());
            }

            $responseData = $response->json();
            if (isset($responseData['error'])) {
                throw new \Exception("API error: " . $responseData['error']);
            }

            return redirect()->back()->with('success', 'Mensaje enviado exitosamente a ' . $data['phone']);
        } catch (\Exception $e) {
            Log::error("Error sending WhatsApp message: " . $e->getMessage(), [
                'response' => $response->body(),
                'httpCode' => $response->status()
            ]);
            return redirect()->back()->with('error', 'Error al enviar el mensaje: ' . $e->getMessage());
        }
    }

    public function sendMessageToWhatsApp($data)
    {   
        Log::info('sendMessageToWhatsApp ', $data);
        
        try {
            $payload = [
                'action' => 'send-message',
                'phone' => $data['phone_number'],
                'content' => $data['message'],
            ];

            // Decidir el tipo de mensaje
            if (isset($data['media_url'])) {
                $payload['type'] = 'media';
                $payload['attachments'] = [config('app.url') . $data['media_url']];
                $payload['content'] = "";
                
            } else {
                $payload['type'] = 'text';
                
            }
            Log::info('sendMessageToWhatsApp payload', $payload);

            $response = Http::asJson()->post($this->end_point, $payload);
            Log::info('sendMessageToWhatsApp payload', [$response->status()]);
            if ($response->failed()) {
                throw new \Exception("Error enviando mensaje: HTTP " . $response->status());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error enviando mensaje a WhatsApp: ' . $e->getMessage());
            throw $e;
        }
    }
}
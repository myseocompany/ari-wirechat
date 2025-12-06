<?php

namespace App\Services;

use App\Models\WhatsAppAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGraphService
{
    public function sendText(WhatsAppAccount $account, string $to, string $message): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ];

        $response = Http::withToken($account->api_token)
            ->acceptJson()
            ->asJson()
            ->post($account->api_url, $payload);

        if ($response->failed()) {
            Log::error('WhatsAppGraphService sendText failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            $response->throw();
        }

        return $response->json();
    }

    public function sendTemplate(WhatsAppAccount $account, string $to, string $templateName, string $languageCode = 'en_US', array $components = []): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        $response = Http::withToken($account->api_token)
            ->acceptJson()
            ->asJson()
            ->post($account->api_url, $payload);

        if ($response->failed()) {
            Log::error('WhatsAppGraphService sendTemplate failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);
            $response->throw();
        }

        $json = $response->json();
        Log::info('WhatsAppGraphService sendTemplate success', [
            'status' => $response->status(),
            'response' => $json,
        ]);

        return $json;
    }

    public function listTemplates(WhatsAppAccount $account): array
    {
        if (!$account->business_account_id) {
            throw new \InvalidArgumentException('Falta business_account_id en la cuenta');
        }

        $url = "https://graph.facebook.com/v22.0/{$account->business_account_id}/message_templates";

        $response = Http::withToken($account->api_token)
            ->acceptJson()
            ->get($url);

        if ($response->failed()) {
            Log::error('WhatsAppGraphService listTemplates failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            $response->throw();
        }

        $data = $response->json();
        return $data['data'] ?? [];
    }
}

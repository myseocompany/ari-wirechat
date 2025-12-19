<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookForwarder
{
    public function forward(array $payload): void
    {
        $url = config('whatsapp.sellerchat_webhook_url');

        if (! $url) {
            return;
        }

        try {
            $response = Http::timeout(3)->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('SellerChat webhook forward failed', [
                    'status' => $response->status(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('SellerChat webhook forward exception', [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}

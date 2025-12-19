<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
                    'body' => Str::limit($response->body(), 1000),
                ]);
            } else {
                Log::info('SellerChat webhook forward ok', [
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

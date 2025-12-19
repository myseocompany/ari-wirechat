<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppWebhookForwarder
{
    public function forward(string $rawPayload, ?string $signature): void
    {
        $url = config('whatsapp.sellerchat_webhook_url');

        if (! $url) {
            return;
        }

        try {
            $headers = array_filter([
                'X-Hub-Signature-256' => $signature,
            ]);

            $response = Http::withHeaders($headers)
                ->timeout(3)
                ->withBody($rawPayload, 'application/json')
                ->post($url);

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

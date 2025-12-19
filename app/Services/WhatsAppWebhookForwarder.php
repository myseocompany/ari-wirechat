<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppWebhookForwarder
{
    /**
     * @param  array<string, string>  $headers
     */
    public function forward(string $rawPayload, array $headers = []): void
    {
        $url = config('whatsapp.sellerchat_webhook_url');

        if (! $url) {
            return;
        }

        try {
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
                    'body' => Str::limit($response->body(), 1000),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('SellerChat webhook forward exception', [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}

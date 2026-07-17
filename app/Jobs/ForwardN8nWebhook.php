<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ForwardN8nWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public array $backoff = [10, 30, 120, 300];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public string $url, public array $payload)
    {
        $this->onQueue('webhooks');
    }

    public function handle(): void
    {
        try {
            $response = Http::timeout(3)->post($this->url, $this->payload);

            if (! $response->successful()) {
                Log::warning('N8n webhook forward failed', [
                    'url' => $this->url,
                    'status' => $response->status(),
                    'body' => Str::limit($response->body(), 1000),
                ]);

                $response->throw();
            } else {
                Log::info('N8n webhook forward ok', [
                    'url' => $this->url,
                    'status' => $response->status(),
                    'body' => Str::limit($response->body(), 1000),
                ]);
            }
        } catch (Throwable $exception) {
            Log::warning('N8n webhook forward exception', [
                'url' => $this->url,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}

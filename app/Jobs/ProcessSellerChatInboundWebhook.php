<?php

namespace App\Jobs;

use App\Services\WhatsAppInboundMessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessSellerChatInboundWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [5, 20, 60];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload)
    {
        $this->onQueue((string) config('whatsapp.inbound_processing_queue', 'webhooks'));
    }

    public function handle(WhatsAppInboundMessageService $service): void
    {
        $processed = $service->handle($this->payload);

        Log::info('SellerChat inbound webhook processed', [
            'processed_messages' => $processed,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('SellerChat inbound webhook job failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}

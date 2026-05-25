<?php

namespace App\Jobs;

use App\Services\WhatsAppWebhookForwarder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ForwardSellerChatWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public array $backoff = [10, 30, 120, 300];

    /**
     * @param  array<string, string>  $headers
     */
    public function __construct(public string $rawPayload, public array $headers = [])
    {
        $this->onQueue((string) config('whatsapp.sellerchat_forward_queue', 'webhooks'));
    }

    public function handle(WhatsAppWebhookForwarder $forwarder): void
    {
        $forwarder->forward($this->rawPayload, $this->headers);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('SellerChat forward webhook job failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}

<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Customer;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendCampaignToCustomer implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 120];

    public function __construct(
        public int $campaignId,
        public int $customerId,
        public ?string $channel = null
    ) {
        $this->onQueue('whatsapp_outgoing');
    }

    public function handle(WhatsAppService $whatsAppService): void
    {
        $campaign = Campaign::query()
            ->with('messages')
            ->find($this->campaignId);

        if (! $campaign) {
            Log::warning('Campaign send skipped: campaign not found', [
                'campaign_id' => $this->campaignId,
                'customer_id' => $this->customerId,
            ]);

            return;
        }

        $customer = Customer::query()->find($this->customerId);
        if (! $customer) {
            Log::warning('Campaign send skipped: customer not found', [
                'campaign_id' => $this->campaignId,
                'customer_id' => $this->customerId,
            ]);

            return;
        }

        $whatsAppService->sendCampaignMessages($campaign, $customer, $this->channel);

        Log::info('Campaign send processed', [
            'campaign_id' => $this->campaignId,
            'customer_id' => $this->customerId,
            'channel' => $this->channel,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Campaign send job failed', [
            'campaign_id' => $this->campaignId,
            'customer_id' => $this->customerId,
            'channel' => $this->channel,
            'error' => $exception->getMessage(),
        ]);
    }
}

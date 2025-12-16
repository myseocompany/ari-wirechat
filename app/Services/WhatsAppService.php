<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\MessageSource;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppTemplate;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public const CHANNEL_WATOOLBOX = 'watoolbox';
    public const CHANNEL_GRAPH = 'graph';

    private ?WAToolboxService $toolboxService = null;
    private ?MessageSource $messageSource = null;

    public function __construct(private readonly WhatsAppGraphService $graphService)
    {
    }

    public function sendCampaignMessages(
        ?Campaign $campaign,
        ?Customer $customer,
        ?string $channel = null,
        ?MessageSource $messageSource = null,
        ?WhatsAppAccount $account = null
    ): void {
        if (!$campaign || !$customer) {
            return;
        }

        $channel = $this->resolveChannel($channel);

        if ($channel === self::CHANNEL_GRAPH) {
            $this->sendCampaignViaGraph($campaign, $customer, $account);
            return;
        }

        $this->sendCampaignViaWAToolbox($campaign, $customer, $messageSource);
    }

    public function sendText(
        string $phone,
        string $message,
        ?string $channel = null,
        ?MessageSource $messageSource = null,
        ?WhatsAppAccount $account = null
    ): void {
        if (empty($phone) || trim($message) === '') {
            return;
        }

        $channel = $this->resolveChannel($channel);

        if ($channel === self::CHANNEL_GRAPH) {
            $this->sendTextViaGraph($phone, $message, $account);
            return;
        }

        $toolbox = $this->getWAToolboxService($messageSource);
        if (!$toolbox) {
            return;
        }

        $toolbox->sendMessageToWhatsApp([
            'phone_number' => $phone,
            'message' => $message,
            'action' => 'send-message',
            'type' => 'text',
        ]);
    }

    private function sendCampaignViaWAToolbox(
        Campaign $campaign,
        Customer $customer,
        ?MessageSource $messageSource = null
    ): void {
        $toolbox = $this->getWAToolboxService($messageSource);
        if (!$toolbox) {
            Log::warning('WhatsAppService skipped sendCampaignMessages: no default message source', [
                'campaign_id' => $campaign->id,
                'customer_id' => $customer->id,
            ]);
            return;
        }

        $phone = $customer->getPhone();
        if (empty($phone)) {
            Log::info('WhatsAppService skipped sendCampaignMessages: missing phone', [
                'campaign_id' => $campaign->id,
                'customer_id' => $customer->id,
            ]);
            return;
        }

        $sentTexts = [];

        foreach ($campaign->messages as $message) {
            $text = trim($message->text ?? '');
            if ($text === '' || in_array($text, $sentTexts, true)) {
                continue;
            }

            $sentTexts[] = $text;

            try {
                $toolbox->sendMessageToWhatsApp([
                    'phone_number' => $phone,
                    'message' => $text,
                    'action' => 'send-message',
                    'type' => 'text',
                ]);
            } catch (\Throwable $e) {
                Log::error('WhatsAppService sendCampaignMessages failed', [
                    'campaign_id' => $campaign->id,
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function sendCampaignViaGraph(
        Campaign $campaign,
        Customer $customer,
        ?WhatsAppAccount $account = null
    ): void {
        $account = $account ?: $this->getDefaultWhatsAppAccount();
        if (!$account) {
            Log::warning('WhatsAppService skipped Graph campaign: no WA account configured', [
                'campaign_id' => $campaign->id,
                'customer_id' => $customer->id,
            ]);
            return;
        }

        $phone = $customer->getPhone();
        if (empty($phone)) {
            Log::info('WhatsAppService skipped Graph campaign: missing phone', [
                'campaign_id' => $campaign->id,
                'customer_id' => $customer->id,
            ]);
            return;
        }

        $sentTexts = [];

        foreach ($campaign->messages as $message) {
            $text = trim($message->text ?? '');
            if ($text === '' || in_array($text, $sentTexts, true)) {
                continue;
            }
            $sentTexts[] = $text;

            try {
                $this->graphService->sendText($account, $phone, $text);
            } catch (\Throwable $e) {
                Log::error('WhatsAppService Graph sendCampaignMessages failed', [
                    'campaign_id' => $campaign->id,
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function sendTextViaGraph(string $phone, string $message, ?WhatsAppAccount $account = null): void
    {
        $account = $account ?: $this->getDefaultWhatsAppAccount();
        if (!$account) {
            Log::warning('WhatsAppService skipped Graph text: no WA account configured');
            return;
        }

        try {
            $this->graphService->sendText($account, $phone, $message);
        } catch (\Throwable $e) {
            Log::error('WhatsAppService Graph sendText failed', ['error' => $e->getMessage()]);
        }
    }

    private function getDefaultWhatsAppAccount(): ?WhatsAppAccount
    {
        return WhatsAppAccount::where('is_default', true)->first();
    }

    public function sendTemplateToCustomer(
        Customer $customer,
        string $templateName,
        array $components = [],
        ?string $language = null,
        ?WhatsAppAccount $account = null
    ): void {
        $phone = $customer->getPhone();
        if (!$phone) {
            Log::info('WhatsAppService skipped template: missing phone', [
                'customer_id' => $customer->id,
                'template' => $templateName,
            ]);
            return;
        }

        $template = WhatsAppTemplate::where('name', $templateName)->with('account')->first();
        if ($template) {
            $language = $language ?: ($template->language ?? 'en_US');
            $account = $account ?: $template->account;
        }

        $account = $account ?: $this->getDefaultWhatsAppAccount();
        $language = $language ?: 'en_US';

        if (!$account) {
            Log::warning('WhatsAppService skipped template: no account configured', [
                'customer_id' => $customer->id,
                'template' => $templateName,
            ]);
            return;
        }

        try {
            $response = $this->graphService->sendTemplate($account, $phone, $templateName, $language, $components);
            Log::info('WhatsAppService template response', [
                'customer_id' => $customer->id,
                'template' => $templateName,
                'response' => $response,
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsAppService sendTemplateToCustomer failed', [
                'customer_id' => $customer->id,
                'template' => $templateName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getWAToolboxService(?MessageSource $messageSource = null): ?WAToolboxService
    {
        $source = $messageSource ?: $this->messageSource ?: MessageSource::getDefaultMessageSource();
        if (!$source) {
            return null;
        }

        if (!$this->toolboxService || $this->messageSource?->id !== $source->id) {
            $this->messageSource = $source;
            $this->toolboxService = new WAToolboxService($source);
        }

        return $this->toolboxService;
    }

    private function resolveChannel(?string $channel): string
    {
        $channel = strtolower($channel ?? config('whatsapp.default_channel', self::CHANNEL_WATOOLBOX));

        return in_array($channel, [self::CHANNEL_WATOOLBOX, self::CHANNEL_GRAPH], true)
            ? $channel
            : self::CHANNEL_WATOOLBOX;
    }
}

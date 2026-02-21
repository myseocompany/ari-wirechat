<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\MessageSource;
use App\Models\User;
use App\Models\WhatsAppMessageMap;
use App\Services\MessageSourceConversationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessWhatsAppCrmOutgoingMessage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 120];

    /**
     * @param  array{
     *     id: string,
     *     type: string,
     *     user: string,
     *     instance_key?: string,
     *     phone: string,
     *     content: string,
     *     APIKEY: string,
     *     crm_user_id?: int,
     *     crm_customer_id?: int
     * }  $payload
     */
    public function __construct(public array $payload)
    {
        $this->onQueue('whatsapp_outgoing');
    }

    public function handle(MessageSourceConversationService $messageSourceConversationService): void
    {
        $externalMessageId = trim((string) ($this->payload['id'] ?? ''));
        if ($externalMessageId === '') {
            Log::warning('WhatsApp CRM outgoing skipped: empty message id', [
                'payload' => $this->payload,
            ]);

            return;
        }

        if (WhatsAppMessageMap::query()->where('external_message_id', $externalMessageId)->exists()) {
            Log::info('WhatsApp CRM outgoing skipped: duplicate external_message_id', [
                'external_message_id' => $externalMessageId,
            ]);

            return;
        }

        $incomingApiKey = (string) ($this->payload['APIKEY'] ?? '');

        $messageSource = MessageSource::query()
            ->where('APIKEY', $incomingApiKey)
            ->first();

        if (! $messageSource) {
            Log::warning('WhatsApp CRM outgoing skipped: message source not found', [
                'external_message_id' => $externalMessageId,
                'apikey_prefix' => mb_substr($incomingApiKey, 0, 6),
                'apikey_length' => mb_strlen($incomingApiKey),
            ]);

            return;
        }

        if (! $messageSource->isActive()) {
            Log::warning('WhatsApp CRM outgoing skipped: message source inactive', [
                'external_message_id' => $externalMessageId,
                'message_source_id' => $messageSource->id,
            ]);

            return;
        }

        $phone = $this->normalizePhone((string) ($this->payload['phone'] ?? ''));
        if ($phone === '') {
            Log::warning('WhatsApp CRM outgoing skipped: invalid phone', [
                'external_message_id' => $externalMessageId,
            ]);

            return;
        }

        $body = trim((string) ($this->payload['content'] ?? ''));
        if ($body === '') {
            Log::warning('WhatsApp CRM outgoing skipped: empty content', [
                'external_message_id' => $externalMessageId,
            ]);

            return;
        }

        Log::info('WhatsApp CRM outgoing processing started', [
            'external_message_id' => $externalMessageId,
            'phone' => $phone,
            'instance_key' => $this->payload['instance_key'] ?? null,
        ]);

        DB::transaction(function () use (
            $externalMessageId,
            $phone,
            $body,
            $messageSource,
            $messageSourceConversationService
        ): void {
            if (WhatsAppMessageMap::query()
                ->lockForUpdate()
                ->where('external_message_id', $externalMessageId)
                ->exists()) {
                return;
            }

            $customer = $this->resolveCustomer(
                $phone,
                $this->payload['crm_customer_id'] ?? null,
                $externalMessageId
            );
            $requestedUserId = $this->resolveRequestedUserId();
            if (! $customer) {
                $customer = Customer::query()->create([
                    'name' => 'WhatsApp CRM '.$phone,
                    'phone' => $phone,
                ]);

                $customer->forceFill([
                    'status_id' => 1,
                    'source_id' => $this->resolveSourceId($messageSource),
                    'user_id' => $requestedUserId,
                ])->save();
            } elseif ($requestedUserId && ! $customer->user_id) {
                $customer->forceFill([
                    'user_id' => $requestedUserId,
                ])->save();
            }

            $conversation = $messageSourceConversationService->resolveOrCreate($messageSource, $customer);
            $messageSourceConversationService->syncAssignedAgentParticipant($conversation, $customer);

            if (! $conversation->participant($messageSource)) {
                $conversation->addParticipant($messageSource);
            }

            $message = $messageSource->sendMessageTo($conversation, $body);
            if (! $message) {
                throw new \RuntimeException('No fue posible almacenar el mensaje saliente.');
            }

            WhatsAppMessageMap::query()->create([
                'external_message_id' => $externalMessageId,
                'wire_message_id' => $message->id,
                'wa_id' => $phone,
                'raw_payload' => [
                    'direction' => 'outgoing',
                    'channel' => 'wa_crm_extension',
                    'payload' => $this->payload,
                ],
            ]);

            Log::info('WhatsApp CRM outgoing stored successfully', [
                'external_message_id' => $externalMessageId,
                'wire_message_id' => $message->id,
                'conversation_id' => $conversation->id,
                'customer_id' => $customer->id,
                'sender_type' => $message->sendable_type,
                'sender_id' => $message->sendable_id,
                'customer_user_id' => $customer->user_id,
            ]);
        }, 3);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('WhatsApp CRM outgoing job failed', [
            'payload' => $this->payload,
            'error' => $exception->getMessage(),
        ]);
    }

    private function resolveSourceId(MessageSource $messageSource): int
    {
        $settings = is_array($messageSource->settings) ? $messageSource->settings : [];
        $sourceId = data_get($settings, 'source_id');

        return is_numeric($sourceId) && (int) $sourceId > 0
            ? (int) $sourceId
            : 79;
    }

    private function resolveRequestedUserId(): ?int
    {
        $requestedUserId = $this->payload['crm_user_id'] ?? null;

        if (! is_numeric($requestedUserId)) {
            return null;
        }

        $id = (int) $requestedUserId;

        return User::query()->whereKey($id)->exists() ? $id : null;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    private function resolveCustomer(string $phone, mixed $crmCustomerId, string $externalMessageId): ?Customer
    {
        if (is_numeric($crmCustomerId)) {
            $customerById = Customer::query()->find((int) $crmCustomerId);
            if ($customerById) {
                if (! $this->customerMatchesPhone($customerById, $phone)) {
                    Log::warning('WhatsApp CRM outgoing: crm_customer_id does not match phone', [
                        'external_message_id' => $externalMessageId,
                        'crm_customer_id' => $customerById->id,
                        'phone' => $phone,
                        'customer_phone' => (string) $customerById->phone,
                    ]);
                }

                return $customerById;
            }

            Log::warning('WhatsApp CRM outgoing: crm_customer_id not found, fallback by phone', [
                'external_message_id' => $externalMessageId,
                'crm_customer_id' => (int) $crmCustomerId,
                'phone' => $phone,
            ]);
        }

        return Customer::findByPhoneInternational($phone);
    }

    private function customerMatchesPhone(Customer $customer, string $phone): bool
    {
        $last9 = substr($phone, -9);
        $candidates = [
            (string) ($customer->phone_last9 ?? ''),
            (string) ($customer->phone2_last9 ?? ''),
            (string) ($customer->contact_phone2_last9 ?? ''),
        ];

        return in_array($last9, $candidates, true);
    }
}

<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\MessageSource;
use App\Models\User;
use App\Models\WhatsAppMessageMap;
use App\Services\LeadAssignmentService;
use App\Services\MessageSourceConversationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessSellerChatOutgoingMessage implements ShouldQueue
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
     *     phone: string,
     *     content: string,
     *     APIKEY: string,
     *     crm_user_id?: int
     * }  $payload
     */
    public function __construct(public array $payload)
    {
        $this->onQueue('sellerchat');
    }

    public function handle(
        LeadAssignmentService $leadAssignmentService,
        MessageSourceConversationService $messageSourceConversationService
    ): void {
        $externalMessageId = trim((string) ($this->payload['id'] ?? ''));
        if ($externalMessageId === '') {
            Log::warning('SellerChat outgoing skipped: empty message id', [
                'payload' => $this->payload,
            ]);

            return;
        }

        if (WhatsAppMessageMap::query()->where('external_message_id', $externalMessageId)->exists()) {
            return;
        }

        $messageSource = MessageSource::query()
            ->where('APIKEY', (string) ($this->payload['APIKEY'] ?? ''))
            ->first();

        if (! $messageSource) {
            Log::warning('SellerChat outgoing skipped: message source not found', [
                'external_message_id' => $externalMessageId,
            ]);

            return;
        }

        if (! $messageSource->isActive()) {
            Log::warning('SellerChat outgoing skipped: message source inactive', [
                'external_message_id' => $externalMessageId,
                'message_source_id' => $messageSource->id,
            ]);

            return;
        }

        $phone = $this->normalizePhone((string) ($this->payload['phone'] ?? ''));
        if ($phone === '') {
            Log::warning('SellerChat outgoing skipped: invalid phone', [
                'external_message_id' => $externalMessageId,
            ]);

            return;
        }

        $body = trim((string) ($this->payload['content'] ?? ''));
        if ($body === '') {
            Log::warning('SellerChat outgoing skipped: empty content', [
                'external_message_id' => $externalMessageId,
            ]);

            return;
        }

        DB::transaction(function () use (
            $externalMessageId,
            $phone,
            $body,
            $messageSource,
            $leadAssignmentService,
            $messageSourceConversationService
        ): void {
            if (WhatsAppMessageMap::query()
                ->lockForUpdate()
                ->where('external_message_id', $externalMessageId)
                ->exists()) {
                return;
            }

            $customer = Customer::findByPhoneInternational($phone);
            if (! $customer) {
                $customer = Customer::query()->create([
                    'name' => 'WhatsApp SellerChat '.$phone,
                    'phone' => $phone,
                ]);

                $assignedUserId = $leadAssignmentService->getAssignableUserId();
                $customer->forceFill([
                    'status_id' => 1,
                    'source_id' => $this->resolveSourceId($messageSource),
                    'user_id' => $assignedUserId,
                ])->save();

                if ($assignedUserId) {
                    $leadAssignmentService->recordAssignment(
                        $assignedUserId,
                        $customer->id,
                        'sellerchat_outgoing',
                        [
                            'source_id' => $customer->source_id,
                            'message_source_id' => $messageSource->id,
                        ]
                    );
                }
            }

            $conversation = $messageSourceConversationService->resolveOrCreate($messageSource, $customer);
            $messageSourceConversationService->syncAssignedAgentParticipant($conversation, $customer);

            $advisor = $this->resolveAdvisor($customer, $messageSource);
            if (! $advisor) {
                Log::warning('SellerChat outgoing skipped: advisor unresolved', [
                    'external_message_id' => $externalMessageId,
                    'customer_id' => $customer->id,
                    'message_source_id' => $messageSource->id,
                ]);

                return;
            }

            if (! $conversation->participant($advisor)) {
                $conversation->addParticipant($advisor);
            }

            $message = $advisor->sendMessageTo($conversation, $body);
            if (! $message) {
                throw new \RuntimeException('No fue posible almacenar el mensaje saliente.');
            }

            if (! $customer->user_id) {
                $customer->user_id = $advisor->id;
                $customer->save();
            }

            WhatsAppMessageMap::query()->create([
                'external_message_id' => $externalMessageId,
                'wire_message_id' => $message->id,
                'wa_id' => $phone,
                'raw_payload' => [
                    'direction' => 'outgoing',
                    'channel' => 'sellerchat_extension',
                    'payload' => $this->payload,
                ],
            ]);
        }, 3);
    }

    private function resolveSourceId(MessageSource $messageSource): int
    {
        $settings = is_array($messageSource->settings) ? $messageSource->settings : [];
        $sourceId = data_get($settings, 'source_id');

        return is_numeric($sourceId) && (int) $sourceId > 0
            ? (int) $sourceId
            : 79;
    }

    private function resolveAdvisor(Customer $customer, MessageSource $messageSource): ?User
    {
        $requestedUserId = $this->payload['crm_user_id'] ?? null;
        if (is_numeric($requestedUserId)) {
            $requestedUser = User::query()->find((int) $requestedUserId);
            if ($requestedUser) {
                return $requestedUser;
            }
        }

        if (! empty($customer->user_id)) {
            $owner = User::query()->find((int) $customer->user_id);
            if ($owner) {
                return $owner;
            }
        }

        $userFromSource = $messageSource->users()
            ->orderByDesc('user_message_sources.is_default')
            ->orderBy('users.id')
            ->first();

        if ($userFromSource) {
            return $userFromSource;
        }

        return User::query()->orderBy('id')->first();
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}

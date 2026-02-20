<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MessageSource;
use App\Models\User;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppMessageMap;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Namu\WireChat\Enums\MessageType;
use Namu\WireChat\Models\Conversation;
use Namu\WireChat\Models\Message;

class WhatsAppInboundMessageService
{
    public function __construct(
        private readonly WhatsAppWebhookParser $parser,
        private readonly LeadAssignmentService $leadAssignmentService,
        private readonly MessageSourceConversationService $messageSourceConversationService
    ) {}

    public function handle(array $payload): int
    {
        $messages = $this->parser->parse($payload);
        $processed = 0;

        foreach ($messages as $message) {
            if ($this->storeMessage($message)) {
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * @param  array{
     *     external_message_id: string,
     *     wa_id: string,
     *     type: string,
     *     body: ?string,
     *     timestamp: int,
     *     phone_number_id: ?string,
     *     display_phone_number: ?string,
     *     business_account_id: ?string,
     *     raw_payload: array
     * }  $message
     */
    private function storeMessage(array $message): bool
    {
        return DB::transaction(function () use ($message): bool {
            $messageSource = $this->resolveMessageSource($message);
            $sourceId = $this->resolveSourceId($messageSource);

            $customer = Customer::findByPhoneInternational($message['wa_id']);
            if (! $customer) {
                $customer = Customer::create([
                    'name' => 'WhatsApp SellerChat '.$message['wa_id'],
                    'phone' => $message['wa_id'],
                ]);

                $assignedUserId = $this->leadAssignmentService->getAssignableUserId();
                $customer->forceFill([
                    'status_id' => 1,
                    'source_id' => $sourceId,
                    'user_id' => $assignedUserId,
                ])->save();

                if ($assignedUserId) {
                    $this->leadAssignmentService->recordAssignment(
                        $assignedUserId,
                        $customer->id,
                        'whatsapp_webhook',
                        [
                            'strategy' => 'auto',
                            'source_id' => $sourceId,
                        ]
                    );
                }
            }

            if ($messageSource) {
                $conversation = $this->messageSourceConversationService->resolveOrCreate($messageSource, $customer);
                $this->messageSourceConversationService->syncAssignedAgentParticipant($conversation, $customer);
            } else {
                $systemUser = $this->resolveSystemUser();
                if (! $systemUser) {
                    Log::warning('WhatsApp inbound message skipped: no message source and no system user configured', [
                        'wa_id' => $message['wa_id'],
                        'external_message_id' => $message['external_message_id'],
                    ]);

                    return false;
                }

                $conversation = $this->findOrCreateConversation($systemUser, $customer);
            }

            $timestamp = $this->resolveTimestamp($message['timestamp']);

            $wireMessage = Message::create([
                'conversation_id' => $conversation->id,
                'sendable_type' => $customer->getMorphClass(),
                'sendable_id' => $customer->id,
                'body' => $message['body'],
                'type' => $this->mapMessageType($message['type']),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $inserted = WhatsAppMessageMap::query()->insertOrIgnore([
                'external_message_id' => $message['external_message_id'],
                'wire_message_id' => $wireMessage->id,
                'wa_id' => $message['wa_id'],
                'raw_payload' => json_encode($message['raw_payload'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($inserted === 0) {
                $wireMessage->delete();

                return false;
            }

            $conversation->forceFill(['updated_at' => $timestamp])->save();

            return true;
        }, 3);
    }

    private function resolveSystemUser(): ?User
    {
        $systemUserId = config('whatsapp.system_user_id');

        if ($systemUserId) {
            return User::find($systemUserId);
        }

        return User::query()->orderBy('id')->first();
    }

    private function findOrCreateConversation(User $systemUser, Customer $customer): Conversation
    {
        $conversation = Conversation::query()
            ->whereHas('participants', function ($query) use ($systemUser) {
                $query->where('participantable_id', $systemUser->id)
                    ->where('participantable_type', $systemUser->getMorphClass());
            })
            ->whereHas('participants', function ($query) use ($customer) {
                $query->where('participantable_id', $customer->id)
                    ->where('participantable_type', $customer->getMorphClass());
            })
            ->latest('updated_at')
            ->first();

        if ($conversation) {
            return $conversation;
        }

        return $systemUser->createConversationWith($customer);
    }

    private function resolveTimestamp(int $timestamp): Carbon
    {
        if ($timestamp > 0) {
            return Carbon::createFromTimestamp($timestamp);
        }

        return now();
    }

    private function mapMessageType(string $type): string
    {
        return match ($type) {
            'audio', 'voice' => MessageType::VOICE->value,
            'text' => MessageType::TEXT->value,
            default => MessageType::ATTACHMENT->value,
        };
    }

    /**
     * @param  array{
     *     phone_number_id: ?string,
     *     display_phone_number: ?string,
     *     business_account_id: ?string
     * }  $message
     */
    private function resolveMessageSource(array $message): ?MessageSource
    {
        $phoneNumberId = $message['phone_number_id'] ?? null;
        $businessAccountId = $message['business_account_id'] ?? null;
        $displayPhoneNumber = $message['display_phone_number'] ?? null;

        $whatsAppAccount = WhatsAppAccount::query()
            ->when(
                ! empty($phoneNumberId),
                fn ($query) => $query->where('phone_number_id', $phoneNumberId)
            )
            ->when(
                empty($phoneNumberId) && ! empty($businessAccountId),
                fn ($query) => $query->where('business_account_id', $businessAccountId)
            )
            ->first();

        if ($whatsAppAccount) {
            $settings = is_array($whatsAppAccount->settings) ? $whatsAppAccount->settings : [];
            $mappedMessageSourceId = data_get($settings, 'message_source_id');

            if (is_numeric($mappedMessageSourceId)) {
                $mapped = MessageSource::query()->find((int) $mappedMessageSourceId);
                if ($mapped) {
                    return $mapped;
                }
            }

            $fromAccountPhone = $this->findMessageSourceByPhone($whatsAppAccount->phone_number);
            if ($fromAccountPhone) {
                return $fromAccountPhone;
            }
        }

        $fromDisplayPhone = $this->findMessageSourceByPhone($displayPhoneNumber);
        if ($fromDisplayPhone) {
            return $fromDisplayPhone;
        }

        return MessageSource::getDefaultMessageSource();
    }

    private function resolveSourceId(?MessageSource $messageSource): int
    {
        if (! $messageSource || ! is_array($messageSource->settings)) {
            return 79;
        }

        $sourceId = data_get($messageSource->settings, 'source_id');

        return is_numeric($sourceId) && (int) $sourceId > 0
            ? (int) $sourceId
            : 79;
    }

    private function findMessageSourceByPhone(?string $phone): ?MessageSource
    {
        $normalized = $this->normalizePhone($phone);
        if (! $normalized) {
            return null;
        }

        $messageSource = MessageSource::query()
            ->where('phone_number', $normalized)
            ->orWhere('phone_number', '+'.$normalized)
            ->first();

        if ($messageSource) {
            return $messageSource;
        }

        return MessageSource::query()
            ->get()
            ->first(function (MessageSource $source) use ($normalized) {
                $settings = is_array($source->settings) ? $source->settings : [];
                $settingsPhone = $this->normalizePhone((string) data_get($settings, 'phone_number', ''));

                return $settingsPhone === $normalized;
            });
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        return $digits !== '' ? $digits : null;
    }
}

<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use App\Models\WhatsAppMessageMap;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Namu\WireChat\Enums\MessageType;
use Namu\WireChat\Models\Conversation;
use Namu\WireChat\Models\Message;

class WhatsAppInboundMessageService
{
    public function __construct(private readonly WhatsAppWebhookParser $parser) {}

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
     * @param  array{external_message_id: string, wa_id: string, type: string, body: ?string, timestamp: int, raw_payload: array}  $message
     */
    private function storeMessage(array $message): bool
    {
        return DB::transaction(function () use ($message): bool {
            $exists = WhatsAppMessageMap::where('external_message_id', $message['external_message_id'])
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                return false;
            }

            $customer = Customer::findByPhoneInternational($message['wa_id']);
            if (! $customer) {
                $customer = Customer::create([
                    'name' => 'WhatsApp '.$message['wa_id'],
                    'phone' => $message['wa_id'],
                ]);
            }

            $systemUser = $this->resolveSystemUser();
            if (! $systemUser) {
                Log::warning('WhatsApp inbound message skipped: no system user configured', [
                    'wa_id' => $message['wa_id'],
                    'external_message_id' => $message['external_message_id'],
                ]);

                return false;
            }

            $conversation = $this->findOrCreateConversation($systemUser, $customer);
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

            $conversation->forceFill(['updated_at' => $timestamp])->save();

            WhatsAppMessageMap::create([
                'external_message_id' => $message['external_message_id'],
                'wire_message_id' => $wireMessage->id,
                'wa_id' => $message['wa_id'],
                'raw_payload' => $message['raw_payload'],
            ]);

            return true;
        });
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
}

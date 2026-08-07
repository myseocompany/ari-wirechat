<?php

namespace App\Jobs;

use App\Models\AriCrmMirrorActor;
use App\Models\AriCrmMirrorContact;
use App\Models\AriCrmMirrorConversation;
use App\Models\AriCrmMirrorDelivery;
use App\Models\AriCrmMirrorMessage;
use App\Models\Customer;
use App\Services\MessageSourceConversationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Namu\WireChat\Events\MessageCreated;
use Namu\WireChat\Models\Message;

class ProcessAriCrmMirrorDelivery implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $deliveryId)
    {
        $this->onQueue('aricrm_mirror');
    }

    /**
     * Execute the job.
     */
    public function handle(MessageSourceConversationService $conversationService): void
    {
        DB::transaction(function () use ($conversationService): void {
            $delivery = AriCrmMirrorDelivery::query()->with('integration.messageSource')->lockForUpdate()->find($this->deliveryId);
            if (! $delivery || $delivery->status === 'processed') {
                return;
            }
            $delivery->increment('attempts');
            $delivery->update(['status' => 'processing']);
            $payload = $delivery->payload;
            $integration = $delivery->integration;

            $contactMapping = AriCrmMirrorContact::query()->where('integration_id', $integration->id)->where('aricrm_contact_id', $payload['contact_id'])->lockForUpdate()->first();
            $phone = preg_replace('/\D+/', '', (string) $payload['phone']) ?: null;
            $customer = $contactMapping?->customer;
            if (! $customer) {
                $customer = $phone ? Customer::findByPhoneInternational($phone) : null;
                $customer ??= Customer::query()->create(['name' => $payload['name'] ?: 'Cliente AriCRM', 'phone' => $phone]);
                AriCrmMirrorContact::query()->create(['integration_id' => $integration->id, 'aricrm_contact_id' => $payload['contact_id'], 'customer_id' => $customer->id, 'normalized_phone' => $phone]);
            }
            if (filled($payload['name']) && $customer->name !== $payload['name']) {
                $customer->update(['name' => $payload['name']]);
            }

            $conversationMapping = AriCrmMirrorConversation::query()->where('integration_id', $integration->id)->where('aricrm_conversation_id', $payload['conversation_id'])->lockForUpdate()->first();
            $conversation = $conversationMapping?->conversation ?? $conversationService->resolveOrCreate($integration->messageSource, $customer);
            AriCrmMirrorConversation::query()->firstOrCreate(['integration_id' => $integration->id, 'aricrm_conversation_id' => $payload['conversation_id']], ['conversation_id' => $conversation->id]);

            $mirrorMessage = AriCrmMirrorMessage::query()->where('integration_id', $integration->id)->where('aricrm_message_id', $payload['message_id'])->lockForUpdate()->first();
            if ($mirrorMessage) {
                $delivery->update(['status' => 'processed', 'wire_message_id' => $mirrorMessage->wire_message_id, 'processed_at' => now()]);

                return;
            }

            $author = $payload['author'];
            $sender = $payload['direction'] === 'inbound' ? $customer : AriCrmMirrorActor::query()->firstOrCreate(['integration_id' => $integration->id, 'actor_type' => $author['type'], 'aricrm_actor_id' => $author['id'] ?? null], ['name' => $author['name'] ?? $author['type']]);
            $wireMessage = Message::query()->create(['conversation_id' => $conversation->id, 'sendable_type' => $sender->getMorphClass(), 'sendable_id' => $sender->id, 'body' => $payload['body'], 'type' => $payload['message_type']]);
            $wireMessage->forceFill(['created_at' => $payload['occurred_at'], 'updated_at' => $payload['occurred_at']])->saveQuietly();
            $mirrorMessage = AriCrmMirrorMessage::query()->create(['integration_id' => $integration->id, 'aricrm_message_id' => $payload['message_id'], 'wire_message_id' => $wireMessage->id, 'wa_message_id' => $payload['wa_message_id'] ?? null, 'author_type' => $author['type'], 'author_id' => $author['id'] ?? null, 'author_name' => $author['name'] ?? null, 'sent_at' => $payload['sent_at'], 'occurred_at' => $payload['occurred_at'], 'media_status' => $payload['media'] ? 'pending' : null, 'metadata' => $payload['metadata'] ?? null]);
            $delivery->update(['status' => 'processed', 'wire_message_id' => $wireMessage->id, 'processed_at' => now()]);
            DB::afterCommit(fn () => broadcast(new MessageCreated($wireMessage)));
        });
    }
}

<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\MessageSourceConversationService;
use App\Services\WAToolboxService;
use Illuminate\Support\Facades\Log;
use Namu\WireChat\Models\Message as ModelsMessage;

class WAToolboxListener
{
    public function __construct(
        private readonly MessageSourceConversationService $messageSourceConversationService
    ) {}

    public function handle(object $event): void
    {
        if (($event->message->sendable_type ?? null) !== app(User::class)->getMorphClass()) {
            return;
        }

        $message = ModelsMessage::find($event->message->id);
        if (! $message) {
            return;
        }

        $conversation = $message->conversation;
        $messageSource = $this->messageSourceConversationService->findMessageSourceForConversation($conversation);
        $customer = $this->messageSourceConversationService->findCustomerForConversation($conversation);

        if (! $messageSource || ! $customer) {
            return;
        }

        $phoneNumber = $customer->phone;
        if (! $phoneNumber) {
            Log::warning('WAToolbox outbound skipped: customer phone missing', [
                'customer_id' => $customer->id,
                'conversation_id' => $conversation->id,
            ]);

            return;
        }

        $waToolboxService = new WAToolboxService($messageSource);

        $payload = [
            'phone_number' => $phoneNumber,
            'message' => (string) ($message->body ?? ''),
        ];

        if (isset($message->attachment->file_path)) {
            $payload['media_url'] = $message->attachment->url;
        }

        try {
            $waToolboxService->sendMessageToWhatsApp($payload);
        } catch (\Throwable $exception) {
            Log::error('Error sending outbound WAToolBox message', [
                'message_id' => $message->id,
                'conversation_id' => $conversation->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}

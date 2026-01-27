<?php

namespace App\Services\LeadClassifier;

use App\Models\Customer;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Namu\WireChat\Enums\MessageType;
use Namu\WireChat\Models\Message;

class ConversationSnapshotBuilder
{
    /**
     * @return array{
     *     conversation_id: int,
     *     customer_id: int,
     *     customer_messages: array<int, string>,
     *     full_customer_text: string,
     *     last_customer_message_at: CarbonInterface,
     *     customer_message_count: int
     * }|null
     */
    public function build(int $conversationId): ?array
    {
        $customerMorph = (new Customer)->getMorphClass();

        /** @var Collection<int, Message> $messages */
        $messages = Message::query()
            ->select(['sendable_id', 'body', 'created_at'])
            ->where('conversation_id', $conversationId)
            ->where('sendable_type', $customerMorph)
            ->where('type', MessageType::TEXT->value)
            ->orderBy('created_at')
            ->get();

        if ($messages->isEmpty()) {
            return null;
        }

        $customerId = (int) $messages->first()->sendable_id;

        $customerMessages = $messages
            ->pluck('body')
            ->filter(fn ($body) => is_string($body) && trim($body) !== '')
            ->map(fn ($body) => trim((string) $body))
            ->values()
            ->all();

        if ($customerMessages === []) {
            return null;
        }

        /** @var CarbonInterface $lastCustomerMessageAt */
        $lastCustomerMessageAt = $messages->last()->created_at;

        return [
            'conversation_id' => $conversationId,
            'customer_id' => $customerId,
            'customer_messages' => $customerMessages,
            'full_customer_text' => implode("\n", $customerMessages),
            'last_customer_message_at' => $lastCustomerMessageAt,
            'customer_message_count' => count($customerMessages),
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MessageSource;
use App\Models\MessageSourceConversation;
use App\Models\User;
use Namu\WireChat\Enums\ConversationType;
use Namu\WireChat\Enums\ParticipantRole;
use Namu\WireChat\Models\Conversation;

class MessageSourceConversationService
{
    public function resolveOrCreate(MessageSource $messageSource, Customer $customer): Conversation
    {
        $mapping = MessageSourceConversation::query()
            ->where('message_source_id', $messageSource->id)
            ->where('customer_id', $customer->id)
            ->with('conversation')
            ->first();

        if ($mapping?->conversation) {
            return $mapping->conversation;
        }

        $conversation = $this->findExistingConversation($messageSource, $customer);
        if (! $conversation) {
            $conversation = $messageSource->createGroup(
                name: $this->buildConversationName($messageSource, $customer),
                description: 'Canal WhatsApp'
            );
            $conversation->addParticipant($customer, ParticipantRole::PARTICIPANT);
        }

        MessageSourceConversation::query()->updateOrCreate(
            [
                'message_source_id' => $messageSource->id,
                'customer_id' => $customer->id,
            ],
            [
                'conversation_id' => $conversation->id,
            ]
        );

        return $conversation;
    }

    public function syncAssignedAgentParticipant(Conversation $conversation, Customer $customer): void
    {
        if (! $customer->user_id) {
            return;
        }

        $assignedUser = User::find($customer->user_id);
        if (! $assignedUser) {
            return;
        }

        $exists = $conversation->participants()
            ->where('participantable_id', $assignedUser->id)
            ->where('participantable_type', $assignedUser->getMorphClass())
            ->exists();

        if ($exists) {
            return;
        }

        $conversation->addParticipant($assignedUser, ParticipantRole::ADMIN);
    }

    public function findMessageSourceForConversation(Conversation $conversation): ?MessageSource
    {
        $messageSourceMorph = (new MessageSource)->getMorphClass();

        $participant = $conversation->participants()
            ->where('participantable_type', $messageSourceMorph)
            ->first();

        return $participant?->participantable;
    }

    public function findCustomerForConversation(Conversation $conversation): ?Customer
    {
        $customerMorph = (new Customer)->getMorphClass();

        $participant = $conversation->participants()
            ->where('participantable_type', $customerMorph)
            ->first();

        return $participant?->participantable;
    }

    private function findExistingConversation(MessageSource $messageSource, Customer $customer): ?Conversation
    {
        return Conversation::query()
            ->where('type', ConversationType::GROUP)
            ->whereHas('participants', function ($query) use ($messageSource) {
                $query->where('participantable_id', $messageSource->id)
                    ->where('participantable_type', $messageSource->getMorphClass());
            })
            ->whereHas('participants', function ($query) use ($customer) {
                $query->where('participantable_id', $customer->id)
                    ->where('participantable_type', $customer->getMorphClass());
            })
            ->latest('updated_at')
            ->first();
    }

    private function buildConversationName(MessageSource $messageSource, Customer $customer): string
    {
        $sourceName = trim((string) ($messageSource->name ?: 'WhatsApp '.$messageSource->id));
        $customerName = trim((string) ($customer->name ?: $customer->phone ?: 'Cliente'));

        return $sourceName.' · '.$customerName;
    }
}

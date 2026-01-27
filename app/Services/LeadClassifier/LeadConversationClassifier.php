<?php

namespace App\Services\LeadClassifier;

use App\Models\LeadConversationClassification;
use Carbon\CarbonInterface;

class LeadConversationClassifier
{
    /**
     * @param  array{
     *     conversation_id: int,
     *     customer_id: int,
     *     customer_messages: array<int, string>,
     *     full_customer_text: string,
     *     last_customer_message_at: CarbonInterface,
     *     customer_message_count: int
     * }  $snapshot
     * @return LeadConversationClassification|null
     */
    public function __construct(
        private readonly ConversationSnapshotBuilder $snapshotBuilder,
        private readonly LeadSignalsLlmExtractor $signalsExtractor,
        private readonly LeadScoreCalculator $scoreCalculator,
        private readonly LeadTagSuggester $tagSuggester
    ) {}

    public function classify(int $conversationId): ?LeadConversationClassification
    {
        $snapshot = $this->snapshotBuilder->build($conversationId);

        if ($snapshot === null) {
            return null;
        }

        return $this->classifyFromSnapshot($snapshot);
    }

    /**
     * @param  array{
     *     conversation_id: int,
     *     customer_id: int,
     *     customer_messages: array<int, string>,
     *     full_customer_text: string,
     *     last_customer_message_at: CarbonInterface,
     *     customer_message_count: int
     * }  $snapshot
     */
    public function classifyFromSnapshot(array $snapshot): LeadConversationClassification
    {
        $signalsResult = $this->signalsExtractor->extract($snapshot);
        $signals = $signalsResult['signals'];
        $reasons = $signalsResult['reasons'];

        $scoreResult = $this->scoreCalculator->calculate($signals);
        $score = $scoreResult['score'];
        $status = $scoreResult['status'];

        $suggestedTagId = $this->tagSuggester->suggest($signals, $score, $status);

        $classifierVersion = 'v1-signals-llm';

        $classification = LeadConversationClassification::query()->updateOrCreate(
            ['conversation_id' => $snapshot['conversation_id']],
            [
                'customer_id' => $snapshot['customer_id'],
                'status' => $status,
                'score' => $score,
                'confidence' => null,
                'signals_json' => $signals,
                'reasons_json' => $reasons,
                'suggested_tag_id' => $suggestedTagId,
                'last_customer_message_at' => $snapshot['last_customer_message_at'],
                'classified_at' => now(),
                'classifier_version' => $classifierVersion,
                'prompt_version' => 'sellerchat-v1',
                'model' => $signalsResult['model'],
            ]
        );

        return $classification;
    }
}

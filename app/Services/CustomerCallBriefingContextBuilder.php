<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MessageSourceConversation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Namu\WireChat\Models\Conversation;
use Namu\WireChat\Models\Message;

class CustomerCallBriefingContextBuilder
{
    /**
     * @return array{source_hash: string, context: string, sufficient: bool}
     */
    public function build(Customer $customer, string $promptVersion, string $promptHash): array
    {
        $this->ensureNormalizerIsAvailable();
        $customer->loadMissing(['status', 'tags', 'histories', 'actions.type']);

        $actions = $customer->actions()
            ->with('type')
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->get()
            ->sortBy('id')
            ->values();

        $messages = $this->messages($customer)->sortBy('id')->values();
        $histories = $customer->histories->sortBy('id')->values();

        $canonical = [
            'schema_version' => 'call-briefing-source-v1',
            'prompt_version' => $promptVersion,
            'prompt_hash' => $promptHash,
            'customer' => $this->customerData($customer),
            'tags' => $customer->tags->sortBy('id')->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $this->text($tag->name),
                'slug' => $this->text($tag->slug),
                'updated_at' => $this->date($tag->updated_at),
            ])->values()->all(),
            'actions' => $actions->map(fn ($action) => [
                'id' => $action->id,
                'type_id' => $action->type_id,
                'creator_user_id' => $action->creator_user_id,
                'note' => $this->text($action->note),
                'created_at' => $this->date($action->created_at),
                'updated_at' => $this->date($action->updated_at),
            ])->all(),
            'histories' => $histories->map(fn ($history) => [
                'id' => $history->id,
                'status_id' => $history->status_id,
                'user_id' => $history->user_id,
                'source_id' => $history->source_id,
                'updated_user_id' => $history->updated_user_id,
                'business' => $this->text($history->business),
                'city' => $this->text($history->city),
                'country' => $this->text($history->country),
                'notes' => $this->text($history->notes),
                'bought_products' => $this->text($history->bought_products),
                'technical_visit' => $history->technical_visit,
                'updated_at' => $this->date($history->updated_at),
            ])->all(),
            'messages' => $messages->map(fn ($message) => [
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sendable_id' => $message->sendable_id,
                'sendable_type' => $this->text($message->sendable_type),
                'type' => $this->text($message->type),
                'body' => $this->text($message->body),
                'created_at' => $this->date($message->created_at),
                'updated_at' => $this->date($message->updated_at),
            ])->all(),
        ];

        foreach ($canonical as $key => $value) {
            if (is_array($value)) {
                $canonical[$key] = $this->sortKeys($value);
            }
        }
        $json = json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);

        $commercialActions = $actions->filter(function ($action): bool {
            $note = trim((string) $action->note);
            $type = mb_strtolower((string) ($action->type?->name ?? ''));

            return $note !== '' && ! str_contains($type, 'actualizaci');
        });
        $verifiedMessages = $messages->filter(fn ($message): bool => trim((string) $message->body) !== '');

        $recentActions = collect($canonical['actions'])->reverse()->take(25);
        $historicalMilestones = collect($canonical['actions'])
            ->filter(fn (array $action): bool => preg_match('/venta|cotiz|visita|demostr|producto|volumen|presupuesto|decisor|pérdida|perdida/ui', (string) $action['note']) === 1)
            ->take(10);

        $context = json_encode([
            'customer' => $canonical['customer'],
            'tags' => $canonical['tags'],
            'recent_actions' => $recentActions->values()->all(),
            'historical_milestones' => $historicalMilestones->values()->all(),
            'status_transitions' => $this->transitions($histories, $customer),
            'recent_messages' => collect($canonical['messages'])->reverse()->values()->all(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        return [
            'source_hash' => hash('sha256', $json),
            'context' => $this->truncateContext($context),
            'sufficient' => $commercialActions->isNotEmpty() || $verifiedMessages->isNotEmpty(),
        ];
    }

    private function messages(Customer $customer): Collection
    {
        $conversationIds = Conversation::query()
            ->whereHas('participants', function ($query) use ($customer): void {
                $query->where('participantable_id', $customer->id)
                    ->where('participantable_type', $customer->getMorphClass());
            })
            ->pluck('id')
            ->merge(MessageSourceConversation::query()->where('customer_id', $customer->id)->pluck('conversation_id'))
            ->filter()
            ->unique()
            ->values();

        if ($conversationIds->isEmpty()) {
            return collect();
        }

        return Message::withoutGlobalScope(\Namu\WireChat\Models\Scopes\WithoutRemovedMessages::class)
            ->whereIn('conversation_id', $conversationIds)
            ->orderByDesc('id')
            ->limit(30)
            ->get();
    }

    private function customerData(Customer $customer): array
    {
        return [
            'business' => $this->text($customer->business),
            'city' => $this->text($customer->city),
            'company_type' => $this->text($customer->company_type),
            'count_empanadas' => $customer->count_empanadas,
            'country' => $this->text($customer->country),
            'created_at' => $this->date($customer->created_at),
            'empanadas_size' => $this->text($customer->empanadas_size),
            'id' => $customer->id,
            'maker' => $customer->maker,
            'notes' => $this->text($customer->notes),
            'number_venues' => $customer->number_venues,
            'product_id' => $customer->product_id,
            'request' => $this->text($customer->request),
            'scoring' => $customer->scoring,
            'scoring_interest' => $this->text($customer->scoring_interest),
            'scoring_profile' => $this->text($customer->scoring_profile),
            'status_id' => $customer->status_id,
            'updated_at' => $this->date($customer->updated_at),
        ];
    }

    private function transitions(Collection $histories, Customer $customer): array
    {
        $snapshots = $histories->map(fn ($history) => [
            'status_id' => $history->status_id,
            'user_id' => $history->user_id,
            'source_id' => $history->source_id,
            'business' => $this->text($history->business),
            'city' => $this->text($history->city),
            'country' => $this->text($history->country),
            'notes' => $this->text($history->notes),
            'bought_products' => $this->text($history->bought_products),
            'technical_visit' => $history->technical_visit,
            'updated_at' => $this->date($history->updated_at),
        ])->push([
            'status_id' => $customer->status_id,
            'user_id' => $customer->user_id,
            'source_id' => $customer->source_id,
            'business' => $this->text($customer->business),
            'city' => $this->text($customer->city),
            'country' => $this->text($customer->country),
            'notes' => $this->text($customer->notes),
            'bought_products' => $this->text($customer->bought_products),
            'technical_visit' => $customer->technical_visit,
            'updated_at' => $this->date($customer->updated_at),
        ])->values();

        return $snapshots->zip($snapshots->slice(1))->map(function ($pair): ?array {
            [$from, $to] = $pair;
            if (! $to || $from === $to) {
                return null;
            }

            return ['from' => $from, 'to' => $to, 'occurred_at' => $to['updated_at']];
        })->filter()->values()->all();
    }

    private function text(mixed $value, ?int $limit = null): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $text = str_replace(["\r\n", "\r", "\0"], ["\n", "\n", ''], (string) $value);
        $text = \Normalizer::normalize($text, \Normalizer::FORM_C) ?: $text;

        return mb_strimwidth(trim($text), 0, $limit ?? $this->noteLimit(), '');
    }

    private function date(mixed $value): ?string
    {
        if (! $value || (string) $value === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->utc()->format('Y-m-d\\TH:i:s\\Z');
        } catch (\Throwable) {
            return null;
        }
    }

    private function sortKeys(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = Arr::isList($item) ? array_map(fn ($child) => is_array($child) ? $this->sortKeys($child) : $child, $item) : $this->sortKeys($item);
            }
        }

        ksort($value);

        return $value;
    }

    private function ensureNormalizerIsAvailable(): void
    {
        if (! class_exists(\Normalizer::class)) {
            throw new \RuntimeException('normalizer_unavailable');
        }
    }

    private function noteLimit(): int
    {
        return max(1, (int) config('openai.call_briefing_note_limit', 1200));
    }

    private function truncateContext(string $context): string
    {
        return mb_strimwidth($context, 0, max(1, (int) config('openai.call_briefing_context_limit', 18000)), '');
    }
}

<?php

namespace App\Mcp\Tools;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CrmCustomerStageDistributionTool extends Tool
{
    protected string $name = 'crm-customer-stage-distribution';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Devuelve la distribucion de clientes por estado del pipeline, incluyendo inactividad por ultima actividad de acciones y Wirechat.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'tags' => ['nullable', 'array', 'max:25'],
            'tags.*' => ['string', 'max:100'],
            'created_from' => ['nullable', 'date_format:Y-m-d'],
            'created_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:created_from'],
            'last_activity_from' => ['nullable', 'date_format:Y-m-d'],
            'last_activity_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:last_activity_from'],
        ], [
            'tags.array' => 'Los tags deben enviarse como un arreglo de textos.',
            'tags.max' => 'El maximo de tags es 25.',
            'tags.*.string' => 'Cada tag debe ser texto.',
            'tags.*.max' => 'Cada tag puede tener maximo 100 caracteres.',
            'created_from.date_format' => 'La fecha created_from debe tener el formato YYYY-MM-DD.',
            'created_to.date_format' => 'La fecha created_to debe tener el formato YYYY-MM-DD.',
            'created_to.after_or_equal' => 'created_to debe ser igual o posterior a created_from.',
            'last_activity_from.date_format' => 'La fecha last_activity_from debe tener el formato YYYY-MM-DD.',
            'last_activity_to.date_format' => 'La fecha last_activity_to debe tener el formato YYYY-MM-DD.',
            'last_activity_to.after_or_equal' => 'last_activity_to debe ser igual o posterior a last_activity_from.',
        ]);

        $createdFrom = $this->startOfDay($validated['created_from'] ?? null);
        $createdTo = $this->endOfDay($validated['created_to'] ?? null);
        $lastActivityFrom = $this->startOfDay($validated['last_activity_from'] ?? null);
        $lastActivityTo = $this->endOfDay($validated['last_activity_to'] ?? null);
        $tags = $this->normalizeTags($validated['tags'] ?? []);

        $baseQuery = $this->baseCustomerQuery(
            tags: $tags,
            createdFrom: $createdFrom,
            createdTo: $createdTo,
            lastActivityFrom: $lastActivityFrom,
            lastActivityTo: $lastActivityTo,
        );

        return Response::json([
            'filters_applied' => [
                'tags' => $tags,
                'created_from' => $createdFrom?->toDateString(),
                'created_to' => $createdTo?->toDateString(),
                'last_activity_from' => $lastActivityFrom?->toDateString(),
                'last_activity_to' => $lastActivityTo?->toDateString(),
            ],
            'total_customers' => (clone $baseQuery)->count(),
            'by_stage' => $this->byStage($baseQuery),
            'by_stage_with_last_activity' => $this->byStageWithLastActivity($baseQuery),
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tags' => $schema->array()->description('Tags exactos a buscar en customers.notes. Si se envian varios, se filtra por cualquiera de ellos. Ej: ["#alimentec2026_meta_ads", "#alimentec2026_WP"].'),
            'created_from' => $schema->string()->description('Filtra clientes creados desde esta fecha, formato YYYY-MM-DD.'),
            'created_to' => $schema->string()->description('Filtra clientes creados hasta esta fecha, formato YYYY-MM-DD.'),
            'last_activity_from' => $schema->string()->description('Filtra clientes cuya ultima actividad fue desde esta fecha, formato YYYY-MM-DD.'),
            'last_activity_to' => $schema->string()->description('Filtra clientes cuya ultima actividad fue hasta esta fecha, formato YYYY-MM-DD.'),
        ];
    }

    /**
     * @param  array<int, mixed>  $tags
     * @return array<int, string>
     */
    private function normalizeTags(array $tags): array
    {
        return collect($tags)
            ->map(fn (mixed $tag): string => trim((string) $tag))
            ->filter(fn (string $tag): bool => $tag !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function startOfDay(mixed $date): ?Carbon
    {
        if ($date === null || $date === '') {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', (string) $date)->startOfDay();
    }

    private function endOfDay(mixed $date): ?Carbon
    {
        if ($date === null || $date === '') {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', (string) $date)->endOfDay();
    }

    /**
     * @param  array<int, string>  $tags
     */
    private function baseCustomerQuery(
        array $tags,
        ?Carbon $createdFrom,
        ?Carbon $createdTo,
        ?Carbon $lastActivityFrom,
        ?Carbon $lastActivityTo,
    ): Builder {
        $lastActionSubquery = DB::table('actions')
            ->selectRaw('customer_id, max(created_at) as last_action_at')
            ->whereNotNull('customer_id')
            ->whereNull('deleted_at')
            ->groupBy('customer_id');

        $lastWireMessageSubquery = $this->lastWireMessageSubquery();
        $lastActivityExpression = $this->lastActivityExpression();

        $query = DB::table('customers')
            ->leftJoin('customer_statuses', 'customer_statuses.id', '=', 'customers.status_id')
            ->leftJoinSub($lastActionSubquery, 'last_actions', function ($join): void {
                $join->on('last_actions.customer_id', '=', 'customers.id');
            })
            ->leftJoinSub($lastWireMessageSubquery, 'last_wire_messages', function ($join): void {
                $join->on('last_wire_messages.customer_id', '=', 'customers.id');
            })
            ->selectRaw('customers.id as customer_id')
            ->selectRaw('customers.status_id as stage_id')
            ->selectRaw("coalesce(customer_statuses.name, 'Sin estado') as stage_name")
            ->selectRaw('coalesce(customer_statuses.weight, 9999) as stage_weight')
            ->selectRaw("{$lastActivityExpression} as last_activity_at");

        if ($tags !== []) {
            $query->where(function (Builder $query) use ($tags): void {
                foreach ($tags as $tag) {
                    $query->orWhere('customers.notes', 'like', '%'.$tag.'%');
                }
            });
        }

        if ($createdFrom !== null) {
            $query->where('customers.created_at', '>=', $createdFrom);
        }

        if ($createdTo !== null) {
            $query->where('customers.created_at', '<=', $createdTo);
        }

        if ($lastActivityFrom !== null) {
            $query->whereRaw("{$lastActivityExpression} >= ?", [$lastActivityFrom->toDateTimeString()]);
        }

        if ($lastActivityTo !== null) {
            $query->whereRaw("{$lastActivityExpression} <= ?", [$lastActivityTo->toDateTimeString()]);
        }

        return DB::query()->fromSub($query, 'customer_stage_activity');
    }

    private function lastWireMessageSubquery(): Builder
    {
        $customerMorph = (new Customer)->getMorphClass();

        $directMessages = DB::table('wire_messages')
            ->selectRaw('wire_messages.sendable_id as customer_id')
            ->selectRaw('max(wire_messages.created_at) as last_wire_message_at')
            ->where('wire_messages.sendable_type', $customerMorph)
            ->whereNull('wire_messages.deleted_at')
            ->groupBy('wire_messages.sendable_id');

        $mappedConversationMessages = DB::table('wire_messages')
            ->join('message_source_conversations as msc', 'msc.conversation_id', '=', 'wire_messages.conversation_id')
            ->selectRaw('msc.customer_id as customer_id')
            ->selectRaw('max(wire_messages.created_at) as last_wire_message_at')
            ->whereNull('wire_messages.deleted_at')
            ->groupBy('msc.customer_id');

        return DB::query()
            ->fromSub($directMessages->unionAll($mappedConversationMessages), 'wire_activity')
            ->selectRaw('customer_id, max(last_wire_message_at) as last_wire_message_at')
            ->groupBy('customer_id');
    }

    private function lastActivityExpression(): string
    {
        return <<<'SQL'
case
    when last_actions.last_action_at is null then last_wire_messages.last_wire_message_at
    when last_wire_messages.last_wire_message_at is null then last_actions.last_action_at
    when last_actions.last_action_at >= last_wire_messages.last_wire_message_at then last_actions.last_action_at
    else last_wire_messages.last_wire_message_at
end
SQL;
    }

    /**
     * @return array<int, array{stage_id: int|null, stage_name: string, count: int}>
     */
    private function byStage(Builder $baseQuery): array
    {
        return (clone $baseQuery)
            ->selectRaw('stage_id, stage_name, stage_weight, count(*) as total')
            ->groupBy('stage_id', 'stage_name', 'stage_weight')
            ->orderBy('stage_weight')
            ->orderBy('stage_id')
            ->get()
            ->map(fn ($row): array => [
                'stage_id' => $row->stage_id !== null ? (int) $row->stage_id : null,
                'stage_name' => (string) $row->stage_name,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return array<int, array{stage_id: int|null, stage_name: string, count: int, sin_actividad_7d: int, sin_actividad_30d: int, sin_actividad_90d: int}>
     */
    private function byStageWithLastActivity(Builder $baseQuery): array
    {
        $sevenDaysAgo = now()->subDays(7)->toDateTimeString();
        $thirtyDaysAgo = now()->subDays(30)->toDateTimeString();
        $ninetyDaysAgo = now()->subDays(90)->toDateTimeString();

        return (clone $baseQuery)
            ->selectRaw('stage_id, stage_name, stage_weight, count(*) as total')
            ->selectRaw('sum(case when last_activity_at is null or last_activity_at < ? then 1 else 0 end) as sin_actividad_7d', [$sevenDaysAgo])
            ->selectRaw('sum(case when last_activity_at is null or last_activity_at < ? then 1 else 0 end) as sin_actividad_30d', [$thirtyDaysAgo])
            ->selectRaw('sum(case when last_activity_at is null or last_activity_at < ? then 1 else 0 end) as sin_actividad_90d', [$ninetyDaysAgo])
            ->groupBy('stage_id', 'stage_name', 'stage_weight')
            ->orderBy('stage_weight')
            ->orderBy('stage_id')
            ->get()
            ->map(fn ($row): array => [
                'stage_id' => $row->stage_id !== null ? (int) $row->stage_id : null,
                'stage_name' => (string) $row->stage_name,
                'count' => (int) $row->total,
                'sin_actividad_7d' => (int) $row->sin_actividad_7d,
                'sin_actividad_30d' => (int) $row->sin_actividad_30d,
                'sin_actividad_90d' => (int) $row->sin_actividad_90d,
            ])
            ->all();
    }
}
